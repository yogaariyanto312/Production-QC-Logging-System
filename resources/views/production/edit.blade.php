@extends('layouts.app')

@section('title', 'Edit Data Produksi')
@section('page-title', 'Edit Data Produksi')
@section('page-subtitle', 'Ubah data produksi yang sudah diinput')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5">
            <h2 class="text-lg font-bold text-white">Edit Data Produksi</h2>
            <p class="text-amber-100 text-sm mt-1">{{ $productionLog->product->name ?? '' }} — {{ $productionLog->production_date->format('d/m/Y') }}</p>
        </div>

        <form method="POST" action="{{ route('production.update', $productionLog) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Tanggal Produksi <span class="text-red-500">*</span>
                </label>
                <input type="date" name="production_date"
                       value="{{ old('production_date', $productionLog->production_date->toDateString()) }}"
                       max="{{ today()->toDateString() }}"
                       class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                              text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select name="product_id"
                        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                               text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id', $productionLog->product_id) == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}{{ $product->series ? ' — ' . $product->series : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    Jumlah Per Shift <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-3 gap-4">
                    @foreach([1, 2, 3] as $shift)
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-2 text-center">Shift {{ $shift }}</label>
                        <input type="number" id="shift{{ $shift }}_qty" name="shift{{ $shift }}_qty"
                               value="{{ old('shift'.$shift.'_qty', $productionLog->{'shift'.$shift.'_qty'}) }}"
                               min="0" max="9999"
                               class="w-full px-4 py-4 text-center text-xl font-bold border border-slate-300 dark:border-slate-600
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white rounded-xl
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Total Produksi</p>
                    <p id="total_display" class="text-3xl font-bold text-amber-700 dark:text-amber-300">
                        {{ number_format($productionLog->total_qty) }} unit
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('notes', $productionLog->notes) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('production.index') }}"
                   class="flex-1 py-3 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-semibold transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
