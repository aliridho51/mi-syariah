<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan & Tunggakan — MI Syariah</title>
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
<body class="bg-slate-50 min-h-screen">

    <div class="no-print">
        @include('partials.navbar')
    </div>

    <div class="max-w-6xl mx-auto p-6 mt-4">

        {{-- Header & Filter --}}
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-lg font-bold text-slate-800">📊 Laporan Keuangan & Monitoring Tunggakan MI Syariah</h1>
                <p class="text-xs text-slate-500">Pemisahan dana operasional (SPP/Jasa Pendidikan) dan dana amanah (Infaq/Wakaf)</p>
            </div>

            <form method="GET" action="{{ route('payments.laporan') }}" class="no-print flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Tahun Ajaran</label>
                    <select name="tahun_id" onchange="this.form.submit()"
                        class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs bg-white">
                        <option value="">Semua Periode</option>
                        @foreach($tahunAjarans as $ta)
                            <option value="{{ $ta->id }}" @selected($tahunId == $ta->id)>{{ $ta->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kelas</label>
                    <select name="kelas" onchange="this.form.submit()"
                        class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs bg-white">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kls)
                            <option value="{{ $kls }}" @selected($kelas === $kls)>Kelas {{ $kls }}</option>
                        @endforeach
                    </select>
                </div>
                <a href="{{ route('payments.exportCsv', ['tahun_id' => $tahunId, 'kelas' => $kelas]) }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3.5 py-2 rounded-lg transition">
                    ⬇️ Export Excel (CSV)
                </a>
                <button type="button" onclick="window.print()"
                    class="border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold px-3.5 py-2 rounded-lg transition">
                    🖨️ Cetak Laporan
                </button>
            </form>
        </div>

        {{-- Kartu Ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold text-slate-500 uppercase">Dana Pokok Lunas ({{ $jumlahLunas }} transaksi)</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPokokLunas, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold text-slate-500 uppercase">Dana Infaq Sukarela</p>
                <p class="text-2xl font-bold text-teal-600 mt-1">Rp {{ number_format($totalInfaqLunas, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-xs font-semibold text-slate-500 uppercase">Total Kas Masuk Rekening MI</p>
                <p class="text-2xl font-bold text-purple-600 mt-1">Rp {{ number_format($totalPokokLunas + $totalInfaqLunas, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-rose-200 shadow-sm">
                <p class="text-xs font-semibold text-rose-600 uppercase">Belum Terbayar ({{ $jumlahBelumLunas }} tagihan)</p>
                <p class="text-2xl font-bold text-rose-600 mt-1">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Rekap per Jenis Pembayaran & Akad --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-700 text-sm">Rekap per Jenis Pembayaran & Akad Syariah</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100">
                                <th class="px-4 py-2.5">Jenis & Akad</th>
                                <th class="px-4 py-2.5 text-right">Lunas</th>
                                <th class="px-4 py-2.5 text-right">Infaq</th>
                                <th class="px-4 py-2.5 text-right">Tunggakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rekapPerJenis as $rj)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ $rj->nama }}</div>
                                    <div class="text-[10px] text-emerald-700">{{ $rj->akad }}</div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">
                                    Rp {{ number_format($rj->pokok_lunas, 0, ',', '.') }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $rj->jml_lunas }} lunas</div>
                                </td>
                                <td class="px-4 py-3 text-right text-teal-700">
                                    Rp {{ number_format($rj->infaq_lunas, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">
                                    Rp {{ number_format($rj->nominal_tunggakan, 0, ',', '.') }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $rj->jml_tunggakan }} tagihan</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Rekap per Kelas --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-700 text-sm">Rekapitulasi per Kelas</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100">
                                <th class="px-4 py-2.5">Kelas</th>
                                <th class="px-4 py-2.5 text-right">Pokok Lunas</th>
                                <th class="px-4 py-2.5 text-right">Infaq</th>
                                <th class="px-4 py-2.5 text-right">Belum Lunas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($rekapPerKelas as $rk)
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    Kelas {{ $rk->kelas }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $rk->jumlah_siswa }} siswa</div>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-emerald-700">Rp {{ number_format($rk->pokok_lunas, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right text-teal-700">Rp {{ number_format($rk->infaq_lunas, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-rose-600">
                                    Rp {{ number_format($rk->nominal_tunggakan, 0, ',', '.') }}
                                    <div class="text-[10px] text-slate-400 font-normal">{{ $rk->jml_tunggakan }} tagihan</div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data kelas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Daftar Tagihan Belum Lunas --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-700 text-sm">Daftar Tagihan Belum Lunas / Menunggu Konfirmasi</h2>
                <span class="text-xs text-slate-400">{{ $daftarTunggakan->total() }} tagihan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase border-b border-slate-100">
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Siswa & Kelas</th>
                            <th class="px-4 py-3">Wali Murid</th>
                            <th class="px-4 py-3">Tagihan</th>
                            <th class="px-4 py-3 text-right">Nominal</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($daftarTunggakan as $t)
                        <tr>
                            <td class="px-4 py-3 font-mono text-slate-500">{{ $t->transaction_code }}</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-800">{{ $t->student->nama ?? '-' }}</span>
                                <span class="text-slate-400">(Kelas {{ $t->student->kelas ?? '-' }})</span>
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $t->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $t->title }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">Rp {{ number_format($t->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($t->status === 'waiting_confirmation')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-semibold">Menunggu Verifikasi</span>
                                @else
                                    <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-semibold">Belum Dibayar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Alhamdulillah, tidak ada tunggakan pada filter ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($daftarTunggakan->hasPages())
            <div class="no-print px-5 py-4 border-t border-slate-100">
                {{ $daftarTunggakan->links() }}
            </div>
            @endif
        </div>

    </div>
</body>
</html>
