@extends('layouts.app')

@section('title', 'Gambar Kerja')
@section('page-title', 'Gambar Kerja')
@section('page-subtitle', 'Pilih produk untuk melihat gambar kerjanya')

@section('content')
<div class="space-y-5">

    {{-- Filter & Action Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <form method="GET" action="{{ route('gambar-kerja.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-44">
                <label class="block text-xs font-medium text-slate-500 mb-1">Cari Produk</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Nama atau seri produk..."
                       class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-slate-500 mb-1">Kategori</label>
                <select name="category_id"
                        class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-36">
                <label class="block text-xs font-medium text-slate-500 mb-1">Tampilkan</label>
                <select name="has_gambar"
                        class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Produk</option>
                    <option value="1" {{ request('has_gambar') == '1' ? 'selected' : '' }}>Ada Gambar Kerja</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="{{ route('gambar-kerja.index') }}"
                   class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl">
                    Reset
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('gambar-kerja.create') }}"
                   class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Upload Gambar
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Grid Produk --}}
    @if($products->isEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-2xl py-16 text-center border border-slate-100 dark:border-slate-700">
        <svg class="w-14 h-14 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-slate-500 font-medium">Tidak ada produk ditemukan</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($products as $product)
        @php $firstGambar = $product->gambarKerja->first(); @endphp
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700
                    hover:shadow-md transition-shadow flex flex-col group">

            {{-- Thumbnail (klik ke halaman gambar) --}}
            <a href="{{ route('gambar-kerja.by-product', $product) }}" class="block">
                <div class="h-40 rounded-t-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 relative">
                    @if($firstGambar && $firstGambar->isImage())
                        <img src="{{ asset('storage/' . $firstGambar->file_path) }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @elseif($firstGambar && $firstGambar->isPdf())
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2">
                            <svg class="w-12 h-12 text-red-400" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 1.5L18.5 9H13V3.5zM6 20V4h5v7h7v9H6z"/>
                            </svg>
                            <span class="text-xs font-bold text-red-500 uppercase tracking-widest">PDF</span>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center gap-2 text-slate-300">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-xs font-medium">Belum ada gambar</span>
                        </div>
                    @endif

                    {{-- Badge jumlah gambar --}}
                    @if($product->gambar_kerja_count > 0)
                    <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-xs font-bold bg-black/60 text-white">
                        {{ $product->gambar_kerja_count }} gambar
                    </span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-4 pb-3">
                    <h3 class="font-semibold text-slate-800 dark:text-white text-sm leading-tight line-clamp-1
                                group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                        {{ $product->name }}
                    </h3>
                    @if($product->series_with_kva)
                    <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $product->series_with_kva }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-slate-400">{{ $product->category->name ?? '-' }}</span>
                        @if($product->gambar_kerja_count > 0)
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400 flex items-center gap-1">
                            Lihat gambar
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </span>
                        @else
                        <span class="text-xs text-slate-300 dark:text-slate-600 italic">Belum ada</span>
                        @endif
                    </div>
                </div>
            </a>

            {{-- Tombol Hapus (admin, hanya kalau ada gambar) --}}
            @if(auth()->user()->isAdmin() && $product->gambar_kerja_count > 0)
            <div class="px-4 pb-4 mt-auto">
                <form method="POST" action="{{ route('gambar-kerja.destroy-by-product', $product) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            data-confirm="Hapus semua {{ $product->gambar_kerja_count }} gambar kerja '{{ $product->name }}'? Tindakan ini tidak bisa dibatalkan."
                            class="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium
                                   text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400
                                   rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus Semua Gambar
                    </button>
                </form>
            </div>
            @endif

        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div>{{ $products->links() }}</div>
    @endif
    @endif

</div>
@endsection
