<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Murid MI Syariah</title>
    <meta name="description" content="Sistem Pengelolaan Siswa Madrasah Ibtidaiyah">
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
                <svg class="h-5 w-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- ===== FORM TAMBAH MURID (Admin saja) ===== --}}
        @if(auth()->user()->role === 'admin')
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2" id="form-title">
                <span class="text-emerald-600">👤</span> Tambah Data Murid Baru
            </h2>
            <form id="student-form" action="{{ route('students.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Murid</label>
                    <input type="text" name="nama" id="nama" required value="{{ old('nama') }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="Ahmad Fauzi">
                    @error('nama')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">NIS (Nomor Induk)</label>
                    <input type="text" name="nis" id="nis" required value="{{ old('nis') }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="120980">
                    @error('nis')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kelas</label>
                    <input type="text" name="kelas" id="kelas" required value="{{ old('kelas') }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="1-A">
                    @error('kelas')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Wali Murid (Penanggung Jawab)</label>
                    <select name="user_id" id="user_id" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                        <option value="">— Pilih Wali —</option>
                        @foreach($walis as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Kategori Santri (Keringanan Syariah)</label>
                    <select name="kategori" id="kategori"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none bg-white transition">
                        @foreach(['Reguler', 'Yatim/Piatu', 'Dhuafa', 'Beasiswa Prestasi'] as $kat)
                            <option value="{{ $kat }}">{{ $kat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Potongan SPP Otomatis (%)</label>
                    <input type="number" name="potongan_persen" id="potongan_persen" value="{{ old('potongan_persen', 0) }}" min="0" max="100"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="0">
                </div>
                <div class="md:col-span-2 flex justify-between items-end pt-1">
                    <button type="button" id="btn-cancel" class="hidden text-sm text-slate-500 hover:text-slate-700 font-semibold" onclick="resetForm()">
                        Batal Edit
                    </button>
                    <div class="flex-grow text-right">
                        <button type="submit" id="btn-submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150">
                            Simpan Data
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- ===== TABEL DAFTAR MURID ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                    <span>👥</span> Daftar Murid Madrasah
                </h2>
                <span class="text-xs text-slate-400">{{ $students->total() }} data murid</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="px-5 py-3 font-semibold">NIS</th>
                            <th class="px-5 py-3 font-semibold">Nama Murid</th>
                            <th class="px-5 py-3 font-semibold">Kelas & Kategori</th>
                            <th class="px-5 py-3 font-semibold">Wali Murid</th>
                            @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($students as $s)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-5 py-4 font-mono font-semibold text-slate-700">{{ $s->nis }}</td>
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $s->nama }}</td>
                            <td class="px-5 py-4">
                                <span class="bg-blue-50 text-blue-700 text-xs font-semibold px-2.5 py-1 rounded-md">
                                    Kelas {{ $s->kelas }}
                                </span>
                                @if(($s->kategori ?? 'Reguler') !== 'Reguler' || ($s->potongan_persen ?? 0) > 0)
                                    <span class="ml-1 bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold px-2 py-0.5 rounded-md">
                                        {{ $s->kategori }} {{ $s->potongan_persen > 0 ? '(-' . $s->potongan_persen . '% SPP)' : '' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-slate-600">
                                <div class="font-medium text-slate-800">{{ $s->wali->name ?? '— Tidak Ada Wali —' }}</div>
                                <div class="text-xs text-slate-400">{{ $s->wali->email ?? '' }}</div>
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="px-5 py-4 text-right">
                                <button type="button" 
                                    class="text-xs text-amber-600 hover:text-amber-700 font-semibold mr-3 transition"
                                    onclick="editStudent(@js($s))">
                                    Edit
                                </button>
                                <form action="{{ route('students.destroy', $s->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus data murid {{ $s->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-700 font-semibold transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-sm font-medium">Belum ada data murid</p>
                                    <p class="text-xs">Hubungi admin untuk mendaftarkan nama murid Anda ke sistem.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($students->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
            @endif
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ &nbsp;•&nbsp; Sistem Pembayaran Syariah MI &nbsp;•&nbsp; {{ date('Y') }}
        </p>

    </div>

    @if(auth()->user()->role === 'admin')
    <script>
        function editStudent(student) {
            document.getElementById('form-title').innerHTML = '<span class="text-amber-600">📝</span> Edit Data Murid: ' + student.nama;
            
            // Set input values
            document.getElementById('nama').value = student.nama;
            document.getElementById('nis').value = student.nis;
            document.getElementById('kelas').value = student.kelas;
            document.getElementById('user_id').value = student.user_id;
            document.getElementById('kategori').value = student.kategori || 'Reguler';
            document.getElementById('potongan_persen').value = student.potongan_persen || 0;

            // Set Form action & method
            const form = document.getElementById('student-form');
            form.action = "{{ url('students') }}/" + student.id;
            document.getElementById('form-method').value = "PUT";

            // Show cancel button
            document.getElementById('btn-cancel').classList.remove('hidden');
            document.getElementById('btn-submit').textContent = "Perbarui Data";
            document.getElementById('btn-submit').className = "inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150";
            
            // Scroll to form
            document.getElementById('form-title').scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').innerHTML = '<span class="text-emerald-600">👤</span> Tambah Data Murid Baru';
            
            // Reset input values
            document.getElementById('nama').value = '';
            document.getElementById('nis').value = '';
            document.getElementById('kelas').value = '';
            document.getElementById('user_id').value = '';
            document.getElementById('kategori').value = 'Reguler';
            document.getElementById('potongan_persen').value = 0;

            // Set Form action & method
            const form = document.getElementById('student-form');
            form.action = "{{ route('students.store') }}";
            document.getElementById('form-method').value = "POST";

            // Hide cancel button
            document.getElementById('btn-cancel').classList.add('hidden');
            document.getElementById('btn-submit').textContent = "Simpan Data";
            document.getElementById('btn-submit').className = "inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150";
        }
    </script>
    @endif

</body>
</html>
