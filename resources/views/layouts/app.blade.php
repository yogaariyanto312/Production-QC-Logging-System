<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QC Production') — QC System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        // Base URL yang selalu ikut protokol browser (aman untuk http & https, XAMPP & hosting)
        window.APP_BASE = window.location.origin + '{{ rtrim(parse_url(url("/"), PHP_URL_PATH) ?? "", "/") }}';
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 font-sans antialiased">

@php
    $__maint        = \App\Models\BotSetting::instance();
    $__maintActive  = $__maint->maintenance_mode
        && !auth()->user()->isDeveloper()
        && (!$__maint->maintenance_until || now()->lt($__maint->maintenance_until));
@endphp
@if($__maintActive)
<div class="fixed inset-0 z-9999 flex flex-col items-center justify-center px-6"
     style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);">

    {{-- animated blobs --}}
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative flex flex-col items-center text-center max-w-md w-full">

        {{-- icon --}}
        <div class="w-24 h-24 mb-6 rounded-3xl bg-amber-500/20 border border-amber-500/30 flex items-center justify-center">
            <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>

        {{-- badge --}}
        <span class="inline-flex items-center gap-1.5 px-3 py-1 mb-4 bg-amber-500/20 border border-amber-500/40 text-amber-400 text-xs font-bold rounded-full uppercase tracking-widest">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
            Under Maintenance
        </span>

        {{-- heading --}}
        <h1 class="text-3xl font-extrabold text-white mb-3">Sistem Sedang Maintenance</h1>

        {{-- message --}}
        <p class="text-slate-400 text-sm leading-relaxed mb-6">
            {{ $__maint->maintenance_message ?: 'Kami sedang melakukan pemeliharaan sistem. Harap bersabar, layanan akan kembali normal secepatnya.' }}
        </p>

        {{-- countdown (only if maintenance_until is set) --}}
        @if($__maint->maintenance_until)
        <div class="w-full mb-6 px-5 py-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl">
            <p class="text-xs text-amber-400 font-semibold uppercase tracking-wide mb-2">Estimasi Selesai</p>
            <p class="text-sm text-slate-300 mb-3">
                {{ $__maint->maintenance_until->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB
            </p>
            <div id="maint-countdown" class="flex justify-center gap-3 text-center">
                <div class="flex flex-col items-center">
                    <span id="maint-cd-h" class="text-2xl font-extrabold text-white tabular-nums">--</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wide mt-0.5">Jam</span>
                </div>
                <span class="text-2xl font-bold text-slate-600 self-start mt-0.5">:</span>
                <div class="flex flex-col items-center">
                    <span id="maint-cd-m" class="text-2xl font-extrabold text-white tabular-nums">--</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wide mt-0.5">Menit</span>
                </div>
                <span class="text-2xl font-bold text-slate-600 self-start mt-0.5">:</span>
                <div class="flex flex-col items-center">
                    <span id="maint-cd-s" class="text-2xl font-extrabold text-amber-400 tabular-nums">--</span>
                    <span class="text-[10px] text-slate-500 uppercase tracking-wide mt-0.5">Detik</span>
                </div>
            </div>
        </div>
        <script>
        (function () {
            var end = new Date('{{ $__maint->maintenance_until->toIso8601String() }}').getTime();
            function pad(n) { return String(n).padStart(2, '0'); }
            function tick() {
                var diff = end - Date.now();
                if (diff <= 0) { location.reload(); return; }
                var h = Math.floor(diff / 3600000);
                var m = Math.floor((diff % 3600000) / 60000);
                var s = Math.floor((diff % 60000) / 1000);
                document.getElementById('maint-cd-h').textContent = pad(h);
                document.getElementById('maint-cd-m').textContent = pad(m);
                document.getElementById('maint-cd-s').textContent = pad(s);
            }
            tick();
            setInterval(tick, 1000);
        })();
        </script>
        @endif

        {{-- user info --}}
        <div class="w-full flex items-center gap-3 px-4 py-3 bg-white/5 border border-white/10 rounded-2xl mb-6">
            <div class="w-9 h-9 rounded-full bg-slate-600 flex items-center justify-center shrink-0 text-sm font-bold text-white">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="text-left flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }}</p>
            </div>
        </div>

        {{-- logout button --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-semibold rounded-2xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>

    </div>
</div>
@endif

<div class="flex h-full">

    {{-- Sidebar Overlay (mobile) --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden"></div>

    {{-- ====== SIDEBAR ====== --}}
    <aside id="sidebar"
           class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 dark:bg-slate-950 text-white flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-700">
            <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-white leading-tight">QC Production</p>
                <p class="text-xs text-slate-400">Sistem Pencatatan</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
                $all      = ['developer', 'admin', 'supervisor', 'operator'];
                $withVisitor = ['developer', 'admin', 'supervisor', 'operator', 'visitor'];
                $navItems = [
                    ['route' => 'dashboard',         'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard',        'roles' => $withVisitor],
                    ['route' => 'notes.index',       'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'label' => 'Catatan',             'roles' => $all],
                    ['route' => 'production.targets.index', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Target Produksi', 'roles' => ['developer', 'admin', 'supervisor', 'operator']],
                    ['route' => 'chatting',          'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z', 'label' => 'Chatting',           'roles' => $withVisitor],
                    ['route' => 'gambar-kerja.index','icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Gambar Kerja',    'roles' => $withVisitor],
                    ['route' => 'production.create', 'icon' => 'M12 4v16m8-8H4', 'label' => 'Input Produksi',                                                                                                                                                        'roles' => $all],
                    ['route' => 'production.index',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Riwayat Produksi', 'roles' => $withVisitor],
                    ['route' => 'products.index',    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Master Produk',        'roles' => ['developer', 'admin', 'supervisor']],
                    ['route' => 'reports.index',     'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Laporan',            'roles' => ['developer', 'admin', 'supervisor']],
                    ['route' => 'categories.index',       'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'label' => 'Kategori',           'roles' => ['developer']],
                    ['route' => 'management.index',  'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'label' => 'Manajemen', 'roles' => ['developer', 'admin', 'operator'], 'activeRoutes' => ['management.*', 'admins.*', 'supervisors.*', 'operators.*', 'developers.*']],
                    ['route' => 'tutorial', 'label' => 'Tutorial', 'roles' => $withVisitor, 'paths' => [
                        'M11.5 6H4a1 1 0 00-1 1v11a1 1 0 001 1h7.5V6z',
                        'M12.5 6H20a1 1 0 011 1v11a1 1 0 01-1 1h-7.5V6z',
                        'M11.5 18.5c.3.4.8.5 1.3.5s1-.3 1.3-.5',
                        'M12 1a3 3 0 100 6 3 3 0 000-6z',
                        'M11 3l3 1-3 1V3z',
                        'M4.5 10h5.5M4.5 12.5h3.5M4.5 15h5.5',
                        'M13.5 10h5.5M13.5 12.5h3.5M13.5 15h5.5',
                    ]],
                    ['route' => 'developer.bot-settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Settings', 'roles' => ['developer']],
                    ['route' => 'about',             'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => 'Tentang Aplikasi',   'roles' => $withVisitor],
                ];
            @endphp

            @foreach($navItems as $item)
                @if(in_array(auth()->user()->role, $item['roles']))
                    @php
                        $isActive = isset($item['activeRoutes'])
                            ? collect($item['activeRoutes'])->some(fn($p) => request()->routeIs($p))
                            : request()->routeIs($item['route'].'*');
                    @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $isActive
                                 ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/30'
                                 : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @foreach($item['paths'] ?? [$item['icon']] as $p)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p }}"/>
                            @endforeach
                        </svg>
                        {{ $item['label'] }}
                        <span class="ml-auto flex items-center gap-1.5">
                            @if($item['route'] === 'chatting')
                            <span id="sidebar-chat-badge"
                                  class="hidden min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-[10px] font-bold
                                         rounded-full flex items-center justify-center leading-none"></span>
                            @endif
                            @if($isActive)
                            <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                            @endif
                        </span>
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- User info + dropdown --}}
        <div class="px-4 py-4 border-t border-slate-700 relative" x-data="{ open: false }">
            {{-- Trigger --}}
            <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-800 transition-colors group">
                <div class="w-9 h-9 rounded-full shrink-0 overflow-hidden bg-blue-700 flex items-center justify-center">
                    {{-- Inisial selalu ada sebagai fallback --}}
                    <span class="text-sm font-bold text-white" style="{{ auth()->user()->avatar ? 'display:none' : '' }}"
                          id="sidebar-avatar-initials">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatarUrl() }}" alt=""
                         class="w-full h-full object-cover"
                         onerror="this.style.display='none'; document.getElementById('sidebar-avatar-initials').style.display='flex'">
                    @endif
                </div>
                <div class="flex-1 min-w-0 text-left">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>

            {{-- Dropdown menu --}}
            <div x-show="open" x-cloak
                 @click.outside="open = false"
                 class="absolute bottom-full left-4 right-4 mb-2 bg-slate-800 border border-slate-700 rounded-xl shadow-xl overflow-hidden">

                {{-- Profile info header --}}
                <div class="px-4 py-3 border-b border-slate-700">
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? auth()->user()->username }}</p>
                </div>

                {{-- Menu items --}}
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Saya
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-slate-700 hover:text-red-300 transition-colors">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ====== MAIN CONTENT ====== --}}
    <div class="flex-1 flex flex-col min-w-0 lg:pl-64">

        {{-- Top Navbar --}}
        <header class="sticky top-0 z-10 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 shadow-sm">
            <div class="flex items-center justify-between h-16 px-4 lg:px-6">
                <div class="flex items-center gap-4">
                    {{-- Mobile sidebar toggle --}}
                    <button id="sidebar-toggle"
                            class="lg:hidden p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- Page title --}}
                    <div>
                        <h1 class="text-base font-semibold text-slate-800 dark:text-white">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">@yield('page-subtitle', 'QC Production System')</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Date + Live Clock --}}
                    <span class="hidden sm:flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        {{ now()->translatedFormat('l, d F Y') }}
                        <span class="w-px h-3 bg-slate-300 dark:bg-slate-600"></span>
                        <span id="live-clock" class="font-mono tabular-nums"></span>
                    </span>

                    {{-- Dark mode toggle --}}
                    <button id="dark-toggle"
                            class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700">
                        <svg class="w-5 h-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- Flash Messages (Toast) --}}
        @if(session('success') || session('error') || session('warning'))
        <div class="fixed top-4 right-4 z-50 flex flex-col gap-2" id="toast-container">
            @if(session('success'))
            <div class="toast-auto flex items-center gap-3 px-4 py-3 bg-green-600 text-white rounded-xl shadow-lg max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
            @endif
            @if(session('error'))
            <div class="toast-auto flex items-center gap-3 px-4 py-3 bg-red-600 text-white rounded-xl shadow-lg max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
            @endif
            @if(session('warning'))
            <div class="toast-auto flex items-center gap-3 px-4 py-3 bg-amber-500 text-white rounded-xl shadow-lg max-w-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm font-medium">{{ session('warning') }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Main Page Content --}}
        <main class="@yield('main-class', 'flex-1 p-4 lg:p-6 overflow-auto')">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="py-3 px-6 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            <p class="text-xs text-center text-slate-400">
                &copy; {{ now()->year }} QC Production System. All rights reserved.
            </p>
        </footer>
    </div>
</div>

<script>
(function () {
    var el = document.getElementById('live-clock');
    if (!el) return;
    function tick() {
        var now = new Date();
        var h   = now.getHours();
        var m   = now.getMinutes();
        var s   = now.getSeconds();
        var ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        el.textContent = h + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0') + ' ' + ampm;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>

@stack('scripts')

@if(\App\Models\BotSetting::instance()->disable_devtools && !auth()->user()->isDeveloper())
<style>
#devtools-toast {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
    z-index: 9999; display: flex; align-items: center; gap: 0.625rem;
    padding: 0.75rem 1.25rem; border-radius: 0.875rem;
    background: #1e293b; color: #f1f5f9;
    font-size: 0.8125rem; font-weight: 600; font-family: inherit;
    box-shadow: 0 8px 32px rgba(0,0,0,.35);
    white-space: nowrap; pointer-events: none;
    opacity: 0; transition: opacity .2s ease;
}
#devtools-toast.show { opacity: 1; }
</style>
<div id="devtools-toast">
    <svg style="width:1rem;height:1rem;flex-shrink:0;color:#f87171" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <span id="devtools-toast-msg">Akses diblokir oleh Developer.</span>
</div>
<script>
(function() {
    var toast = document.getElementById('devtools-toast');
    var msg   = document.getElementById('devtools-toast-msg');
    var timer = null;

    function showToast(text) {
        msg.textContent = text;
        toast.classList.add('show');
        clearTimeout(timer);
        timer = setTimeout(function() { toast.classList.remove('show'); }, 2500);
    }

    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        showToast('Klik kanan dinonaktifkan oleh Developer.');
    });

    document.addEventListener('keydown', function(e) {
        var blocked = false;
        var label   = '';

        if (e.key === 'F12') {
            blocked = true; label = 'F12 (DevTools) diblokir oleh Developer.';
        } else if (e.ctrlKey && e.shiftKey && e.key.toUpperCase() === 'I') {
            blocked = true; label = 'Ctrl+Shift+I (Inspect) diblokir oleh Developer.';
        } else if (e.ctrlKey && e.shiftKey && e.key.toUpperCase() === 'J') {
            blocked = true; label = 'Ctrl+Shift+J (Console) diblokir oleh Developer.';
        } else if (e.ctrlKey && e.shiftKey && e.key.toUpperCase() === 'C') {
            blocked = true; label = 'Ctrl+Shift+C (Inspect) diblokir oleh Developer.';
        } else if (e.ctrlKey && e.key.toUpperCase() === 'U') {
            blocked = true; label = 'Ctrl+U (View Source) diblokir oleh Developer.';
        }

        if (blocked) {
            e.preventDefault();
            e.stopPropagation();
            showToast(label);
            return false;
        }
    }, true);
})();
</script>
@endif

@if(auth()->check() && auth()->user()->isPrivileged())
<style>
@keyframes msg-shrink { from { width:100% } to { width:0% } }
.msg-toast-bar { animation: msg-shrink 5s linear forwards; }
</style>

<div x-data="globalMsgToast()">
    <div class="fixed bottom-5 right-5 z-60 flex flex-col gap-3 pointer-events-none" style="width:22rem">
        <template x-for="t in toasts" :key="t.id">
            <div class="pointer-events-auto bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 overflow-hidden"
                 x-show="t.visible"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4">
                <div class="flex items-start gap-3 p-4 cursor-pointer"
                     @click="window.location.href='{{ route('chatting') }}'">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-white" x-text="t.initial"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wide">Pesan Baru</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-white mt-0.5" x-text="t.senderName"></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2" x-text="t.preview"></p>
                    </div>
                    <button @click.stop="dismiss(t.id)"
                            class="text-slate-300 hover:text-slate-500 dark:hover:text-slate-400 shrink-0 p-0.5 -mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="h-1 bg-slate-100 dark:bg-slate-700">
                    <div class="h-full bg-blue-500 rounded-full msg-toast-bar"></div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function globalMsgToast() {
    let knownIds = new Set();
    let seeded   = false;

    return {
        toasts: [],

        push(msg) {
            const uid = Date.now() + Math.random();
            this.toasts.push({
                id:         uid,
                senderName: msg.sender?.name ?? 'Operator',
                initial:    (msg.sender?.name ?? 'O').charAt(0).toUpperCase(),
                preview:    msg.message.length > 80 ? msg.message.slice(0, 80) + '…' : msg.message,
                visible:    true,
            });
            setTimeout(() => this.dismiss(uid), 5000);
        },

        dismiss(uid) {
            const t = this.toasts.find(t => t.id === uid);
            if (t) t.visible = false;
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== uid); }, 300);
        },

        async poll() {
            try {
                const res   = await fetch(window.APP_BASE + '/messages', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const fresh = await res.json();

                if (!seeded) {
                    // load pertama: isi knownIds tanpa tampilkan toast
                    fresh.forEach(m => knownIds.add(m.id));
                    seeded = true;
                } else {
                    const onChat = ['/messages', '/chatting'].some(p => window.location.pathname.replace(/\/$/, '').endsWith(p));
                    fresh.filter(m => !knownIds.has(m.id)).forEach(m => {
                        knownIds.add(m.id);
                        if (!onChat) this.push(m);
                    });
                }
            } catch(e) {}
        },

        init() {
            this.poll();
            setInterval(() => this.poll(), 30000);
        }
    };
}
</script>
@endif

{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed bottom-20 right-4 z-[9999] flex flex-col gap-2 pointer-events-none" style="max-width:320px"></div>

<script>
(function () {
    const NOTIF_URL   = window.APP_BASE + '/api/notifications';
    const CHAT_URL    = window.APP_BASE + '/chatting';
    const NOTES_URL   = window.APP_BASE + '/notes';
    const MSG_KEY     = 'notif_last_msg_id';
    const NOTE_KEY    = 'notif_notes_shown';
    const IS_CHAT     = /\/(chatting|chat)\b/.test(window.location.pathname);

    let lastMsgId  = parseInt(localStorage.getItem(MSG_KEY) || '0');
    let shownNotes = (() => { try { return JSON.parse(localStorage.getItem(NOTE_KEY) || '[]'); } catch { return []; } })();
    let synced     = false; // true setelah poll pertama selesai (baseline sudah set)

    // Escape HTML — cegah XSS dari konten user (nama pengirim, isi pesan, judul catatan)
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    // ── Toast ──────────────────────────────────────────────────────────────────
    function showToast(icon, title, body, url, colorClass) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const id = 'toast-' + Date.now() + Math.random();
        const el = document.createElement('div');
        el.id = id;
        el.className = [
            'pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-2xl shadow-2xl border',
            'text-sm text-white cursor-pointer select-none',
            'translate-x-[110%] opacity-0 transition-all duration-300 ease-out',
            colorClass,
        ].join(' ');

        el.innerHTML = `
            <span class="text-xl shrink-0 mt-0.5">${icon}</span>
            <div class="flex-1 min-w-0">
                <p class="font-semibold leading-tight truncate">${esc(title)}</p>
                <p class="text-xs opacity-80 mt-0.5" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${esc(body)}</p>
            </div>
            <button class="shrink-0 text-white/60 hover:text-white text-xl leading-none px-1"
                    onclick="event.stopPropagation();document.getElementById('${id}')?.remove()">×</button>`;

        el.addEventListener('click', () => { window.location.href = url; });
        container.appendChild(el);

        // Animate in
        requestAnimationFrame(() => requestAnimationFrame(() => {
            el.classList.remove('translate-x-[110%]', 'opacity-0');
        }));

        // Auto dismiss
        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-x-[110%]');
            setTimeout(() => el.remove(), 320);
        }, 6000);
    }

    // ── Poll ───────────────────────────────────────────────────────────────────
    async function poll() {
        try {
            const res = await fetch(`${NOTIF_URL}?last_msg_id=${lastMsgId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();

            const serverLastId  = parseInt(data.last_msg_id || lastMsgId);
            const newMsgs       = Array.isArray(data.messages) ? data.messages : [];
            const noteReminders = Array.isArray(data.note_reminders) ? data.note_reminders : [];

            // Poll pertama: hanya set baseline tanpa tampilkan toast
            if (!synced) {
                lastMsgId = serverLastId;
                localStorage.setItem(MSG_KEY, lastMsgId);
                synced = true;
                // Note reminders tetap ditampilkan pada poll pertama (bukan "pesan baru")
            } else {
                // Pesan baru: tampilkan toast jika bukan di halaman chat
                if (!IS_CHAT && newMsgs.length > 0) {
                    // Max 3 toast sekaligus agar tidak spam
                    newMsgs.slice(0, 3).forEach(m => {
                        showToast('💬', m.sender_name, m.preview, CHAT_URL,
                            'bg-blue-700/95 border-blue-500');
                    });
                    if (newMsgs.length > 3) {
                        showToast('💬', `+${newMsgs.length - 3} pesan lainnya`, 'Buka chatting untuk melihat semua', CHAT_URL,
                            'bg-slate-700/95 border-slate-500');
                    }
                }
                // Update lastMsgId selalu (chat page atau bukan)
                if (serverLastId > lastMsgId) {
                    lastMsgId = serverLastId;
                    localStorage.setItem(MSG_KEY, lastMsgId);
                }
            }

            // Update badge sidebar Chatting
            const badge = document.getElementById('sidebar-chat-badge');
            if (badge) {
                const count = parseInt(data.unread_count || 0);
                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                } else {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
            }

            // Catatan jatuh tempo: tampil sekali per note per hari
            if (noteReminders.length > 0) {
                const today    = new Date().toDateString();
                const cleanOld = shownNotes.filter(n => n.date === today);
                const shownIds = new Set(cleanOld.map(n => n.id));

                noteReminders.forEach(n => {
                    if (!shownIds.has(n.id)) {
                        showToast('📋', 'Catatan jatuh tempo hari ini', n.title, NOTES_URL,
                            'bg-amber-600/95 border-amber-400');
                        cleanOld.push({ id: n.id, date: today });
                        shownIds.add(n.id);
                    }
                });

                shownNotes = cleanOld;
                localStorage.setItem(NOTE_KEY, JSON.stringify(shownNotes));
            }
        } catch (e) {
            // Gagal fetch — coba lagi di interval berikutnya
        }
    }

    // Poll pertama langsung (sync baseline), lalu setiap 8 detik
    poll();
    setInterval(poll, 8000);

}());
</script>

{{-- Scroll to top button --}}
<button id="scroll-top-btn"
        onclick="window.scrollTo({top:0,behavior:'smooth'})"
        title="Kembali ke atas"
        class="fixed bottom-6 right-5 z-50 w-10 h-10 rounded-full
               bg-slate-700 hover:bg-slate-600 dark:bg-slate-600 dark:hover:bg-slate-500
               text-white shadow-lg flex items-center justify-center
               opacity-0 pointer-events-none transition-all duration-300 scale-90">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
    </svg>
</button>
<script>
(function () {
    const btn = document.getElementById('scroll-top-btn');
    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
            btn.classList.remove('opacity-0', 'pointer-events-none', 'scale-90');
            btn.classList.add('opacity-100', 'scale-100');
        } else {
            btn.classList.add('opacity-0', 'pointer-events-none', 'scale-90');
            btn.classList.remove('opacity-100', 'scale-100');
        }
    }, { passive: true });
}());
</script>
</body>
</html>
