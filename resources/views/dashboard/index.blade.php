@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan produksi hari ini')

@section('content')
<div class="space-y-6">

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Today Input --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 px-2 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full pulse-dot"></span>Live
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['today_total']) }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total Unit Hari Ini</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['today_entries'] }} entri produksi</p>
        </div>

        {{-- Monthly Total --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['monthly_total']) }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total Bulan Ini</p>
            <p class="text-xs text-slate-400 mt-1">{{ now()->translatedFormat('F Y') }}</p>
        </div>

        {{-- Total Products --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total_products'] }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Produk Aktif</p>
            <p class="text-xs text-slate-400 mt-1">Terdaftar di sistem</p>
        </div>

        {{-- Quick Action --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-blue-100 mb-3">Aksi Cepat</p>
            <a href="{{ route('production.create') }}"
               class="flex items-center gap-2 w-full py-2.5 px-4 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Input Produksi
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('reports.daily') }}"
               class="flex items-center gap-2 w-full py-2.5 px-4 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors mt-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Laporan Harian
            </a>
            @endif
        </div>
    </div>

    {{-- ====== CHARTS ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Line Chart - 7 hari terakhir --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Trend Produksi</h3>
                    <p class="text-xs text-slate-500">7 hari terakhir</p>
                </div>
            </div>
            <canvas id="lineChart" height="100"></canvas>
        </div>

        {{-- Bar Chart - Top produk --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">Top 5 Produk</h3>
                <p class="text-xs text-slate-500">Bulan {{ now()->translatedFormat('F Y') }}</p>
            </div>
            <canvas id="barChart" height="180"></canvas>
        </div>
    </div>

    {{-- ====== KATEGORI WIDGET (admin only) ====== --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-violet-100 dark:bg-violet-900/40 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Kelola Kategori</h3>
                    <p class="text-xs text-slate-500">{{ $categories->count() }} kategori terdaftar</p>
                </div>
            </div>
            <a href="{{ route('categories.create') }}"
               class="flex items-center gap-1.5 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </a>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($categories as $category)
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
                {{-- Icon & Info --}}
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                            {{ $category->is_active ? 'bg-violet-100 dark:bg-violet-900/30' : 'bg-slate-100 dark:bg-slate-700' }}">
                    <span class="text-sm font-bold {{ $category->is_active ? 'text-violet-700 dark:text-violet-400' : 'text-slate-400' }}">
                        {{ strtoupper(substr($category->name, 0, 2)) }}
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm text-slate-800 dark:text-white truncate">{{ $category->name }}</span>
                        @if($category->code)
                        <span class="text-xs font-mono text-slate-400 bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded">
                            {{ $category->code }}
                        </span>
                        @endif
                        @if(!$category->is_active)
                        <span class="text-xs text-red-500 bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded">Nonaktif</span>
                        @endif
                    </div>
                    @if($category->description)
                    <p class="text-xs text-slate-400 truncate mt-0.5">{{ $category->description }}</p>
                    @endif
                </div>

                {{-- Produk count --}}
                <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap hidden sm:block">
                    {{ $category->products_count }} produk
                </span>

                {{-- Action buttons (tampil saat hover) --}}
                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    {{-- Tombol Edit Cepat (buka modal) --}}
                    <button type="button"
                            onclick="openEditModal({{ $category->id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->code ?? '') }}', '{{ addslashes($category->description ?? '') }}', {{ $category->is_active ? 'true' : 'false' }})"
                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                            title="Edit cepat">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    {{-- Tombol Edit Halaman Penuh --}}
                    <a href="{{ route('categories.edit', $category) }}"
                       class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                       title="Edit halaman penuh">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    {{-- Hapus --}}
                    <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                                data-confirm="Hapus kategori '{{ $category->name }}'? Pastikan tidak ada produk di kategori ini."
                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-slate-400">
                <p class="text-sm">Belum ada kategori.</p>
                <a href="{{ route('categories.create') }}" class="text-violet-600 text-sm hover:underline mt-1 inline-block">Tambah sekarang →</a>
            </div>
            @endforelse
        </div>

        @if($categories->count() > 0)
        <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
            <a href="{{ route('categories.index') }}"
               class="text-xs text-violet-600 hover:text-violet-700 dark:text-violet-400 font-medium">
                Kelola semua kategori →
            </a>
        </div>
        @endif
    </div>

    {{-- ====== MODAL EDIT CEPAT KATEGORI ====== --}}
    <div id="editCategoryModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header modal --}}
            <div class="flex items-center justify-between px-6 py-4 bg-linear-to-r from-amber-500 to-orange-500">
                <h3 class="text-base font-bold text-white">Edit Cepat Kategori</h3>
                <button onclick="closeEditModal()"
                        class="p-1 rounded-lg text-amber-100 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="editCategoryForm" method="POST" class="p-6 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="modal_name" name="name"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                  text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode</label>
                    <input type="text" id="modal_code" name="code"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                  text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-mono uppercase"
                           placeholder="Opsional">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Keterangan</label>
                    <textarea id="modal_description" name="description" rows="2"
                              class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                     text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm resize-none"
                              placeholder="Deskripsi kategori..."></textarea>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" id="modal_is_active" name="is_active" value="1"
                           class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <label for="modal_is_active" class="text-sm font-medium text-slate-700 dark:text-slate-300">Kategori Aktif</label>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeEditModal()"
                            class="flex-1 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                                   hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ====== DEPARTEMEN WIDGET (admin only) ====== --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-teal-100 dark:bg-teal-900/40 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Kelola Departemen</h3>
                    <p class="text-xs text-slate-500">{{ $departments->count() }} departemen terdaftar</p>
                </div>
            </div>
            <button type="button" onclick="openDeptModal()"
                    class="flex items-center gap-1.5 px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </button>
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($departments as $dept)
            <div class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors group">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                            {{ $dept->is_active ? 'bg-teal-100 dark:bg-teal-900/30' : 'bg-slate-100 dark:bg-slate-700' }}">
                    <span class="text-sm font-bold {{ $dept->is_active ? 'text-teal-700 dark:text-teal-400' : 'text-slate-400' }}">
                        {{ strtoupper(substr($dept->name, 0, 2)) }}
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-sm text-slate-800 dark:text-white truncate">{{ $dept->name }}</span>
                        @if(!$dept->is_active)
                        <span class="text-xs text-red-500 bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded">Nonaktif</span>
                        @endif
                    </div>
                </div>

                <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap hidden sm:block">
                    {{ $dept->operators_count }} operator
                </span>

                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    <a href="{{ route('departments.edit', $dept) }}"
                       class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                       title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    <form method="POST" action="{{ route('departments.destroy', $dept) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                                data-confirm="Hapus departemen '{{ $dept->name }}'?"
                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-slate-400">
                <p class="text-sm">Belum ada departemen.</p>
                <button type="button" onclick="openDeptModal()" class="text-teal-600 text-sm hover:underline mt-1 inline-block">Tambah sekarang →</button>
            </div>
            @endforelse
        </div>

        @if($departments->count() > 0)
        <div class="px-6 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
            <a href="{{ route('departments.index') }}"
               class="text-xs text-teal-600 hover:text-teal-700 dark:text-teal-400 font-medium">
                Kelola semua departemen →
            </a>
        </div>
        @endif
    </div>

    {{-- Modal Tambah Departemen Cepat --}}
    <div id="deptModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-sm bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 bg-linear-to-r from-teal-500 to-emerald-600">
                <h3 class="text-base font-bold text-white">Tambah Departemen</h3>
                <button onclick="closeDeptModal()"
                        class="p-1 rounded-lg text-teal-100 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('departments.quick-store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Departemen <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="dept_name_input" name="name"
                           placeholder="Contoh: QC Welding"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                  text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 text-sm"
                           required>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" onclick="closeDeptModal()"
                            class="flex-1 py-2.5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                                   hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-sm font-semibold transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- ====== RECENT LOGS ====== --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-semibold text-slate-800 dark:text-white">Input Produksi Terbaru</h3>
            <a href="{{ route('production.index') }}"
               class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                Lihat semua →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 1</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 2</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 3</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">
                                {{ $log->production_date->format('d/m/Y') }}
                            </span>
                            @if($log->production_date->isToday())
                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded-md">Hari ini</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 dark:text-white">{{ $log->product->name ?? '-' }}</div>
                            @if($log->product->series_with_kva)
                            <div class="text-xs text-slate-400">{{ $log->product->series_with_kva }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift1_qty) }}</td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift2_qty) }}</td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift3_qty) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ number_format($log->total_qty) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-sm">{{ $log->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                            </svg>
                            <p>Belum ada data produksi hari ini</p>
                            <a href="{{ route('production.create') }}" class="mt-2 inline-block text-blue-600 text-sm hover:underline">
                                Input sekarang →
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ====== Modal Tambah Departemen ======
const deptModal = document.getElementById('deptModal');
function openDeptModal() {
    if (!deptModal) return;
    deptModal.classList.remove('hidden');
    deptModal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('dept_name_input')?.focus(), 50);
}
function closeDeptModal() {
    if (!deptModal) return;
    deptModal.classList.add('hidden');
    deptModal.classList.remove('flex');
    document.body.style.overflow = '';
}
deptModal?.addEventListener('click', e => { if (e.target === deptModal) closeDeptModal(); });

// ====== Modal Edit Kategori ======
const modal    = document.getElementById('editCategoryModal');
const modalForm = document.getElementById('editCategoryForm');

function openEditModal(id, name, code, description, isActive) {
    // Set action URL ke route update kategori
    modalForm.action = '/categories/' + id;

    // Isi field form
    document.getElementById('modal_name').value        = name;
    document.getElementById('modal_code').value        = code;
    document.getElementById('modal_description').value = description;
    document.getElementById('modal_is_active').checked = isActive;

    // Tampilkan modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';

    // Fokus ke field nama
    setTimeout(() => document.getElementById('modal_name').focus(), 50);
}

function closeEditModal() {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

// Tutup modal saat klik backdrop
modal?.addEventListener('click', function (e) {
    if (e.target === modal) closeEditModal();
});

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (!modal?.classList.contains('hidden')) closeEditModal();
        if (!deptModal?.classList.contains('hidden')) closeDeptModal();
    }
});
</script>
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#94a3b8' : '#64748b';

// Line Chart - Trend Produksi
const lineCtx = document.getElementById('lineChart').getContext('2d');
const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->pluck('date')) !!},
        datasets: [{
            label: 'Total Produksi',
            data: {!! json_encode($chartData->pluck('total')) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} unit`
                }
            }
        },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => v.toLocaleString('id-ID') }, beginAtZero: true }
        }
    }
});

// Bar Chart - Top Produk
const barCtx = document.getElementById('barChart').getContext('2d');
const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($productChart->pluck('name')) !!},
        datasets: [{
            label: 'Total',
            data: {!! json_encode($productChart->pluck('total')) !!},
            backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b'],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 }, callback: v => v.toLocaleString('id-ID') }, beginAtZero: true },
            y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
        }
    }
});


</script>
@endpush
