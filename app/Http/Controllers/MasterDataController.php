<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use App\Models\Payment;
use App\Models\Student;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Pengelolaan master data pembayaran (khusus Admin/Bendahara):
 * Jenis Pembayaran & Tahun Ajaran.
 */
class MasterDataController extends Controller
{
    public function index()
    {
        $jenisPembayarans = JenisPembayaran::withCount('payments')->orderBy('nama')->get();
        $tahunAjarans = TahunAjaran::withCount('payments')->orderByDesc('nama')->get();

        return view('master.index', compact('jenisPembayarans', 'tahunAjarans'));
    }

    /**
     * Mengosongkan seluruh data (Tahun Ajaran, Jenis Pembayaran, Wali Murid, Murid, dan Tagihan) kembali ke nol.
     */
    public function resetData()
    {
        Schema::disableForeignKeyConstraints();
        Payment::truncate();
        Student::truncate();
        JenisPembayaran::truncate();
        TahunAjaran::truncate();
        User::where('role', 'wali_murid')->delete();
        Schema::enableForeignKeyConstraints();

        return redirect()->route('master.index')
            ->with('success', 'Seluruh data di Admin (Tahun Ajaran, Jenis Pembayaran, Wali, Murid, dan Tagihan) sudah dikosongkan ke nol. Silakan mulai dari Langkah 1: tambah Tahun Ajaran & Jenis Pembayaran.');
    }

    /* ===================== JENIS PEMBAYARAN ===================== */

    public function storeJenis(Request $request)
    {
        JenisPembayaran::create($this->validateJenis($request));

        return redirect()->back()->with('success', 'Jenis pembayaran berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, $id)
    {
        $jenis = JenisPembayaran::findOrFail($id);
        $jenis->update($this->validateJenis($request, $jenis->id));

        return redirect()->back()->with('success', 'Jenis pembayaran berhasil diperbarui.');
    }

    public function destroyJenis($id)
    {
        $jenis = JenisPembayaran::findOrFail($id);

        if ($jenis->payments()->exists()) {
            return redirect()->back()->with('error', 'Jenis pembayaran "' . $jenis->nama . '" sudah dipakai pada tagihan sehingga tidak dapat dihapus.');
        }

        $jenis->delete();

        return redirect()->back()->with('success', 'Jenis pembayaran berhasil dihapus.');
    }

    private function validateJenis(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', Rule::unique('jenis_pembayarans', 'nama')->ignore($ignoreId)],
            'nominal' => 'required|numeric|min:0',
            'akad' => ['required', Rule::in(array_keys(JenisPembayaran::AKAD))],
            'keterangan' => 'nullable|string|max:255',
        ]);

        $data['is_bulanan'] = $request->boolean('is_bulanan');

        return $data;
    }

    /* ===================== TAHUN AJARAN ===================== */

    public function storeTahun(Request $request)
    {
        $data = $this->validateTahun($request);

        DB::transaction(function () use ($data, $request) {
            // Jika ini tahun ajaran pertama atau dicentang aktif, jadikan satu-satunya yang aktif
            $aktif = $request->boolean('aktif') || ! TahunAjaran::exists();
            if ($aktif) {
                TahunAjaran::query()->update(['aktif' => false]);
            }

            TahunAjaran::create(['nama' => $data['nama'], 'aktif' => $aktif]);
        });

        return redirect()->back()->with('success', 'Tahun ajaran ' . $data['nama'] . ' berhasil ditambahkan.');
    }

    public function updateTahun(Request $request, $id)
    {
        $tahun = TahunAjaran::findOrFail($id);
        $data = $this->validateTahun($request, $tahun->id);

        $tahun->update(['nama' => $data['nama']]);

        return redirect()->back()->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function aktifkanTahun($id)
    {
        $tahun = TahunAjaran::findOrFail($id);

        DB::transaction(function () use ($tahun) {
            TahunAjaran::query()->update(['aktif' => false]);
            $tahun->update(['aktif' => true]);
        });

        return redirect()->back()->with('success', 'Tahun ajaran ' . $tahun->nama . ' sekarang aktif.');
    }

    public function destroyTahun($id)
    {
        $tahun = TahunAjaran::findOrFail($id);

        if ($tahun->payments()->exists()) {
            return redirect()->back()->with('error', 'Tahun ajaran ' . $tahun->nama . ' sudah memiliki tagihan sehingga tidak dapat dihapus.');
        }

        if ($tahun->aktif) {
            return redirect()->back()->with('error', 'Tahun ajaran yang sedang aktif tidak dapat dihapus. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        $tahun->delete();

        return redirect()->back()->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    private function validateTahun(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'nama' => [
                'required', 'string', 'max:20',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tahun_ajarans', 'nama')->ignore($ignoreId),
            ],
        ], [
            'nama.regex' => 'Format tahun ajaran harus seperti 2026/2027.',
        ]);

        [$awal, $akhir] = array_map('intval', explode('/', $data['nama']));
        if ($akhir !== $awal + 1) {
            throw ValidationException::withMessages([
                'nama' => 'Tahun kedua harus satu tahun setelah tahun pertama (contoh: 2026/2027).',
            ]);
        }

        return $data;
    }
}
