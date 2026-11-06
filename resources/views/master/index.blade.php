<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Pembayaran — MI Syariah</title>
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

        @if(session('success'))
            <div class="fade-in mb-5 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-xl shadow-sm">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="fade-in mb-5 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl shadow-sm">
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ===================== KOLOM KIRI: TAHUN AJARAN ===================== --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 id="tahun-form-title" class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="text-emerald-600">📅</span> Tambah Tahun Ajaran
                    </h2>
                    <form id="tahun-form" action="{{ route('master.tahun.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_method" id="tahun-method" value="POST">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Periode Tahun Ajaran</label>
                            <input type="text" name="nama" id="tahun-nama" required value="{{ old('nama') }}"
                                placeholder="2026/2027"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                            @error('nama')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <label id="tahun-aktif-wrap" class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="aktif" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                            Jadikan tahun ajaran aktif sekarang
                        </label>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" id="tahun-cancel" onclick="resetTahunForm()"
                                class="hidden px-4 py-2 border border-slate-300 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" id="tahun-submit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-5 py-2 rounded-lg text-xs transition">
                                Simpan Tahun Ajaran
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-700 text-sm">Daftar Tahun Ajaran</h3>
                        <span class="text-xs text-slate-400">{{ $tahunAjarans->count() }} periode</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse($tahunAjarans as $ta)
                        <div class="px-5 py-3.5 flex items-center justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-slate-800 text-sm">{{ $ta->nama }}</span>
                                    @if($ta->aktif)
                                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">Aktif</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-400">{{ $ta->payments_count }} tagihan</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(! $ta->aktif)
                                <form action="{{ route('master.tahun.aktifkan', $ta->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-emerald-600 hover:text-emerald-700 font-semibold">Aktifkan</button>
                                </form>
                                @endif
                                <button type="button" onclick="editTahun(@js($ta))" class="text-[11px] text-amber-600 hover:text-amber-700 font-semibold">Edit</button>
                                <form action="{{ route('master.tahun.destroy', $ta->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-[11px] text-rose-600 hover:text-rose-700 font-semibold">Hapus</button>
                                </form>
                            </div>
                        </div>
                        @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-400">Belum ada tahun ajaran.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================== KOLOM KANAN: JENIS PEMBAYARAN ===================== --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                    <h2 id="jenis-form-title" class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2">
                        <span class="text-emerald-600">💳</span> Tambah Jenis Pembayaran Syariah
                    </h2>
                    <form id="jenis-form" action="{{ route('master.jenis.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <input type="hidden" name="_method" id="jenis-method" value="POST">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Pembayaran</label>
                            <input type="text" name="nama" id="jenis-nama" required
                                placeholder="Contoh: SPP / Uang Gedung"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nominal Default (Rp)</label>
                            <input type="number" name="nominal" id="jenis-nominal" required min="0"
                                placeholder="150000"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Akad Syariah</label>
                            <select name="akad" id="jenis-akad" required
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                                @foreach(\App\Models\JenisPembayaran::AKAD as $kode => $label)
                                    <option value="{{ $kode }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Keterangan (Opsional)</label>
                            <input type="text" name="keterangan" id="jenis-keterangan"
                                placeholder="Biaya pendidikan bulanan"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                        </div>
                        <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3 pt-1">
                            <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="is_bulanan" id="jenis-bulanan" value="1" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span>Ditagih setiap bulan (Juli s.d. Juni, seperti SPP)</span>
                            </label>
                            <div class="flex gap-2">
                                <button type="button" id="jenis-cancel" onclick="resetJenisForm()"
                                    class="hidden px-4 py-2 border border-slate-300 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-50 transition">
                                    Batal
                                </button>
                                <button type="submit" id="jenis-submit"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-6 py-2 rounded-lg text-xs transition">
                                    Simpan Jenis Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="font-semibold text-slate-700 text-sm">Daftar Jenis Pembayaran</h3>
                        <span class="text-xs text-slate-400">{{ $jenisPembayarans->count() }} jenis</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                    <th class="px-5 py-3 font-semibold">Nama</th>
                                    <th class="px-5 py-3 font-semibold">Nominal Default</th>
                                    <th class="px-5 py-3 font-semibold">Akad Syariah</th>
                                    <th class="px-5 py-3 font-semibold">Siklus</th>
                                    <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                                @forelse($jenisPembayarans as $j)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-800">{{ $j->nama }}</div>
                                        @if($j->keterangan)
                                            <div class="text-[11px] text-slate-400">{{ $j->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-slate-800">Rp {{ number_format($j->nominal, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3.5 text-xs">
                                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-medium">
                                            {{ $j->akad_label }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs">
                                        @if($j->is_bulanan)
                                            <span class="text-blue-600 font-semibold">Bulanan</span>
                                        @else
                                            <span class="text-slate-500">Sekali / Tahunan</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <button type="button" onclick="editJenis(@js($j))" class="text-xs text-amber-600 hover:text-amber-700 font-semibold mr-3">Edit</button>
                                        <form action="{{ route('master.jenis.destroy', $j->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Hapus jenis pembayaran {{ $j->nama }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-10 text-center text-xs text-slate-400">Belum ada jenis pembayaran.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ &nbsp;•&nbsp; Sistem Pembayaran Syariah MI &nbsp;•&nbsp; {{ date('Y') }}
        </p>

        {{-- Tombol Reset Data ke Nol (Hapus Semua Wali, Murid & Tagihan) --}}
        <div class="mt-6 bg-rose-50 border border-rose-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h4 class="text-xs font-bold text-rose-800">🗑️ Kosongkan Seluruh Data (Mulai dari Nol)</h4>
                <p class="text-[11px] text-rose-700">Menghapus semua akun Wali Murid, Data Murid, dan Tagihan agar Anda bisa menginput ulang dari kosong.</p>
            </div>
            <form action="{{ route('master.resetData') }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus SEMUA data Wali Murid, Data Murid, dan Tagihan?')">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">
                    Kosongkan Semua Data Sekarang
                </button>
            </form>
        </div>
    </div>

    <script>
        function editTahun(ta) {
            document.getElementById('tahun-form-title').innerHTML = '<span class="text-amber-600">📝</span> Edit Tahun Ajaran: ' + ta.nama;
            document.getElementById('tahun-nama').value = ta.nama;
            document.getElementById('tahun-form').action = "{{ url('master/tahun') }}/" + ta.id;
            document.getElementById('tahun-method').value = 'PUT';
            document.getElementById('tahun-aktif-wrap').classList.add('hidden');
            document.getElementById('tahun-cancel').classList.remove('hidden');
            document.getElementById('tahun-submit').textContent = 'Perbarui';
        }
        function resetTahunForm() {
            document.getElementById('tahun-form-title').innerHTML = '<span class="text-emerald-600">📅</span> Tambah Tahun Ajaran';
            document.getElementById('tahun-nama').value = '';
            document.getElementById('tahun-form').action = "{{ route('master.tahun.store') }}";
            document.getElementById('tahun-method').value = 'POST';
            document.getElementById('tahun-aktif-wrap').classList.remove('hidden');
            document.getElementById('tahun-cancel').classList.add('hidden');
            document.getElementById('tahun-submit').textContent = 'Simpan Tahun Ajaran';
        }

        function editJenis(j) {
            document.getElementById('jenis-form-title').innerHTML = '<span class="text-amber-600">📝</span> Edit Jenis Pembayaran: ' + j.nama;
            document.getElementById('jenis-nama').value = j.nama;
            document.getElementById('jenis-nominal').value = Math.round(j.nominal);
            document.getElementById('jenis-akad').value = j.akad;
            document.getElementById('jenis-keterangan').value = j.keterangan || '';
            document.getElementById('jenis-bulanan').checked = !!j.is_bulanan;
            document.getElementById('jenis-form').action = "{{ url('master/jenis') }}/" + j.id;
            document.getElementById('jenis-method').value = 'PUT';
            document.getElementById('jenis-cancel').classList.remove('hidden');
            document.getElementById('jenis-submit').textContent = 'Perbarui Jenis';
        }
        function resetJenisForm() {
            document.getElementById('jenis-form-title').innerHTML = '<span class="text-emerald-600">💳</span> Tambah Jenis Pembayaran Syariah';
            document.getElementById('jenis-nama').value = '';
            document.getElementById('jenis-nominal').value = '';
            document.getElementById('jenis-akad').value = 'ijarah';
            document.getElementById('jenis-keterangan').value = '';
            document.getElementById('jenis-bulanan').checked = false;
            document.getElementById('jenis-form').action = "{{ route('master.jenis.store') }}";
            document.getElementById('jenis-method').value = 'POST';
            document.getElementById('jenis-cancel').classList.add('hidden');
            document.getElementById('jenis-submit').textContent = 'Simpan Jenis Pembayaran';
        }
    </script>
</body>
</html>

