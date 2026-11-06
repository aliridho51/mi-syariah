<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi {{ $payment->transaction_code }} — MI Syariah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4">

    <div class="max-w-2xl mx-auto">
        {{-- Tombol aksi (tidak ikut tercetak) --}}
        <div class="no-print flex items-center justify-between mb-4">
            <a href="{{ route('payments.index') }}" class="text-xs font-semibold text-slate-600 hover:text-emerald-700">
                ← Kembali ke Daftar Pembayaran
            </a>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-4 py-2 rounded-lg shadow-sm transition">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>

        {{-- Lembar Kwitansi --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-8">
            <div class="flex items-start justify-between border-b-2 border-emerald-700 pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">🕌</span>
                    <div>
                        <h1 class="font-bold text-emerald-800 text-lg leading-tight">MADRASAH IBTIDAIYAH (MI) SYARIAH</h1>
                        <p class="text-xs text-slate-500">Bukti Pembayaran & Infaq Resmi Madrasah</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1 rounded-full uppercase">
                        LUNAS
                    </span>
                    <p class="font-mono text-xs text-slate-500 mt-1">{{ $payment->transaction_code }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs mb-6">
                <div>
                    <p class="text-slate-400">Nama Siswa / Murid</p>
                    <p class="font-semibold text-slate-800 text-sm">{{ $payment->student->nama ?? '-' }}</p>
                    <p class="text-slate-500">NIS: {{ $payment->student->nis ?? '-' }} • Kelas {{ $payment->student->kelas ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Diterima dari (Wali Murid)</p>
                    <p class="font-semibold text-slate-800 text-sm">{{ $payment->user->name ?? '-' }}</p>
                    @if($payment->bank_pengirim || $payment->nama_pengirim)
                        <p class="text-slate-500">Rek. Pengirim: {{ $payment->bank_pengirim }} a.n. {{ $payment->nama_pengirim }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-400">Metode & Kanal Pembayaran</p>
                    <p class="font-medium text-slate-700">{{ $payment->payment_method ?? 'Virtual Account BSI MI' }}</p>
                    @if($payment->va_number)
                        <p class="font-mono text-[11px] text-emerald-700">No. VA BSI: {{ $payment->va_number }}</p>
                    @endif
                    @if($payment->reference_no)
                        <p class="font-mono text-[11px] text-slate-500">No. Referensi Bank: {{ $payment->reference_no }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-slate-400">Tanggal Lunas</p>
                    <p class="font-medium text-slate-700">{{ $payment->paid_at ? $payment->paid_at->translatedFormat('d F Y, H:i') : '-' }}</p>
                </div>
            </div>

            <table class="w-full text-sm border-t border-b border-slate-200 mb-6">
                <thead>
                    <tr class="text-xs uppercase text-slate-500 bg-slate-50">
                        <th class="py-2.5 px-3 text-left">Uraian Pembayaran</th>
                        <th class="py-2.5 px-3 text-left">Akad Syariah</th>
                        <th class="py-2.5 px-3 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">{{ $payment->title }}</td>
                        <td class="py-3 px-3 text-xs text-slate-600">{{ $payment->jenisPembayaran->akad_label ?? 'Ijarah (jasa pendidikan)' }}</td>
                        <td class="py-3 px-3 text-right font-semibold text-slate-800">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($payment->infaq_amount > 0)
                    <tr>
                        <td class="py-3 px-3 font-medium text-slate-800">Infaq Sukarela Madrasah</td>
                        <td class="py-3 px-3 text-xs text-slate-600">Hibah / Infaq Sukarela</td>
                        <td class="py-3 px-3 text-right font-semibold text-emerald-700">Rp {{ number_format($payment->infaq_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-emerald-50 font-bold text-emerald-950">
                        <td colspan="2" class="py-3 px-3">Total Diterima</td>
                        <td class="py-3 px-3 text-right text-base">
                            Rp {{ number_format($payment->amount + $payment->infaq_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="flex items-end justify-between text-xs text-slate-500 pt-2">
                <div class="max-w-xs">
                    <p class="italic text-slate-600">"Jazakumullahu khairan katsiran. Semoga menjadi amal jariyah dan keberkahan ilmu bagi ananda."</p>
                </div>
                <div class="text-center">
                    <p>Status Verifikasi,</p>
                    <div class="my-2 inline-block border border-emerald-300 bg-emerald-50 text-emerald-800 text-[10px] font-bold px-2.5 py-1 rounded">
                        ✓ TERVERIFIKASI OTOMATIS SISTEM SIAKAD MI
                    </div>
                    <p class="font-semibold text-slate-800 underline">{{ $payment->approver->name ?? 'Sistem Payment Gateway BSI MI' }}</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
