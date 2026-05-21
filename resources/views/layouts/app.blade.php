<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'QC Production') — QC System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="h-full bg-slate-50 dark:bg-slate-900 font-sans antialiased">

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
                $navItems = [
                    ['route' => 'dashboard',        'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', 'label' => 'Dashboard', 'role' => 'all'],
                    ['route' => 'production.create','icon' => 'M12 4v16m8-8H4', 'label' => 'Input Produksi', 'role' => 'all'],
                    ['route' => 'production.index', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'label' => 'Riwayat Produksi', 'role' => 'all'],
                    ['route' => 'chat',             'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z', 'label' => 'Chat Admin', 'role' => 'operator'],
                    ['route' => 'messages.index',   'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z', 'label' => 'Pesan Masuk', 'role' => 'admin'],
                    ['route' => 'products.index',   'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Master Produk', 'role' => 'admin'],
                    ['route' => 'categories.index', 'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'label' => 'Kategori', 'role' => 'admin'],
                    ['route' => 'operators.index',   'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Operator', 'role' => 'admin'],
                    ['route' => 'admins.index',      'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Manajemen Admin', 'role' => 'admin'],
                    ['route' => 'departments.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Departemen', 'role' => 'admin'],
                    ['route' => 'reports.index',    'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Laporan', 'role' => 'admin'],
                ];
            @endphp

            @foreach($navItems as $item)
                @if($item['role'] === 'all' || auth()->user()->role === $item['role'])
                    @php $isActive = request()->routeIs($item['route'].'*') @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all
                              {{ $isActive
                                 ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/30'
                                 : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        {{ $item['label'] }}
                        @if($isActive)
                            <span class="ml-auto w-1.5 h-1.5 bg-white rounded-full"></span>
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- User info + dropdown --}}
        <div class="px-4 py-4 border-t border-slate-700 relative" x-data="{ open: false }">
            {{-- Trigger --}}
            <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-3 rounded-xl px-2 py-1.5 hover:bg-slate-800 transition-colors group">
                <div class="w-9 h-9 rounded-full bg-blue-700 flex items-center justify-center shrink-0">
                    <span class="text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
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
                    @if(auth()->user()->phone)
                    <p class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->phone }}</p>
                    @endif
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
                    {{-- Date --}}
                    <span class="hidden sm:block text-xs text-slate-500 dark:text-slate-400">
                        {{ now()->translatedFormat('l, d F Y') }}
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
        <main class="flex-1 p-4 lg:p-6 overflow-auto">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="py-3 px-6 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
            <p class="text-xs text-center text-slate-400">
                &copy; {{ now()->year }} QC Production System · Versi 1.0
            </p>
        </footer>
    </div>
</div>

@stack('scripts')

@if(auth()->check() && auth()->user()->role === 'admin')
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
                     @click="window.location.href='{{ route('messages.index') }}'">
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
                const res   = await fetch('/messages', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const fresh = await res.json();

                if (!seeded) {
                    // load pertama: isi knownIds tanpa tampilkan toast
                    fresh.forEach(m => knownIds.add(m.id));
                    seeded = true;
                } else {
                    const onChat = window.location.pathname.replace(/\/$/, '').endsWith('/messages');
                    fresh.filter(m => !knownIds.has(m.id)).forEach(m => {
                        knownIds.add(m.id);
                        if (!onChat) this.push(m);
                    });
                }
            } catch(e) {}
        },

        init() {
            this.poll();
            setInterval(() => this.poll(), 15000);
        }
    };
}
</script>
@endif
</body>
</html>
