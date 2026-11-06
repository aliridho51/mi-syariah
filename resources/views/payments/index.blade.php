<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Syariah MI</title>
    <meta name="description" content="Sistem Pengelolaan SPP dan Infaq Transparan Madrasah Ibtidaiyah">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

    @include('partials.navbar')

    <div class="max-w-6xl mx-auto p-6 mt-6">

        {{-- ===== NOTIFIKASI ===== --}}
        @if(session('success'))
            <div class="fade-in mb-5 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl shadow-sm">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="fade-in mb-5 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl shadow-sm">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===== BANNER & RINGKASAN KHUSUS WALI SANTRI ===== --}}
        @if(auth()->user()->role === 'wali_murid')
        <div class="bg-gradient-to-r from-emerald-700 to-emerald-900 p-6 rounded-xl shadow-md text-white mb-5">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 bg-white/15 text-emerald-100 text-[11px] font-semibold px-2.5 py-0.5 rounded-full mb-2">
                        👪 Portal Wali Santri — {{ auth()->user()->name }}
                    </div>
                    <h3 class="font-bold text-lg">Tagihan Pendidikan Putra/Putri Anda</h3>
                    <p class="text-sm text-emerald-100 mt-1">
                        Halaman ini <strong>hanya menampilkan tagihan putra/putri yang terdaftar pada akun Anda</strong>.
                        Klik <strong>"⚡ Bayar Non-Tunai"</strong> untuk membayar lewat Virtual Account BSI / QRIS dan bukti lunas akan terbit otomatis.
                    </p>
                </div>
                <div class="bg-white/10 px-4 py-3 rounded-lg border border-white/20 min-w-[230px]">
                    <p class="text-xs uppercase tracking-wider text-emerald-200">Total Tagihan Belum Dibayar</p>
                    <p class="text-2xl font-bold mt-1">Rp {{ number_format($totalBelumBayarWali ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-emerald-100 mt-1">
                        Putra/Putri:
                        @if(isset($anakWali) && $anakWali->isNotEmpty())
                            <strong>{{ $anakWali->pluck('nama')->join(', ') }}</strong>
                        @else
                            <em>Belum ada murid terhubung ke akun ini</em>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== KARTU RINGKASAN (Admin & Kepala) ===== --}}
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'kepala_madrasah')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Dana Pokok Terkumpul</p>
                    <span class="text-emerald-500 text-lg">💰</span>
                </div>
                <p class="text-2xl font-bold text-emerald-600">Rp {{ number_format($totalSpp ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Infaq Sukarela Terkumpul</p>
                    <span class="text-teal-500 text-lg">🤲</span>
                </div>
                <p class="text-2xl font-bold text-teal-600">Rp {{ number_format($totalInfaq ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Tagihan</p>
                    <span class="text-blue-500 text-lg">📋</span>
                </div>
                <p class="text-2xl font-bold text-blue-600">{{ $payments->total() }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Dana Masuk</p>
                    <span class="text-purple-500 text-lg">📈</span>
                </div>
                <p class="text-2xl font-bold text-purple-600">Rp {{ number_format(($totalSpp ?? 0) + ($totalInfaq ?? 0), 0, ',', '.') }}</p>
            </div>
        </div>
        @endif

        {{-- ===== FORM TERBIT TAGIHAN (Admin saja) ===== --}}
        @if(auth()->user()->role === 'admin')
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                    <span class="text-emerald-600">➕</span> Terbitkan Tagihan Pembayaran Syariah (Otomatis Buat Nomor VA BSI)
                </h2>
                <div class="flex items-center gap-3">
                    <a href="{{ route('master.index') }}" class="text-xs text-emerald-700 hover:underline font-medium">
                        ⚙️ Kelola Jenis Pembayaran & Tahun Ajaran →
                    </a>
                    <form action="{{ route('master.resetData') }}" method="POST"
                        onsubmit="return confirm('Kosongkan SEMUA data di Admin (Tahun Ajaran, Jenis Pembayaran, Wali, Murid, dan Tagihan) agar mulai dari nol?')">
                        @csrf
                        <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold px-3 py-1 rounded-lg transition">
                            🗑️ Reset Semua Data ke Nol
                        </button>
                    </form>
                </div>
            </div>

            @if($jenisPembayarans->isEmpty() || $tahunAjarans->isEmpty())
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 text-xs rounded-lg">
                    Master data belum lengkap. Silakan tambahkan <strong>Tahun Ajaran</strong> dan <strong>Jenis Pembayaran</strong> terlebih dahulu di menu
                    <a href="{{ route('master.index') }}" class="underline font-semibold">Master Data</a>.
                </div>
            @else
                <form action="{{ route('payments.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    @csrf

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Terbitkan Untuk</label>
                        <select name="target" id="bill-target" onchange="onTargetChange()"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            <option value="siswa" @selected(old('target', 'siswa') === 'siswa')>Satu Siswa</option>
                            <option value="kelas" @selected(old('target') === 'kelas')>Satu Kelas Sekaligus</option>
                            <option value="semua" @selected(old('target') === 'semua')>Semua Siswa ({{ $students->count() }} murid)</option>
                        </select>
                    </div>

                    <div id="wrap-siswa" class="md:col-span-4">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Siswa / Murid</label>
                        <select name="student_id" id="bill-student"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            <option value="">— Pilih Murid —</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>
                                    {{ $s->nama }} (Kelas {{ $s->kelas }} — Wali: {{ $s->wali->name ?? 'Tanpa Wali' }})
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-kelas" class="md:col-span-4 hidden">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Kelas</label>
                        <select name="kelas" id="bill-kelas"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            <option value="">— Pilih Kelas —</option>
                            @foreach($kelasList as $kls)
                                <option value="{{ $kls }}" @selected(old('kelas') === $kls)>Kelas {{ $kls }}</option>
                            @endforeach
                        </select>
                        @error('kelas')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="wrap-semua" class="md:col-span-4 hidden flex items-end">
                        <div class="w-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg px-3.5 py-2.5">
                            Tagihan & Nomor VA BSI unik akan diterbitkan untuk seluruh <strong>{{ $students->count() }} siswa</strong>.
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jenis Pembayaran</label>
                        <select name="jenis_pembayaran_id" id="bill-jenis" required onchange="onJenisChange()"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            <option value="">— Pilih Jenis —</option>
                            @foreach($jenisPembayarans as $j)
                                <option value="{{ $j->id }}"
                                    data-nominal="{{ (int) $j->nominal }}"
                                    data-bulanan="{{ $j->is_bulanan ? '1' : '0' }}"
                                    data-akad="{{ $j->akad_label }}"
                                    @selected(old('jenis_pembayaran_id') == $j->id)>
                                    {{ $j->nama }} — Rp {{ number_format($j->nominal, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <p id="info-akad" class="text-[11px] text-emerald-700 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" required
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            @foreach($tahunAjarans as $ta)
                                <option value="{{ $ta->id }}" @selected(old('tahun_ajaran_id', $tahunAktif?->id) == $ta->id)>
                                    {{ $ta->nama }}{{ $ta->aktif ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wrap-bulan" class="md:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Bulan SPP</label>
                        <select name="bulan" id="bill-bulan"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                            @foreach(\App\Models\Payment::BULAN as $num => $namaBulan)
                                <option value="{{ $num }}" @selected((int) old('bulan', (int) date('n')) === $num)>{{ $namaBulan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal Tagihan (Rp)</label>
                        <input type="number" name="amount" id="bill-amount" required value="{{ old('amount') }}" min="0"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                            placeholder="Otomatis dari jenis pembayaran">
                    </div>

                    <div class="md:col-span-6 flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-slate-100">
                        <p class="text-[11px] text-slate-400">
                            💡 Setiap tagihan otomatis mendapat <strong>Nomor Virtual Account (VA) BSI</strong>. Infaq sukarela diisi sendiri oleh Wali saat membayar.
                        </p>
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Simpan & Terbitkan Tagihan
                        </button>
                    </div>
                </form>
            @endif
        </div>
        @endif

        {{-- ===== FILTER & PENCARIAN ===== --}}
        <form method="GET" action="{{ route('payments.index') }}" class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama siswa, NIS, kode, atau VA..."
                    class="w-full border border-slate-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <select name="status" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Belum Bayar</option>
                    <option value="paid" @selected(request('status') === 'paid')>Lunas</option>
                    <option value="waiting_confirmation" @selected(request('status') === 'waiting_confirmation')>Menunggu Konfirmasi</option>
                </select>
            </div>
            <div>
                <select name="kelas" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kls)
                        <option value="{{ $kls }}" @selected(request('kelas') === $kls)>Kelas {{ $kls }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="tahun_id" class="w-full border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs bg-white">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach($tahunAjarans as $ta)
                        <option value="{{ $ta->id }}" @selected(request('tahun_id') == $ta->id)>TA {{ $ta->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">Filter</button>
                @if(request()->hasAny(['q', 'status', 'kelas', 'tahun_id', 'jenis_id', 'bulan']))
                    <a href="{{ route('payments.index') }}" class="px-2.5 py-1.5 border border-slate-300 text-slate-600 rounded-lg text-xs hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        {{-- ===== TABEL DAFTAR TAGIHAN ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                    <span>📋</span> Daftar Tagihan & Riwayat Pembayaran Non-Tunai
                </h2>
                <span class="text-xs text-slate-400">{{ $payments->total() }} data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="px-5 py-3 font-semibold">Kode & Virtual Account BSI</th>
                            <th class="px-5 py-3 font-semibold">Siswa (NIS - Kelas)</th>
                            @if(auth()->user()->role !== 'wali_murid')
                                <th class="px-5 py-3 font-semibold">Wali Murid</th>
                            @endif
                            <th class="px-5 py-3 font-semibold">Tagihan & Akad</th>
                            <th class="px-5 py-3 font-semibold">Nominal</th>
                            <th class="px-5 py-3 font-semibold">Infaq Sukarela</th>
                            <th class="px-5 py-3 font-semibold">Status & Bukti Otomatis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($payments as $p)
                        @php
                            $vaNumber = $p->va_number ?: \App\Models\Payment::generateVaNumber($p->student?->nis, $p->id);
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-5 py-4 font-mono text-xs text-slate-600">
                                <div class="font-semibold text-slate-700">{{ $p->transaction_code }}</div>
                                <div class="text-[11px] text-emerald-700 mt-0.5">VA BSI: <span class="font-bold">{{ $vaNumber }}</span></div>
                                @if($p->reference_no)
                                    <div class="text-[10px] text-slate-400 mt-0.5">Ref: {{ $p->reference_no }}</div>
                                @endif
                                @if($p->catatan_penolakan)
                                    <div class="text-rose-500 text-[10px] font-semibold mt-1">❌ Ditolak: "{{ $p->catatan_penolakan }}"</div>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($p->student)
                                    <div class="font-semibold text-slate-800">{{ $p->student->nama }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $p->student->nis }} — Kelas {{ $p->student->kelas }}</div>
                                @else
                                    <span class="text-slate-400">— Data Murid Terhapus —</span>
                                @endif
                            </td>
                            @if(auth()->user()->role !== 'wali_murid')
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $p->student->wali->name ?? $p->user->name ?? '— Terhapus —' }}
                            </td>
                            @endif
                            <td class="px-5 py-4 text-xs">
                                <div class="font-medium text-slate-700">{{ $p->title }}</div>
                                @if($p->jenisPembayaran)
                                    <span class="inline-block mt-0.5 text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">
                                        Akad: {{ $p->jenisPembayaran->akad_label }}
                                    </span>
                                @endif
                                <div class="text-[10px] text-slate-400 mt-0.5">Terbit: {{ $p->created_at->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-slate-800">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-emerald-600 font-medium">
                                @if($p->infaq_amount > 0)
                                    + Rp {{ number_format($p->infaq_amount, 0, ',', '.') }}
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($p->status == 'paid')
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-[10px] px-2.5 py-0.5 rounded-full font-semibold">
                                            ✓ Lunas ({{ $p->payment_channel ?? 'Terverifikasi' }})
                                        </span>
                                        @if($p->paid_at)
                                            <span class="text-[10px] text-slate-400">{{ $p->paid_at->translatedFormat('d M Y, H:i') }}</span>
                                        @endif
                                        <a href="{{ route('payments.kwitansi', $p->id) }}"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-900 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded transition">
                                            🧾 Lihat Bukti / Kwitansi
                                        </a>
                                    </div>
                                @elseif($p->status == 'waiting_confirmation')
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                            ⌛ Konfirmasi Manual
                                        </span>
                                        @if(auth()->user()->role === 'admin')
                                            <button type="button"
                                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] px-2 py-0.5 rounded font-semibold transition mt-1"
                                                onclick="showAdminApprovalModal(@js($p->id), @js(route('payments.bukti', $p->id)), @js($p->title), {{ (float) $p->amount }}, {{ (float) $p->infaq_amount }})">
                                                Proses
                                            </button>
                                        @endif
                                    </div>
                                @elseif($p->status == 'pending')
                                    @if(auth()->user()->role === 'wali_murid')
                                        <button type="button"
                                            class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs px-3 py-1.5 rounded-lg font-semibold shadow-sm transition"
                                            onclick="showVaModal(@js($p->id), @js($p->title), @js($p->student->nama ?? '-'), @js($vaNumber), {{ (float) $p->amount }})">
                                            ⚡ Bayar Non-Tunai
                                        </button>
                                    @else
                                        <div class="flex flex-col items-start gap-1">
                                            <span class="inline-flex items-center gap-1 bg-amber-100 text-amber-700 text-[10px] px-2 py-0.5 rounded-full font-semibold">
                                                Belum Dibayar
                                            </span>
                                            @if(auth()->user()->role === 'admin')
                                                <form action="{{ route('payments.destroy', $p->id) }}" method="POST"
                                                    onsubmit="return confirm('Hapus tagihan {{ $p->transaction_code }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[10px] text-rose-600 hover:underline">Hapus Tagihan</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-sm font-medium">Belum ada data tagihan</p>
                                    <p class="text-xs">Tagihan akan muncul di sini setelah diterbitkan oleh admin.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $payments->links() }}
            </div>
            @endif
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ &nbsp;•&nbsp; Sistem Pembayaran Syariah MI &nbsp;•&nbsp; {{ date('Y') }}
        </p>

    </div>

    {{-- ===== MODAL PEMBAYARAN NON-TUNAI OTOMATIS ALA SIAKAD (VA BSI & QRIS) ===== --}}
    <div id="va-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden fade-in">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-emerald-800 text-white">
                <div>
                    <h3 class="font-bold text-sm">⚡ Pembayaran Non-Tunai Otomatis (SIAKAD MI Syariah)</h3>
                    <p class="text-[11px] text-emerald-200">Otomatis terverifikasi & menerbitkan bukti lunas tanpa upload struk</p>
                </div>
                <button type="button" class="text-emerald-200 hover:text-white text-lg font-bold" onclick="closeVaModal()">&times;</button>
            </div>

            <form id="va-form" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs space-y-1">
                    <div class="flex justify-between"><span class="text-slate-500">Nama Santri:</span><span class="font-semibold text-slate-800" id="va-student">-</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tagihan:</span><span class="font-semibold text-slate-800" id="va-title">-</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Tagihan Pokok:</span><span class="font-semibold text-slate-800" id="va-pokok">Rp 0</span></div>
                </div>

                {{-- Nomor Virtual Account BSI --}}
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-semibold text-emerald-800 uppercase">Nomor Virtual Account BSI MI</p>
                            <p class="font-mono text-lg font-bold text-emerald-950 tracking-wider mt-0.5" id="va-number">-</p>
                            <p class="text-[11px] text-emerald-700">a.n. MI SYARIAH — Otomatis terdeteksi di BSI Mobile / ATM / BI-FAST</p>
                        </div>
                        <button type="button" onclick="copyVaNumber()"
                            class="bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                            📋 Salin VA
                        </button>
                    </div>
                </div>

                {{-- Pilih Kanal Pembayaran Non-Tunai --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Pilih Metode Non-Tunai</label>
                    <select name="payment_channel" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="BSI Virtual Account">BSI Virtual Account (BSI Mobile / Net Banking)</option>
                        <option value="QRIS MI Syariah">QRIS Resmi MI Syariah (Semua M-Banking / E-Wallet)</option>
                        <option value="BRImo / Mandiri / BI-FAST ke VA BSI">Transfer Bank Lain ke VA BSI</option>
                    </select>
                </div>

                {{-- Infaq Sukarela --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        🤲 Tambah Infaq Sukarela (Rp) <span class="text-slate-400 font-normal">— Opsional, boleh 0</span>
                    </label>
                    <input type="number" name="infaq_amount" id="va-infaq-input" value="0" min="0" step="1000"
                        oninput="updateVaTotal()"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <div class="flex flex-wrap gap-1.5 mt-1.5">
                        @foreach([0, 5000, 10000, 25000, 50000] as $nom)
                            <button type="button" onclick="setVaInfaq({{ $nom }})"
                                class="text-[11px] px-2 py-0.5 rounded border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-medium transition">
                                {{ $nom === 0 ? 'Tanpa Infaq' : '+Rp ' . number_format($nom, 0, ',', '.') }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="bg-slate-900 text-white p-3.5 rounded-lg flex items-center justify-between">
                    <span class="text-xs text-slate-300">Total Debet Otomatis:</span>
                    <span class="text-lg font-bold text-emerald-400" id="va-total">Rp 0</span>
                </div>

                <div class="flex flex-wrap justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeVaModal()"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    @if(config('services.midtrans.client_key') && config('services.midtrans.server_key'))
                        <button type="button" id="btn-midtrans-snap" onclick="bayarViaMidtransSnap()"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            💳 Buka Popup Midtrans (VA Bank & QRIS)
                        </button>
                    @endif
                    <button type="submit"
                        class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                        ✅ Bayar Sekarang (Verifikasi Otomatis)
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== MODAL VERIFIKASI ADMIN (Untuk data lama yang pakai upload manual) ===== --}}
    <div id="admin-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full overflow-hidden fade-in">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Verifikasi Bukti Transfer Wali Murid</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold" onclick="closeAdminModal()">&times;</button>
            </div>
            <div class="p-5 flex flex-col md:flex-row gap-5">
                <div class="w-full md:w-1/2 flex items-center justify-center border border-slate-200 bg-slate-100 rounded-lg overflow-hidden max-h-[320px]">
                    <img id="admin-bukti-img" src="" alt="Bukti Transfer" class="object-contain w-full h-full max-h-[320px]">
                </div>
                <div class="w-full md:w-1/2 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Rincian Pembayaran</p>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 mb-3 text-xs space-y-1">
                            <div class="font-semibold text-slate-800" id="admin-modal-title">-</div>
                            <div class="flex justify-between text-slate-600"><span>Tagihan Pokok:</span><span id="admin-modal-pokok">Rp 0</span></div>
                            <div class="flex justify-between text-emerald-700"><span>Infaq Sukarela:</span><span id="admin-modal-infaq">Rp 0</span></div>
                            <div class="flex justify-between font-bold text-slate-900 border-t border-slate-200 pt-1"><span>Total:</span><span id="admin-modal-total">Rp 0</span></div>
                        </div>
                        <form id="reject-form" method="POST" class="mb-4">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Alasan Penolakan</label>
                            <textarea name="catatan_penolakan" required rows="2"
                                class="w-full border border-slate-300 rounded-lg p-2 text-xs outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                            <button type="submit" class="w-full mt-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 rounded-lg text-xs">
                                ❌ Tolak Pembayaran
                            </button>
                        </form>
                    </div>
                    <form id="approve-form" method="POST" class="border-t border-slate-100 pt-4">
                        @csrf
                        <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg text-xs">
                            ✅ Setujui & Nyatakan Lunas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatRupiah(angka) {
            return 'Rp ' + (Number(angka) || 0).toLocaleString('id-ID');
        }

        function onTargetChange() {
            const target = document.getElementById('bill-target')?.value;
            if (!target) return;
            document.getElementById('wrap-siswa').classList.toggle('hidden', target !== 'siswa');
            document.getElementById('wrap-kelas').classList.toggle('hidden', target !== 'kelas');
            document.getElementById('wrap-semua').classList.toggle('hidden', target !== 'semua');
        }

        function onJenisChange() {
            const sel = document.getElementById('bill-jenis');
            if (!sel) return;
            const opt = sel.options[sel.selectedIndex];
            const isBulanan = opt?.getAttribute('data-bulanan') === '1';
            const nominal = opt?.getAttribute('data-nominal');
            const akad = opt?.getAttribute('data-akad');

            const wrapBulan = document.getElementById('wrap-bulan');
            if (wrapBulan) wrapBulan.classList.toggle('hidden', !isBulanan);
            if (nominal !== null && nominal !== '') document.getElementById('bill-amount').value = nominal;

            const infoAkad = document.getElementById('info-akad');
            if (infoAkad) {
                infoAkad.textContent = akad ? 'Akad: ' + akad : '';
                infoAkad.classList.toggle('hidden', !akad);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            onTargetChange();
            onJenisChange();
        });

        // ===== Modal Virtual Account & QRIS Otomatis (Wali Santri) =====
        let vaPokok = 0;
        let activePaymentId = null;

        function showVaModal(id, title, studentName, vaNumber, pokok) {
            activePaymentId = id;
            document.getElementById('va-form').action = "{{ url('payments') }}/" + id + "/pay-auto";
            vaPokok = Number(pokok) || 0;

            document.getElementById('va-student').innerText = studentName;
            document.getElementById('va-title').innerText = title;
            document.getElementById('va-number').innerText = vaNumber;
            document.getElementById('va-pokok').innerText = formatRupiah(vaPokok);
            document.getElementById('va-infaq-input').value = 0;
            updateVaTotal();

            document.getElementById('va-modal').classList.remove('hidden');
            document.getElementById('va-modal').classList.add('flex');
        }

        function setVaInfaq(nominal) {
            document.getElementById('va-infaq-input').value = nominal;
            updateVaTotal();
        }

        function updateVaTotal() {
            const infaq = Math.max(0, Number(document.getElementById('va-infaq-input').value) || 0);
            document.getElementById('va-total').innerText = formatRupiah(vaPokok + infaq);
        }

        function copyVaNumber() {
            const va = document.getElementById('va-number').innerText;
            navigator.clipboard?.writeText(va);
            alert('Nomor Virtual Account BSI ' + va + ' berhasil disalin!');
        }

        function closeVaModal() {
            document.getElementById('va-modal').classList.add('hidden');
            document.getElementById('va-modal').classList.remove('flex');
        }

        function showAdminApprovalModal(id, imageUrl, title, pokok, infaq) {
            document.getElementById('admin-bukti-img').src = imageUrl;
            document.getElementById('admin-modal-title').innerText = title;
            document.getElementById('admin-modal-pokok').innerText = formatRupiah(pokok);
            document.getElementById('admin-modal-infaq').innerText = formatRupiah(infaq);
            document.getElementById('admin-modal-total').innerText = formatRupiah((Number(pokok) || 0) + (Number(infaq) || 0));

            document.getElementById('approve-form').action = "{{ url('payments') }}/" + id + "/approve";
            document.getElementById('reject-form').action = "{{ url('payments') }}/" + id + "/reject";

            document.getElementById('admin-modal').classList.remove('hidden');
            document.getElementById('admin-modal').classList.add('flex');
        }

        function closeAdminModal() {
            document.getElementById('admin-modal').classList.add('hidden');
            document.getElementById('admin-modal').classList.remove('flex');
        }
    </script>

    @if(config('services.midtrans.client_key') && config('services.midtrans.server_key'))
        <script src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            async function bayarViaMidtransSnap() {
                if (!activePaymentId || typeof window.snap === 'undefined') return;
                const btn = document.getElementById('btn-midtrans-snap');
                const infaq = Math.max(0, Number(document.getElementById('va-infaq-input').value) || 0);
                btn.disabled = true;
                btn.textContent = 'Memuat Midtrans...';

                try {
                    const res = await fetch("{{ url('payments') }}/" + activePaymentId + "/snap-token", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ infaq_amount: infaq }),
                    });
                    const data = await res.json();
                    if (!res.ok || !data.snap_token) {
                        alert(data.error || 'Gagal membuat token Midtrans.');
                        return;
                    }
                    closeVaModal();
                    window.snap.pay(data.snap_token, {
                        onSuccess: function () {
                            document.getElementById('va-form').submit();
                        },
                        onPending: function () {
                            window.location.reload();
                        },
                        onError: function () {
                            alert('Pembayaran gagal diproses.');
                        }
                    });
                } catch (e) {
                    alert('Gagal terhubung ke server.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = '💳 Buka Popup Midtrans (VA Bank & QRIS)';
                }
            }
        </script>
    @endif

</body>
</html>
