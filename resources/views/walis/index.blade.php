<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Wali Murid MI Syariah</title>
    <meta name="description" content="Sistem Pengelolaan Wali Murid Madrasah Ibtidaiyah">
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

        {{-- ===== FORM TAMBAH/EDIT WALI (Admin saja) ===== --}}
        @if(auth()->user()->role === 'admin')
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4 flex items-center gap-2" id="form-title">
                <span class="text-emerald-600">👤</span> Tambah Data Wali Murid Baru
            </h2>
            <form id="wali-form" action="{{ route('walis.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Wali Murid</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="Zainal Abidin">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Akun (Username)</label>
                    <input type="email" name="email" id="email" required value="{{ old('email') }}"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="zainal@gmail.com">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5" id="password-label">Password Akun (Min. 8 karakter)</label>
                    <input type="password" name="password" id="password" required
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition"
                        placeholder="Masukkan password">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-3 flex justify-between items-center pt-1">
                    <button type="button" id="btn-cancel" class="hidden text-sm text-slate-500 hover:text-slate-700 font-semibold" onclick="resetForm()">
                        Batal Edit
                    </button>
                    <div class="flex-grow text-right">
                        <button type="submit" id="btn-submit"
                            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150">
                            Daftarkan Wali
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- ===== TABEL DAFTAR WALI MURID ===== --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-700 flex items-center gap-2">
                    <span>👥</span> Daftar Akun Wali Murid
                </h2>
                <span class="text-xs text-slate-400">{{ $walis->total() }} akun</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                            <th class="px-5 py-3 font-semibold">Nama Lengkap</th>
                            <th class="px-5 py-3 font-semibold">Email</th>
                            <th class="px-5 py-3 font-semibold">Tanggal Daftar</th>
                            @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 font-semibold text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-600">
                        @forelse($walis as $w)
                        <tr class="hover:bg-slate-50 transition-colors duration-100">
                            <td class="px-5 py-4 font-semibold text-slate-900">{{ $w->name }}</td>
                            <td class="px-5 py-4 font-mono text-slate-600">{{ $w->email }}</td>
                            <td class="px-5 py-4 text-xs text-slate-500">
                                {{ $w->created_at->translatedFormat('d M Y') }}
                            </td>
                            @if(auth()->user()->role === 'admin')
                            <td class="px-5 py-4 text-right">
                                <button type="button" 
                                    class="text-xs text-amber-600 hover:text-amber-700 font-semibold mr-3 transition"
                                    onclick="editWali(@js($w))">
                                    Edit
                                </button>
                                <form action="{{ route('walis.destroy', $w->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun Wali {{ $w->name }}? Akun hanya bisa dihapus jika tidak memiliki data murid/tagihan.')">
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
                            <td colspan="4" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <p class="text-sm font-medium">Belum ada data Wali Murid</p>
                                    <p class="text-xs">Klik formulir di atas untuk mendaftarkan akun Wali Murid baru secara manual.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($walis->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $walis->links() }}
            </div>
            @endif
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ &nbsp;•&nbsp; Sistem Pembayaran Syariah MI &nbsp;•&nbsp; {{ date('Y') }}
        </p>

    </div>

    @if(auth()->user()->role === 'admin')
    <script>
        function editWali(wali) {
            document.getElementById('form-title').innerHTML = '<span class="text-amber-600">📝</span> Edit Data Wali: ' + wali.name;
            
            // Set input values
            document.getElementById('name').value = wali.name;
            document.getElementById('email').value = wali.email;
            
            // Password tidak wajib diisi saat edit
            document.getElementById('password').required = false;
            document.getElementById('password-label').innerHTML = 'Password Akun <span class="text-slate-400 font-normal">(Biarkan kosong jika tidak ingin diubah)</span>';

            // Set Form action & method
            const form = document.getElementById('wali-form');
            form.action = "{{ url('walis') }}/" + wali.id;
            document.getElementById('form-method').value = "PUT";

            // Show cancel button
            document.getElementById('btn-cancel').classList.remove('hidden');
            document.getElementById('btn-submit').textContent = "Perbarui Wali";
            document.getElementById('btn-submit').className = "inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150";
            
            // Scroll to form
            document.getElementById('form-title').scrollIntoView({ behavior: 'smooth' });
        }

        function resetForm() {
            document.getElementById('form-title').innerHTML = '<span class="text-emerald-600">👤</span> Tambah Data Wali Murid Baru';
            
            // Reset input values
            document.getElementById('name').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            
            // Password wajib diisi lagi
            document.getElementById('password').required = true;
            document.getElementById('password-label').innerHTML = 'Password Akun (Min. 8 karakter)';

            // Set Form action & method
            const form = document.getElementById('wali-form');
            form.action = "{{ route('walis.store') }}";
            document.getElementById('form-method').value = "POST";

            // Hide cancel button
            document.getElementById('btn-cancel').classList.add('hidden');
            document.getElementById('btn-submit').textContent = "Daftarkan Wali";
            document.getElementById('btn-submit').className = "inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold px-6 py-2 rounded-lg text-sm transition-colors duration-150";
        }
    </script>
    @endif

</body>
</html>
