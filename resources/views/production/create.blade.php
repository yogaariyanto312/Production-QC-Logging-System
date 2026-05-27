@extends('layouts.app')

@section('title', 'Input Produksi')
@section('page-title', 'Input Produksi')
@section('page-subtitle', 'Tambah data produksi harian')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        <div class="bg-linear-to-r from-blue-600 to-blue-700 px-6 py-5">
            <h2 class="text-lg font-bold text-white">Form Input Produksi Harian</h2>
            <p class="text-blue-100 text-sm mt-1">Isi data produksi dengan lengkap</p>
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
                       class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                              border {{ $errors->has('production_date') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                        class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                               border {{ $errors->has('product_id') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                               focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products->groupBy('name') as $groupName => $groupItems)
                    <optgroup label="{{ $groupName }}">
                        @foreach($groupItems as $product)
                        <option value="{{ $product->id }}"
                                data-type="{{ $product->type }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ ($product->series ?: '—') . ($product->kva ? ' · ' . $product->kva . ' KVA' : '') }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('product_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Operator --}}
            <div>
                <label for="operator_name" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Nama Operator
                </label>
                <input type="text" id="operator_name" name="operator_name"
                       value="{{ old('operator_name') }}"
                       placeholder="Nama operator yang bertugas..."
                       class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                              border {{ $errors->has('operator_name') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('operator_name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Channel UP + BT (tampil hanya jika produk = channel) --}}
            <div id="section-channel" class="hidden space-y-4">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                    Jumlah Channel <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Channel UP</label>
                        <input type="number" id="channel_up" name="shift1_qty"
                               value="{{ old('shift1_qty', 0) }}"
                               min="0" max="9999"
                               class="w-full px-4 py-4 text-center text-xl font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Channel BT</label>
                        <input type="number" id="channel_bt" name="shift2_qty"
                               value="{{ old('shift2_qty', 0) }}"
                               min="0" max="9999"
                               class="w-full px-4 py-4 text-center text-xl font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <p class="text-xs text-blue-600 dark:text-blue-400 mb-2 font-medium">Total otomatis: (UP + BT) ÷ 2</p>
                    <p id="channel-total-display" class="text-3xl font-black text-blue-700 dark:text-blue-300 text-center">0</p>
                    <input type="hidden" id="total_qty_channel" name="total_qty" value="{{ old('total_qty', 0) }}">
                </div>
            </div>

            {{-- Total Manual (tampil hanya jika produk = regular) --}}
            <div id="section-regular">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <label for="total_qty_regular" class="block text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">
                        Total Unit <span class="text-red-500">*</span>
                        <span class="ml-1 text-xs font-normal text-blue-500">(isi manual)</span>
                    </label>
                    <input type="number" id="total_qty_regular" name="total_qty"
                           value="{{ old('total_qty', 0) }}"
                           min="0" max="99999" step="any"
                           class="w-full px-4 py-4 text-center text-3xl font-black rounded-xl
                                  bg-white dark:bg-slate-900 text-blue-700 dark:text-blue-300
                                  border-2 {{ $errors->has('total_qty') ? 'border-red-500' : 'border-blue-300 dark:border-blue-700' }}
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('total_qty')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Nomor Urut - Regular --}}
            <div id="section-notes-regular">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nomor Urut</label>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <label class="block text-xs text-slate-400 mb-1">Nomor Awal</label>
                        <input type="number" id="no_awal_regular" min="1" placeholder="Contoh: 98"
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="pt-4 text-slate-400 font-bold">→</div>
                    <div class="flex-1">
                        <label class="block text-xs text-slate-400 mb-1">Preview</label>
                        <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono text-sm text-blue-600 dark:text-blue-400 min-h-12">
                            <span id="preview-regular">—</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Nomor Urut - Channel --}}
            <div id="section-notes-channel" class="hidden">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nomor Urut</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Nomor Awal UP</label>
                        <input type="number" id="no_awal_up" min="1" placeholder="Contoh: 435"
                               class="w-full px-4 py-3 text-center text-lg font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p id="preview-up" class="mt-2 text-xs text-center font-mono text-blue-500 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg px-2 py-1.5 min-h-7">—</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 text-center">Nomor Awal BT</label>
                        <input type="number" id="no_awal_bt" min="1" placeholder="Contoh: 438"
                               class="w-full px-4 py-3 text-center text-lg font-bold rounded-xl
                                      bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p id="preview-bt" class="mt-2 text-xs text-center font-mono text-purple-500 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20 rounded-lg px-2 py-1.5 min-h-7">—</p>
                    </div>
                </div>
            </div>

            <input type="hidden" id="notes" name="notes" value="{{ old('notes') }}">

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('production.index') }}"
                   class="flex-1 py-3 px-4 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold
                               shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.01]">
                    Simpan Data Produksi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const productSelect       = document.getElementById('product_id');
    const sectionChannel      = document.getElementById('section-channel');
    const sectionRegular      = document.getElementById('section-regular');
    const sectionNotesChannel = document.getElementById('section-notes-channel');
    const sectionNotesRegular = document.getElementById('section-notes-regular');
    const channelUp           = document.getElementById('channel_up');
    const channelBt           = document.getElementById('channel_bt');
    const channelDisplay      = document.getElementById('channel-total-display');
    const totalHidden         = document.getElementById('total_qty_channel');
    const totalRegular        = document.getElementById('total_qty_regular');
    const noAwalRegular       = document.getElementById('no_awal_regular');
    const noAwalUp            = document.getElementById('no_awal_up');
    const noAwalBt            = document.getElementById('no_awal_bt');
    const previewRegular      = document.getElementById('preview-regular');
    const previewUp           = document.getElementById('preview-up');
    const previewBt           = document.getElementById('preview-bt');
    const notesHidden         = document.getElementById('notes');

    function pad(n) {
        const s = String(Math.round(n));
        return s.length < 3 ? s.padStart(3, '0') : s;
    }

    function generateRegular() {
        const start = parseInt(noAwalRegular.value);
        const qty   = Math.ceil(parseFloat(totalRegular.value) || 0);
        if (!start || qty <= 0) {
            previewRegular.textContent = '—';
            notesHidden.value = '';
            return;
        }
        const text = `NO.${pad(start)}-${pad(start + qty)}`;
        previewRegular.textContent = text;
        notesHidden.value = text;
    }

    function generateChannel() {
        const up    = parseInt(channelUp.value) || 0;
        const bt    = parseInt(channelBt.value) || 0;
        const total = up + bt;
        const startUp = parseInt(noAwalUp.value);
        const startBt = parseInt(noAwalBt.value);

        previewUp.textContent = (startUp && up > 0)
            ? `UP NO.${pad(startUp)}-${pad(startUp + up)}` : '—';
        previewBt.textContent = (startBt && bt > 0)
            ? `BT NO.${pad(startBt)}-${pad(startBt + bt)}` : '—';

        const lines = [previewUp.textContent, previewBt.textContent].filter(t => t !== '—');
        notesHidden.value = lines.join('\n');
    }

    function calcChannel() {
        const up    = parseInt(channelUp.value) || 0;
        const bt    = parseInt(channelBt.value) || 0;
        const total = (up + bt) / 2;
        channelDisplay.textContent = total % 1 === 0 ? total.toLocaleString('id-ID') : total.toFixed(1);
        totalHidden.value = total;
        generateChannel();
    }

    function switchMode(type) {
        const isChannel = type === 'channel';
        sectionChannel.classList.toggle('hidden', !isChannel);
        sectionRegular.classList.toggle('hidden', isChannel);
        sectionNotesChannel.classList.toggle('hidden', !isChannel);
        sectionNotesRegular.classList.toggle('hidden', isChannel);

        totalRegular.disabled = isChannel;
        totalHidden.disabled  = !isChannel;
        channelUp.disabled    = !isChannel;
        channelBt.disabled    = !isChannel;

        if (isChannel) calcChannel(); else generateRegular();
    }

    function getSelectedType() {
        const opt = productSelect.options[productSelect.selectedIndex];
        return opt ? (opt.dataset.type || 'regular') : 'regular';
    }

    productSelect.addEventListener('change', () => switchMode(getSelectedType()));
    channelUp.addEventListener('input', calcChannel);
    channelBt.addEventListener('input', calcChannel);
    totalRegular.addEventListener('input', generateRegular);
    noAwalRegular.addEventListener('input', generateRegular);
    noAwalUp.addEventListener('input', generateChannel);
    noAwalBt.addEventListener('input', generateChannel);

    switchMode(getSelectedType());
}());
</script>
@endsection
