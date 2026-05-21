@extends('layouts.app')

@section('title', 'Master Produk')
@section('page-title', 'Master Produk')
@section('page-subtitle', 'Kelola daftar produk dan seri')

@section('content')
<div class="space-y-5">

    {{-- Filter & Action Bar --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-slate-500 mb-1">Cari Produk</label>
                <input type="text" name="search" id="search-realtime"
                       value="{{ request('search') }}"
                       placeholder="Nama atau seri..."
                       class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                              bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="min-w-40">
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
            <div class="flex gap-2">
                <button type="submit"
                        class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="{{ route('products.index') }}"
                   class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl">
                    Reset
                </a>
                <a href="{{ route('products.create') }}"
                   class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Produk
                </a>
            </div>
        </form>
    </div>

    {{-- Product Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($products as $product)
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                             {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <h3 class="font-semibold text-slate-800 dark:text-white">{{ $product->name }}</h3>
            @if($product->series)
            <p class="text-xs text-slate-400 font-mono mt-1">{{ $product->series }}</p>
            @endif
            <div class="flex items-center gap-2 mt-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                             bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                    {{ $product->category->name ?? 'Tanpa Kategori' }}
                </span>
                <span class="text-xs text-slate-400">· {{ $product->unit }}</span>
            </div>
            @if($product->description)
            <p class="text-xs text-slate-400 mt-2 line-clamp-2">{{ $product->description }}</p>
            @endif

            <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('products.edit', $product) }}"
                   class="flex-1 py-2 text-center text-amber-700 bg-amber-50 hover:bg-amber-100 dark:bg-amber-900/20 dark:text-amber-400
                          rounded-lg text-sm font-medium transition-colors">
                    Edit
                </a>
                <form method="POST" action="{{ route('products.destroy', $product) }}" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            data-confirm="Hapus produk '{{ $product->name }}'?"
                            class="w-full py-2 text-center text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400
                                   rounded-lg text-sm font-medium transition-colors">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="md:col-span-2 lg:col-span-3 py-16 text-center bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
            <svg class="w-14 h-14 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <p class="text-slate-500 font-medium">Belum ada produk</p>
            <a href="{{ route('products.create') }}"
               class="mt-4 inline-block px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700">
                Tambah Produk Pertama
            </a>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div>{{ $products->links() }}</div>
    @endif
</div>
@endsection
