<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Pembayaran Syariah MI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-900 via-emerald-800 to-teal-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
        {{-- Header --}}
        <div class="bg-emerald-800 px-6 py-6 text-center text-white">
            <div class="text-4xl mb-2">🕌</div>
            <h1 class="text-lg font-bold tracking-wide">SIAKAD KEUNGAN MI SYARIAH</h1>
            <p class="text-xs text-emerald-200 mt-0.5">Sistem Pembayaran SPP, Virtual Account BSI & Infaq Transparan</p>
        </div>

        <div class="p-6">
            @if (session('status'))
                <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-600 mb-1">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                        placeholder="nama@mi.sch.id"
                        class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-600 mb-1">Kata Sandi</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full border border-slate-300 rounded-lg px-3.5 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Ingat saya</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-emerald-700 hover:underline font-medium">Lupa password?</a>
                    @endif
                </div>

                <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-semibold py-2.5 rounded-lg text-sm shadow-sm transition">
                    Masuk ke Sistem
                </button>
            </form>

            {{-- Panel Akun Pengelola (untuk Login Cepat) --}}
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider text-center mb-2.5">
                    ⚡ Login Cepat Pengelola Madrasah
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="quickLogin('admin@mi.sch.id')"
                        class="text-xs font-semibold py-2 px-2 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition text-center">
                        💼 Bendahara (Admin)
                        <span class="block text-[10px] font-normal text-emerald-600">admin@mi.sch.id</span>
                    </button>
                    <button type="button" onclick="quickLogin('kepala@mi.sch.id')"
                        class="text-xs font-semibold py-2 px-2 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100 transition text-center">
                        🎓 Kepala Madrasah
                        <span class="block text-[10px] font-normal text-blue-600">kepala@mi.sch.id</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 text-center mt-2">
                    Untuk <strong>Wali Santri</strong>, silakan masuk menggunakan email & password yang didaftarkan oleh Bendahara di menu <em>Data Wali</em>.
                </p>
            </div>
        </div>
    </div>

    <script>
        function quickLogin(email) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = 'password123';
            document.getElementById('login-form').submit();
        }
    </script>
</body>
</html>
