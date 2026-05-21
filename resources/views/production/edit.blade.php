@extends('layouts.app')

@section('title', 'Edit Data Produksi')
@section('page-title', 'Edit Data Produksi')
@section('page-subtitle', 'Ubah data produksi yang sudah diinput')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        <div class="bg-linear-to-r from-amber-500 to-orange-500 px-6 py-5">
            <h2 class="text-lg font-bold text-white">Edit Data Produksi</h2>
            <p class="text-amber-100 text-sm mt-1">
                {{ $productionLog->product->name ?? '' }} — {{ $productionLog->production_date->format('d/m/Y') }}
            </p>
        </div>

        <form method="POST" action="{{ route('production.update', $productionLog) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

            {{-- Tanggal --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Tanggal Produksi <span class="text-red-500">*</span>
                </label>
                <input type="date" name="production_date"
                       value="{{ old('production_date', $productionLog->production_date->toDateString()) }}"
                       max="{{ today()->toDateString() }}"
                       class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                              border {{ $errors->has('production_date') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('production_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Produk --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select id="product_id" name="product_id"
                        class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                               border {{ $errors->has('product_id') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($products as $product)
                    <option value="{{ $product->id }}"
                            data-type="{{ $product->type }}"
                            {{ old('product_id', $productionLog->product_id) == $product->id ? 'selected' : '' }}>
                        {{ $product->name }}{{ $product->series_with_kva ? ' — ' . $product->series_with_kva : '' }}
                    </option>
                    @endforeach
                </select>
                @error('product_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Operator --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Operator</label>
                <input type="text" name="operator_name"
                       value="{{ old('operator_name', $productionLog->operator_name) }}"
                       placeholder="Nama operator yang bertugas..."
                       class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                              border {{ $errors->has('operator_name') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Channel UP + BT --}}
            <div id="section-channel" class="hidden space-y-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Jumlah Channel <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Channel UP</label>
                        <input type="number" id="channel_up" name="shift1_qty"
                               value="{{ old('shift1_qty', $productionLog->shift1_qty) }}"
                               min="0" max="9999"
                               class="w-full px-4 py-4 text-center text-xl font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600
                                      focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Channel BT</label>
                        <input type="number" id="channel_bt" name="shift2_qty"
                               value="{{ old('shift2_qty', $productionLog->shift2_qty) }}"
                               min="0" max="9999"
                               class="w-full px-4 py-4 text-center text-xl font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600
                                      focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                    <p class="text-xs text-amber-600 dark:text-amber-400 mb-2 font-medium">Total otomatis: (UP + BT) ÷ 2</p>
                    <p id="channel-total-display" class="text-3xl font-black text-amber-700 dark:text-amber-300 text-center">0</p>
                    <input type="hidden" id="total_qty_channel" name="total_qty" value="{{ old('total_qty', $productionLog->total_qty) }}">
                </div>
            </div>

            {{-- Total Manual --}}
            <div id="section-regular">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
                    <label for="total_qty_regular" class="block text-sm font-semibold text-amber-700 dark:text-amber-300 mb-2">
                        Total Unit <span class="text-red-500">*</span>
                        <span class="ml-1 text-xs font-normal text-amber-500">(isi manual)</span>
                    </label>
                    <input type="number" id="total_qty_regular" name="total_qty"
                           value="{{ old('total_qty', $productionLog->total_qty) }}"
                           min="0" max="99999" step="any"
                           class="w-full px-4 py-4 text-center text-3xl font-black rounded-xl
                                  bg-white dark:bg-slate-900 text-amber-700 dark:text-amber-300
                                  border-2 {{ $errors->has('total_qty') ? 'border-red-500' : 'border-amber-300 dark:border-amber-700' }}
                                  focus:outline-none focus:ring-2 focus:ring-amber-500">
                    @error('total_qty')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Catatan --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Catatan</label>
                <textarea name="notes" rows="3"
                          class="w-full px-4 py-3 rounded-xl resize-none bg-white dark:bg-slate-900
                                 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500
                                 border {{ $errors->has('notes') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}">{{ old('notes', $productionLog->notes) }}</textarea>
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

<script>
(function () {
    const productSelect   = document.getElementById('product_id');
    const sectionChannel  = document.getElementById('section-channel');
    const sectionRegular  = document.getElementById('section-regular');
    const channelUp       = document.getElementById('channel_up');
    const channelBt       = document.getElementById('channel_bt');
    const channelDisplay  = document.getElementById('channel-total-display');
    const totalHidden     = document.getElementById('total_qty_channel');
    const totalRegular    = document.getElementById('total_qty_regular');

    function calcChannel() {
        const up    = parseInt(channelUp.value) || 0;
        const bt    = parseInt(channelBt.value) || 0;
        const total = (up + bt) / 2;
        channelDisplay.textContent = total % 1 === 0 ? total.toLocaleString('id-ID') : total.toFixed(1);
        totalHidden.value          = total;
    }

    function switchMode(type) {
        const isChannel = type === 'channel';
        sectionChannel.classList.toggle('hidden', !isChannel);
        sectionRegular.classList.toggle('hidden', isChannel);
        totalRegular.disabled = isChannel;
        totalHidden.disabled  = !isChannel;
        channelUp.disabled    = !isChannel;
        channelBt.disabled    = !isChannel;
        if (isChannel) calcChannel();
    }

    function getSelectedType() {
        const opt = productSelect.options[productSelect.selectedIndex];
        return opt ? (opt.dataset.type || 'regular') : 'regular';
    }

    productSelect.addEventListener('change', () => switchMode(getSelectedType()));
    channelUp.addEventListener('input', calcChannel);
    channelBt.addEventListener('input', calcChannel);

    switchMode(getSelectedType());
}());
</script>
@endsection
