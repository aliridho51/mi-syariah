{{-- ===== NAVBAR UTAMA ===== --}}
@php
    $navActive = 'text-sm font-semibold text-emerald-700 border-b-2 border-emerald-600 pb-1';
    $navIdle   = 'text-sm font-medium text-slate-600 hover:text-emerald-700 transition';
    $role      = auth()->user()->role ?? null;
@endphp
<nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
    <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between">
        <a href="{{ route('payments.index') }}" class="flex items-center gap-3">
            <span class="text-2xl">🕌</span>
            <div>
                <span class="font-bold text-emerald-800 text-base leading-tight block">MI Syariah</span>
                <span class="text-xs text-slate-400">Sistem Pembayaran Transparan</span>
            </div>
        </a>

        <div class="flex items-center gap-6">
            <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.index') ? $navActive : $navIdle }}">Pembayaran</a>
            <a href="{{ route('students.index') }}" class="{{ request()->routeIs('students.*') ? $navActive : $navIdle }}">Data Murid</a>
            @if($role === 'admin' || $role === 'kepala_madrasah')
                <a href="{{ route('walis.index') }}" class="{{ request()->routeIs('walis.*') ? $navActive : $navIdle }}">Data Wali</a>
                <a href="{{ route('payments.laporan') }}" class="{{ request()->routeIs('payments.laporan') ? $navActive : $navIdle }}">Laporan</a>
            @endif
            @if($role === 'admin')
                <a href="{{ route('master.index') }}" class="{{ request()->routeIs('master.*') ? $navActive : $navIdle }}">Master Data</a>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400">{{ auth()->user()->email }}</p>
            </div>
            <span class="bg-emerald-100 text-emerald-800 text-xs font-semibold px-3 py-1.5 rounded-full capitalize">
                {{ str_replace('_', ' ', $role ?? 'Tamu') }}
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 px-3 py-1.5 rounded-lg font-medium transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1v-2a1 1 0 10-2 0v1H4V5h6v1a1 1 0 102 0V4a1 1 0 00-1-1H3z" clip-rule="evenodd"/>
                        <path d="M13.293 7.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L14.586 12H8a1 1 0 110-2h6.586l-1.293-1.293a1 1 0 010-1.414z"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</nav>

