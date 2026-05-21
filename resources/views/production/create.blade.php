@extends('layouts.app')

@section('title', 'Input Produksi')
@section('page-title', 'Input Produksi')
@section('page-subtitle', 'Tambah data produksi harian')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        {{-- Header Card --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5">
            <h2 class="text-lg font-bold text-white">Form Input Produksi Harian</h2>
            <p class="text-blue-100 text-sm mt-1">Isi data produksi per shift dengan lengkap</p>
        </div>

        <form method="POST" action="{{ route('production.store') }}" class="p-6 space-y-6">
            @csrf

            {{-- Tanggal Produksi --}}
            <div>
                <label for="production_date" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Tanggal Produksi <span class="text-red-500">*</span>
                </label>
                <input type="date" id="production_date" name="production_date"
                       value="{{ old('production_date', today()->toDateString()) }}"
                       max="{{ today()->toDateString() }}"
                       class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                              text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500
                              @error('production_date') border-red-500 @enderror">
                @error('production_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilih Produk --}}
            <div>
                <label for="product_id" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id"
                        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                               text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500
                               @error('product_id') border-red-500 @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" data-unit="{{ $product->unit }}"
                            {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}{{ $product->series ? ' — ' . $product->series : '' }}
                        ({{ $product->category->name ?? 'Tanpa Kategori' }})
                    </option>
                    @endforeach
                </select>
                @error('product_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jumlah Per Shift --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    Jumlah Per Shift <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-4">
                    @foreach([1, 2, 3] as $shift)
                    <div>
                        <label for="shift{{ $shift }}_qty"
                               class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">
                            Shift {{ $shift }}
                        </label>
                        <div class="relative">
                            <input type="number" id="shift{{ $shift }}_qty" name="shift{{ $shift }}_qty"
                                   value="{{ old('shift'.$shift.'_qty', 0) }}"
                                   min="0" max="9999"
                                   class="w-full px-4 py-4 text-center text-xl font-bold border border-slate-300 dark:border-slate-600
                                          bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-blue-500
                                          @error('shift'.$shift.'_qty') border-red-500 @enderror">
                        </div>
                        @error('shift'.$shift.'_qty')
                        <p class="mt-1 text-xs text-red-500 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Auto Total Display --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20
                        border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total Produksi</p>
                        <p class="text-xs text-blue-500 dark:text-blue-400">Dihitung otomatis</p>
                    </div>
                    <div class="text-right">
                        <p id="total_display" class="text-3xl font-bold text-blue-700 dark:text-blue-300 text-gray-400">
                            0 unit
                        </p>
                    </div>
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label for="notes" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                </label>
                <textarea id="notes" name="notes" rows="3"
                          placeholder="Tambahkan catatan khusus jika ada..."
                          class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500
                                 resize-none @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('production.index') }}"
                   class="flex-1 py-3 px-4 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold
                               shadow-lg shadow-blue-900/20 transition-all transform hover:scale-[1.01]">
                    Simpan Data Produksi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
