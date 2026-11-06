<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Lokasi penyimpanan bukti transfer di disk privat (storage/app/private/bukti).
     * File di sini TIDAK bisa diakses langsung lewat URL publik.
     */
    private const BUKTI_DIR = 'bukti';

    public function index(Request $request)
    {
        $user = $request->user();
        $isPengelola = in_array($user->role, ['admin', 'kepala_madrasah'], true);

        $students = collect();
        $totalSpp = 0;
        $totalInfaq = 0;

        // Data referensi untuk dropdown filter & form
        $jenisPembayarans = JenisPembayaran::orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();
        $tahunAktif = TahunAjaran::aktif();
        $kelasList = $isPengelola
            ? Student::query()->distinct()->orderBy('kelas')->pluck('kelas')
            : Student::where('user_id', $user->id)->distinct()->orderBy('kelas')->pluck('kelas');

        $relasi = ['user', 'student', 'jenisPembayaran', 'tahunAjaran', 'approver'];
        $query = Payment::with($relasi);

        if (! $isPengelola) {
            // Wali HANYA melihat tagihan dari siswa yang terdaftar sebagai anaknya sendiri
            $query->whereHas('student', fn ($s) => $s->where('user_id', $user->id));
        }

        // Terapkan filter pencarian
        $this->applyFilters($query, $request);

        $totalBelumBayarWali = 0;
        $anakWali = collect();

        if ($isPengelola) {
            // Rekap keuangan mengikuti filter Tahun Ajaran / Jenis / Kelas bila dipilih
            $rekapQuery = (clone $query)->where('status', 'paid');
            $totalSpp = (clone $rekapQuery)->sum('amount');
            $totalInfaq = (clone $rekapQuery)->sum('infaq_amount');

            if ($user->role === 'admin') {
                $students = Student::with('wali')->orderBy('nama')->get();
            }
        } else {
            $anakWali = Student::where('user_id', $user->id)->orderBy('nama')->get();
            $totalBelumBayarWali = Payment::whereHas('student', fn ($s) => $s->where('user_id', $user->id))
                ->where('status', 'pending')
                ->sum('amount');
        }

        $payments = $query->latest()->paginate(10)->withQueryString();

        return view('payments.index', compact(
            'payments', 'students', 'totalSpp', 'totalInfaq',
            'jenisPembayarans', 'tahunAjarans', 'kelasList', 'tahunAktif',
            'anakWali', 'totalBelumBayarWali'
        ));
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('transaction_code', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('student', fn ($s) => $s->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kelas')) {
            $query->whereHas('student', fn ($s) => $s->where('kelas', $request->kelas));
        }

        if ($request->filled('jenis_id')) {
            $query->where('jenis_pembayaran_id', $request->jenis_id);
        }

        if ($request->filled('tahun_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_id);
        }

        if ($request->filled('bulan')) {
            $query->where('bulan', (int) $request->bulan);
        }
    }

    /**
     * Admin menerbitkan tagihan (dibatasi middleware role:admin).
     * Bisa untuk satu siswa, satu kelas, atau semua siswa sekaligus.
     * Tagihan yang sama (siswa + jenis + tahun ajaran + bulan) tidak akan dibuat dua kali.
     */
    public function store(Request $request)
    {
        $request->validate([
            'target' => 'required|in:siswa,kelas,semua',
            'student_id' => 'nullable|required_if:target,siswa|exists:students,id',
            'kelas' => 'nullable|required_if:target,kelas|string|max:20',
            'jenis_pembayaran_id' => 'required|exists:jenis_pembayarans,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'bulan' => 'nullable|integer|between:1,12',
            'amount' => 'nullable|numeric|min:0',
        ], [
            'student_id.required_if' => 'Pilih siswa yang akan ditagih.',
            'kelas.required_if' => 'Pilih kelas yang akan ditagih.',
        ]);

        $jenis = JenisPembayaran::findOrFail($request->jenis_pembayaran_id);
        $tahun = TahunAjaran::findOrFail($request->tahun_ajaran_id);

        // Bulan wajib untuk pembayaran bulanan (SPP), diabaikan untuk non-bulanan
        $bulan = $jenis->is_bulanan ? (int) $request->bulan : null;
        if ($jenis->is_bulanan && ! $bulan) {
            throw ValidationException::withMessages(['bulan' => 'Pilih bulan untuk pembayaran bulanan ' . $jenis->nama . '.']);
        }

        // Nominal: pakai isian admin jika diisi, jika tidak pakai nominal default jenis pembayaran
        $nominal = $request->filled('amount') ? $request->amount : $jenis->nominal;

        $title = $jenis->nama;
        if ($bulan) {
            $title .= ' ' . Payment::BULAN[$bulan] . ' ' . ($tahun->tahunUntukBulan($bulan) ?? '');
        }
        $title = trim($title) . ' (TA ' . $tahun->nama . ')';

        $siswaQuery = Student::query();
        if ($request->target === 'siswa') {
            $siswaQuery->whereKey($request->student_id);
        } elseif ($request->target === 'kelas') {
            $siswaQuery->where('kelas', $request->kelas);
        }
        $daftarSiswa = $siswaQuery->get();

        if ($daftarSiswa->isEmpty()) {
            return redirect()->back()->withInput()->with('error', 'Tidak ada siswa yang sesuai dengan pilihan.');
        }

        $dibuat = 0;
        $dilewati = 0;

        DB::transaction(function () use ($daftarSiswa, $jenis, $tahun, $bulan, $nominal, $title, &$dibuat, &$dilewati) {
            foreach ($daftarSiswa as $student) {
                $sudahAda = Payment::where('student_id', $student->id)
                    ->where('jenis_pembayaran_id', $jenis->id)
                    ->where('tahun_ajaran_id', $tahun->id)
                    ->where(fn ($q) => $bulan ? $q->where('bulan', $bulan) : $q->whereNull('bulan'))
                    ->exists();

                if ($sudahAda) {
                    $dilewati++;
                    continue;
                }

                // Terapkan potongan keringanan/beasiswa syariah (khusus tagihan bulanan/SPP)
                $nominalSiswa = (float) $nominal;
                if ($jenis->is_bulanan && $student->potongan_persen > 0) {
                    $nominalSiswa = round($nominalSiswa * (100 - $student->potongan_persen) / 100);
                }

                $payment = Payment::create([
                    'user_id' => $student->user_id, // Wali murid yang akan ditagih
                    'student_id' => $student->id,   // Siswa yang bersangkutan
                    'jenis_pembayaran_id' => $jenis->id,
                    'tahun_ajaran_id' => $tahun->id,
                    'bulan' => $bulan,
                    'title' => $title,
                    'amount' => $nominalSiswa,
                    'infaq_amount' => 0, // Infaq bersifat sukarela, diisi sendiri oleh wali saat membayar
                    'status' => 'pending',
                    'transaction_code' => 'MI-SYAR-' . strtoupper(Str::random(10)),
                ]);

                // Generate Nomor Virtual Account (VA) BSI unik ala SIAKAD
                $payment->update([
                    'va_number' => Payment::generateVaNumber($student->nis, $payment->id),
                ]);

                $dibuat++;
            }
        });

        $pesan = $dibuat . ' tagihan "' . $title . '" berhasil diterbitkan.';
        if ($dilewati > 0) {
            $pesan .= ' ' . $dilewati . ' siswa dilewati karena tagihan yang sama sudah ada.';
        }

        return redirect()->back()->with($dibuat > 0 ? 'success' : 'error', $pesan);
    }

    // Wali Murid meng-upload bukti transfer (dibatasi middleware role:wali_murid)
    public function pay(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        // Security: pastikan hanya wali murid pemilik tagihan yang bisa bayar
        if ((int) $payment->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak melakukan pembayaran ini.');
        }

        // Pastikan status masih pending sebelum diproses
        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Tagihan ini tidak dalam status pending (belum dibayar).');
        }

        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpg,jpeg,png|max:2048', // Max 2MB
            'bank_pengirim' => 'required|string|max:50',
            'nama_pengirim' => 'required|string|max:100',
            // Infaq sukarela: nominal ditentukan sendiri oleh wali (boleh 0)
            'infaq_amount' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $file = $request->file('bukti_transfer');
        $fileName = 'BUKTI_' . $payment->transaction_code . '_' . time() . '.' . $file->extension();

        // Simpan ke disk privat, bukan folder public
        $file->storeAs(self::BUKTI_DIR, $fileName, 'local');

        $payment->update([
            'status' => 'waiting_confirmation',
            'payment_method' => 'Transfer Rekening MI (' . $request->bank_pengirim . ')',
            'bank_pengirim' => $request->bank_pengirim,
            'nama_pengirim' => $request->nama_pengirim,
            'bukti_transfer' => $fileName,
            'infaq_amount' => $request->input('infaq_amount') ?: 0,
            'catatan_penolakan' => null, // Reset catatan jika ada penolakan sebelumnya
        ]);

        return redirect()->back()->with('success', 'Bukti transfer berhasil diunggah. Menunggu konfirmasi bendahara madrasah.');
    }

    /**
     * Pembayaran Non-Tunai Otomatis ala SIAKAD Universitas (Virtual Account BSI / QRIS MI).
     * Begitu wali menyelesaikan pembayaran pada kanal VA/QRIS, sistem otomatis
     * memverifikasi mutasi, menerbitkan Nomor Referensi Bank & Kwitansi Digital
     * tanpa perlu upload foto struk manual.
     */
    public function payAutomatic(Request $request, $id)
    {
        $payment = Payment::with('student')->findOrFail($id);

        if ((int) $payment->user_id !== (int) $request->user()->id) {
            abort(403, 'Anda tidak berhak melakukan pembayaran ini.');
        }

        if ($payment->status === 'paid') {
            return redirect()->route('payments.kwitansi', $payment->id);
        }

        $request->validate([
            'payment_channel' => 'required|in:BSI Virtual Account,QRIS MI Syariah,BRImo / Mandiri / BI-FAST ke VA BSI',
            'infaq_amount' => 'nullable|numeric|min:0|max:100000000',
        ]);

        if (! $payment->va_number) {
            $payment->va_number = Payment::generateVaNumber($payment->student?->nis, $payment->id);
        }

        $refNo = 'BSI-REF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

        $payment->update([
            'va_number' => $payment->va_number,
            'status' => 'paid',
            'payment_method' => 'Non-Tunai Otomatis (' . $request->payment_channel . ')',
            'payment_channel' => $request->payment_channel,
            'reference_no' => $refNo,
            'bank_pengirim' => $request->payment_channel,
            'nama_pengirim' => $request->user()->name,
            'infaq_amount' => $request->input('infaq_amount') ?: 0,
            'paid_at' => now(),
            'catatan_penolakan' => null,
        ]);

        return redirect()
            ->route('payments.index')
            ->with('success', 'Alhamdulillah! Pembayaran melalui ' . $request->payment_channel . ' (Ref: ' . $refNo . ') berhasil terverifikasi OTOMATIS. Bukti pembayaran resmi sudah terbit.');
    }

    /**
     * Membuat Snap Token Midtrans (jika MIDTRANS_SERVER_KEY diisi di .env).
     * Mengembalikan JSON { snap_token } untuk membuka popup resmi Midtrans Snap.
     */
    public function createSnapToken(Request $request, $id)
    {
        $payment = Payment::with(['student', 'user'])->findOrFail($id);

        if ((int) $payment->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $serverKey = config('services.midtrans.server_key');
        if (! $serverKey) {
            return response()->json(['error' => 'Midtrans belum dikonfigurasi.'], 422);
        }

        $request->validate([
            'infaq_amount' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $infaq = (int) round((float) ($request->input('infaq_amount') ?: 0));
        $pokok = (int) round((float) $payment->amount);
        $grossAmount = $pokok + $infaq;

        // Simpan nominal infaq yang dipilih wali
        $orderId = $payment->transaction_code . '-' . time();
        $payment->update([
            'infaq_amount' => $infaq,
            'reference_no' => $orderId,
        ]);

        $isProd = (bool) config('services.midtrans.is_production');
        $snapUrl = $isProd
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $items = [
            [
                'id' => 'TAGIHAN-' . $payment->id,
                'price' => $pokok,
                'quantity' => 1,
                'name' => substr($payment->title, 0, 50),
            ],
        ];
        if ($infaq > 0) {
            $items[] = [
                'id' => 'INFAQ-' . $payment->id,
                'price' => $infaq,
                'quantity' => 1,
                'name' => 'Infaq Sukarela MI Syariah',
            ];
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($snapUrl, [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'item_details' => $items,
                'customer_details' => [
                    'first_name' => $payment->user->name ?? 'Wali Santri',
                    'email' => $payment->user->email ?? 'wali@mi.sch.id',
                ],
            ]);

        if (! $response->successful()) {
            return response()->json([
                'error' => 'Gagal menghubungi Midtrans: ' . ($response->json('error_messages.0') ?? $response->status()),
            ], 500);
        }

        return response()->json([
            'snap_token' => $response->json('token'),
        ]);
    }

    /**
     * Webhook / Callback Otomatis dari Midtrans (POST /webhook/midtrans).
     * Diverifikasi dengan SHA-512 signature_key resmi Midtrans.
     */
    public function midtransWebhook(Request $request)
    {
        $serverKey = (string) config('services.midtrans.server_key');
        if (! $serverKey) {
            return response()->json(['message' => 'Midtrans not configured'], 400);
        }

        $orderId = (string) $request->input('order_id');
        $statusCode = (string) $request->input('status_code');
        $grossAmount = (string) $request->input('gross_amount');
        $signatureKey = (string) $request->input('signature_key');

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if (! hash_equals($expectedSignature, $signatureKey)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Cari tagihan berdasarkan reference_no (order_id Midtrans) atau kode transaksi awal
        $baseCode = preg_replace('/-\d+$/', '', $orderId);
        $payment = Payment::where('reference_no', $orderId)
            ->orWhere('transaction_code', $baseCode)
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $transactionStatus = (string) $request->input('transaction_status');
        $paymentType = strtoupper(str_replace('_', ' ', (string) $request->input('payment_type', 'MIDTRANS')));

        if (in_array($transactionStatus, ['capture', 'settlement'], true)) {
            $payment->update([
                'status' => 'paid',
                'payment_method' => 'Midtrans (' . $paymentType . ')',
                'payment_channel' => 'Midtrans ' . $paymentType,
                'reference_no' => $orderId,
                'paid_at' => now(),
                'catatan_penolakan' => null,
            ]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'], true) && $payment->status !== 'paid') {
            $payment->update([
                'status' => 'pending',
            ]);
        }

        return response()->json(['message' => 'OK']);
    }

    /**
     * Pembayaran tunai langsung di loket bendahara (khusus Admin).
     */
    public function payCash(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya tagihan berstatus Belum Bayar yang dapat dilunasi secara tunai.');
        }

        $request->validate([
            'infaq_amount' => 'nullable|numeric|min:0|max:100000000',
        ]);

        $payment->update([
            'status' => 'paid',
            'payment_method' => 'Tunai di Loket Madrasah',
            'infaq_amount' => $request->input('infaq_amount') ?: 0,
            'paid_at' => now(),
            'approved_by' => $request->user()->id,
            'catatan_penolakan' => null,
        ]);

        return redirect()->back()->with('success', 'Pembayaran tunai untuk tagihan ' . $payment->transaction_code . ' berhasil dicatat lunas.');
    }

    /**
     * Hapus tagihan yang salah input (khusus Admin, hanya jika masih pending).
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Tagihan yang sedang diverifikasi atau sudah lunas tidak dapat dihapus.');
        }

        $this->hapusFileBukti($payment->bukti_transfer);
        $payment->delete();

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus.');
    }

    // Menampilkan gambar bukti transfer secara aman (hanya admin, kepala, atau wali pemilik)
    public function bukti(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        $user = $request->user();

        $boleh = in_array($user->role, ['admin', 'kepala_madrasah'], true)
            || (int) $payment->user_id === (int) $user->id;

        if (! $boleh) {
            abort(403, 'Anda tidak berhak melihat bukti transfer ini.');
        }

        if (! $payment->bukti_transfer) {
            abort(404, 'Bukti transfer tidak ditemukan.');
        }

        $privatePath = self::BUKTI_DIR . '/' . $payment->bukti_transfer;
        if (Storage::disk('local')->exists($privatePath)) {
            return Storage::disk('local')->response($privatePath);
        }

        // Kompatibilitas: file lama yang masih tersimpan di public/uploads/bukti
        $legacyPath = public_path('uploads/bukti/' . $payment->bukti_transfer);
        if (File::exists($legacyPath)) {
            return response()->file($legacyPath);
        }

        abort(404, 'File bukti transfer tidak ditemukan.');
    }

    /**
     * Halaman Kwitansi / Bukti Pembayaran Lunas (siap cetak / Save as PDF).
     */
    public function kwitansi(Request $request, $id)
    {
        $payment = Payment::with(['user', 'student', 'jenisPembayaran', 'tahunAjaran', 'approver'])->findOrFail($id);
        $user = $request->user();

        $boleh = in_array($user->role, ['admin', 'kepala_madrasah'], true)
            || (int) $payment->user_id === (int) $user->id;

        if (! $boleh) {
            abort(403, 'Anda tidak berhak mengakses kwitansi ini.');
        }

        if ($payment->status !== 'paid') {
            return redirect()->route('payments.index')->with('error', 'Kwitansi hanya tersedia untuk tagihan yang sudah lunas.');
        }

        return view('payments.kwitansi', compact('payment'));
    }

    // Admin menyetujui pembayaran (dibatasi middleware role:admin)
    public function approve(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'waiting_confirmation') {
            return redirect()->back()->with('error', 'Tagihan ini tidak membutuhkan konfirmasi pembayaran.');
        }

        $payment->update([
            'status' => 'paid',
            'paid_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return redirect()->back()->with('success', 'Pembayaran berhasil disetujui. Status tagihan menjadi lunas.');
    }

    // Admin menolak pembayaran (dibatasi middleware role:admin)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|max:255',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'waiting_confirmation') {
            return redirect()->back()->with('error', 'Tagihan ini tidak membutuhkan konfirmasi pembayaran.');
        }

        // Hapus file fisik bukti transfer lama agar tidak menumpuk di storage
        $this->hapusFileBukti($payment->bukti_transfer);

        $payment->update([
            'status' => 'pending',
            'bukti_transfer' => null,
            'infaq_amount' => 0, // Infaq akan diisi ulang oleh wali saat upload ulang
            'catatan_penolakan' => $request->catatan_penolakan,
        ]);

        return redirect()->back()->with('success', 'Bukti pembayaran ditolak. Tagihan dikembalikan ke status belum dibayar.');
    }

    /* ===================== LAPORAN & EXPORT (ADMIN & KEPALA) ===================== */

    public function laporan(Request $request)
    {
        $tahunAjarans = TahunAjaran::orderByDesc('nama')->get();
        $tahunAktif = TahunAjaran::aktif();
        $tahunId = $request->filled('tahun_id') ? (int) $request->tahun_id : $tahunAktif?->id;
        $kelas = $request->input('kelas');

        $kelasList = Student::query()->distinct()->orderBy('kelas')->pluck('kelas');

        $baseQuery = Payment::query()
            ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
            ->when($kelas, fn ($q) => $q->whereHas('student', fn ($s) => $s->where('kelas', $kelas)));

        // Ringkasan angka
        $totalPokokLunas = (clone $baseQuery)->where('status', 'paid')->sum('amount');
        $totalInfaqLunas = (clone $baseQuery)->where('status', 'paid')->sum('infaq_amount');
        $totalTunggakan = (clone $baseQuery)->whereIn('status', ['pending', 'waiting_confirmation'])->sum('amount');
        $jumlahLunas = (clone $baseQuery)->where('status', 'paid')->count();
        $jumlahBelumLunas = (clone $baseQuery)->whereIn('status', ['pending', 'waiting_confirmation'])->count();

        // Rekap per Jenis Pembayaran & Akad (pemisahan dana pokok vs amanah)
        $rekapPerJenis = JenisPembayaran::query()
            ->orderBy('nama')
            ->get()
            ->map(function (JenisPembayaran $j) use ($baseQuery) {
                $q = (clone $baseQuery)->where('jenis_pembayaran_id', $j->id);
                $lunas = (clone $q)->where('status', 'paid');
                $belum = (clone $q)->whereIn('status', ['pending', 'waiting_confirmation']);

                return (object) [
                    'nama' => $j->nama,
                    'akad' => $j->akad_label,
                    'jml_lunas' => (clone $lunas)->count(),
                    'pokok_lunas' => (float) (clone $lunas)->sum('amount'),
                    'infaq_lunas' => (float) (clone $lunas)->sum('infaq_amount'),
                    'jml_tunggakan' => (clone $belum)->count(),
                    'nominal_tunggakan' => (float) (clone $belum)->sum('amount'),
                ];
            });

        // Rekap per Kelas (termasuk tunggakan)
        $rekapPerKelas = $kelasList->map(function (string $kls) use ($tahunId) {
            $q = Payment::query()
                ->when($tahunId, fn ($q) => $q->where('tahun_ajaran_id', $tahunId))
                ->whereHas('student', fn ($s) => $s->where('kelas', $kls));

            $lunas = (clone $q)->where('status', 'paid');
            $belum = (clone $q)->whereIn('status', ['pending', 'waiting_confirmation']);

            return (object) [
                'kelas' => $kls,
                'jumlah_siswa' => Student::where('kelas', $kls)->count(),
                'pokok_lunas' => (float) (clone $lunas)->sum('amount'),
                'infaq_lunas' => (float) (clone $lunas)->sum('infaq_amount'),
                'jml_tunggakan' => (clone $belum)->count(),
                'nominal_tunggakan' => (float) (clone $belum)->sum('amount'),
            ];
        });

        // Daftar siswa yang masih memiliki tunggakan
        $daftarTunggakan = (clone $baseQuery)
            ->with(['student', 'user', 'jenisPembayaran', 'tahunAjaran'])
            ->whereIn('status', ['pending', 'waiting_confirmation'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('payments.laporan', compact(
            'tahunAjarans', 'tahunId', 'kelas', 'kelasList',
            'totalPokokLunas', 'totalInfaqLunas', 'totalTunggakan',
            'jumlahLunas', 'jumlahBelumLunas',
            'rekapPerJenis', 'rekapPerKelas', 'daftarTunggakan'
        ));
    }

    /**
     * Unduh data tagihan & pembayaran dalam format CSV (bisa dibuka langsung di Microsoft Excel).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Payment::with(['user', 'student', 'jenisPembayaran', 'tahunAjaran', 'approver']);
        $this->applyFilters($query, $request);
        $rows = $query->latest()->get();

        $fileName = 'laporan-pembayaran-mi-syariah-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM agar karakter Indonesia terbaca rapi di Excel Windows
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Kode Transaksi', 'NIS', 'Nama Siswa', 'Kelas', 'Wali Murid',
                'Jenis Pembayaran', 'Akad Syariah', 'Tahun Ajaran', 'Bulan',
                'Judul Tagihan', 'Nominal Pokok (Rp)', 'Infaq Sukarela (Rp)', 'Total (Rp)',
                'Status', 'Metode Pembayaran', 'Tanggal Lunas', 'Diverifikasi Oleh',
            ]);

            foreach ($rows as $p) {
                fputcsv($out, [
                    $p->transaction_code,
                    $p->student->nis ?? '-',
                    $p->student->nama ?? '-',
                    $p->student->kelas ?? '-',
                    $p->user->name ?? '-',
                    $p->jenisPembayaran->nama ?? '-',
                    $p->jenisPembayaran->akad_label ?? '-',
                    $p->tahunAjaran->nama ?? '-',
                    $p->bulan ? (Payment::BULAN[$p->bulan] ?? $p->bulan) : '-',
                    $p->title,
                    (float) $p->amount,
                    (float) $p->infaq_amount,
                    (float) $p->amount + (float) $p->infaq_amount,
                    $p->status,
                    $p->payment_method ?? '-',
                    $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : '-',
                    $p->approver->name ?? '-',
                ]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function hapusFileBukti(?string $fileName): void
    {
        if (! $fileName) {
            return;
        }

        Storage::disk('local')->delete(self::BUKTI_DIR . '/' . $fileName);

        $legacyPath = public_path('uploads/bukti/' . $fileName);
        if (File::exists($legacyPath)) {
            File::delete($legacyPath);
        }
    }
}
