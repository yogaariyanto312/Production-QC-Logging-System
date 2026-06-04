@extends('layouts.app')

@section('title', 'Input Produksi')
@section('page-title', 'Input Produksi')
@section('page-subtitle', 'Tambah data produksi harian')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5">
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
                        @php
                            $isPlaceholder = $product->category && $product->category->has_manual_serial && !$product->series;
                            if ($isPlaceholder) {
                                $nl = strtolower($product->name);
                                $typeTag = str_contains($nl, 'swasta') ? 'Swasta' : (str_contains($nl, 'type') ? 'TypeTest' : 'PLN');
                            }
                        @endphp
                        <option value="{{ $product->id }}"
                                data-type="{{ $product->type }}"
                                data-manual="{{ $isPlaceholder ? '1' : '0' }}"
                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $isPlaceholder ? 'Seri & KVA Manual → ' . $typeTag : (($product->series ?: '—') . ($product->kva ? ' · ' . $product->kva . ' KVA' : '')) }}
                        </option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('product_id')
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

                {{-- Hint: nomor urut terakhir --}}
                <div id="hint-regular" class="mb-3 items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-3 py-2.5" style="display:none">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0 text-xs">
                        <p class="text-slate-500 dark:text-slate-400">
                            Entri terakhir
                            <span id="hint-regular-date" class="font-semibold text-amber-600 dark:text-amber-400"></span>:
                        </p>
                        <p id="hint-regular-text"
                           class="mt-0.5 font-mono font-bold text-sm text-slate-800 dark:text-white truncate"></p>
                        <button type="button" id="hint-regular-btn"
                                class="hidden mt-1 text-blue-600 dark:text-blue-400 hover:underline font-medium">
                            ↳ Lanjutkan dari nomor ini
                        </button>
                    </div>
                </div>

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

                {{-- Hint: nomor urut terakhir (channel) --}}
                <div id="hint-channel" class="mb-3 items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-3 py-2.5" style="display:none">
                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1 min-w-0 text-xs">
                        <p class="text-slate-500 dark:text-slate-400">
                            Entri terakhir
                            <span id="hint-channel-date" class="font-semibold text-amber-600 dark:text-amber-400"></span>:
                        </p>
                        <p id="hint-channel-text"
                           class="mt-0.5 font-mono font-bold text-sm text-slate-800 dark:text-white"></p>
                        <div class="flex gap-3 mt-1">
                            <button type="button" id="hint-channel-btn-up"
                                    class="hidden text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                ↳ Lanjut UP
                            </button>
                            <button type="button" id="hint-channel-btn-bt"
                                    class="hidden text-purple-600 dark:text-purple-400 hover:underline font-medium">
                                ↳ Lanjut BT
                            </button>
                        </div>
                    </div>
                </div>

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

            {{-- Manual Seri & KVA (Swasta / Typetest) --}}
            <div id="section-manual-serial" class="hidden">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 space-y-3">
                    <p class="text-sm font-semibold text-amber-700 dark:text-amber-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Identitas Produk <span class="text-red-500 font-normal text-xs">(wajib diisi)</span>
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                Nomor Seri <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="manual_series" id="manual_series"
                                   value="{{ old('manual_series') }}" maxlength="100"
                                   placeholder="Contoh: A-1234 / 2026.001"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('manual_series') ? 'border-red-500' : 'border-amber-300 dark:border-amber-700' }}
                                          focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm">
                            @error('manual_series')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">
                                KVA <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="manual_kva" id="manual_kva"
                                   value="{{ old('manual_kva') }}" maxlength="50"
                                   placeholder="Contoh: 100 / 2x50 / 250"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('manual_kva') ? 'border-red-500' : 'border-amber-300 dark:border-amber-700' }}
                                          focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono text-sm">
                            @error('manual_kva')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reject / Defect --}}
            <div x-data="{ open: {{ old('reject_qty') > 0 ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-text="open ? 'Sembunyikan Reject' : 'Ada Reject / Defect?'"></span>
                    <svg class="w-4 h-4 ml-auto transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="open" x-transition class="bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 rounded-xl p-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Jumlah Reject</label>
                            <input type="number" name="reject_qty" min="0" max="9999"
                                   value="{{ old('reject_qty', 0) }}"
                                   class="w-full px-4 py-3 text-center text-xl font-bold rounded-xl
                                          bg-white dark:bg-slate-900 text-red-600 dark:text-red-400
                                          border border-red-300 dark:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Kategori Penyebab</label>
                            <select name="reject_category"
                                    class="w-full px-3 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                           border border-red-300 dark:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach(\App\Models\ProductionLog::$rejectCategories as $key => $label)
                                <option value="{{ $key }}" {{ old('reject_category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Keterangan Reject</label>
                        <input type="text" name="reject_notes" maxlength="300"
                               value="{{ old('reject_notes') }}"
                               placeholder="Deskripsi singkat penyebab reject..."
                               class="w-full px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-red-300 dark:border-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 text-sm">
                    </div>
                </div>
            </div>

            {{-- Keterangan --}}
            <div>
                <label for="keterangan" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Keterangan
                    <span class="ml-1 text-xs font-normal text-slate-400">(opsional)</span>
                </label>
                <textarea id="keterangan" name="keterangan" rows="2"
                          placeholder="Catatan tambahan, misal: Barang dari supplier, Lupa input, dll..."
                          class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                 border {{ $errors->has('keterangan') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('production.index') }}"
                   class="flex-1 py-3 px-4 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    <span class="md:hidden">Cancel</span>
                    <span class="hidden md:inline">Batal</span>
                </a>
                <button type="submit"
                        class="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold
                               shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.01]">
                    <span class="md:hidden">Save</span>
                    <span class="hidden md:inline">Simpan Data Produksi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const productSelect        = document.getElementById('product_id');
    const sectionChannel       = document.getElementById('section-channel');
    const sectionRegular       = document.getElementById('section-regular');
    const sectionNotesChannel  = document.getElementById('section-notes-channel');
    const sectionNotesRegular  = document.getElementById('section-notes-regular');
    const sectionManualSerial  = document.getElementById('section-manual-serial');
    const manualSeriesInput    = document.getElementById('manual_series');
    const manualKvaInput       = document.getElementById('manual_kva');
    const channelUp            = document.getElementById('channel_up');
    const channelBt            = document.getElementById('channel_bt');
    const channelDisplay       = document.getElementById('channel-total-display');
    const totalHidden          = document.getElementById('total_qty_channel');
    const totalRegular         = document.getElementById('total_qty_regular');
    const noAwalRegular        = document.getElementById('no_awal_regular');
    const noAwalUp             = document.getElementById('no_awal_up');
    const noAwalBt             = document.getElementById('no_awal_bt');
    const previewRegular       = document.getElementById('preview-regular');
    const previewUp            = document.getElementById('preview-up');
    const previewBt            = document.getElementById('preview-bt');
    const notesHidden          = document.getElementById('notes');

    // Hint elements
    const hintRegular        = document.getElementById('hint-regular');
    const hintRegularDate    = document.getElementById('hint-regular-date');
    const hintRegularText    = document.getElementById('hint-regular-text');
    const hintRegularBtn     = document.getElementById('hint-regular-btn');
    const hintChannel        = document.getElementById('hint-channel');
    const hintChannelDate    = document.getElementById('hint-channel-date');
    const hintChannelText    = document.getElementById('hint-channel-text');
    const hintChannelBtnUp   = document.getElementById('hint-channel-btn-up');
    const hintChannelBtnBt   = document.getElementById('hint-channel-btn-bt');

    function pad(n) {
        const s = String(Math.round(n));
        return s.length < 3 ? s.padStart(3, '0') : s;
    }

    function generateRegular() {
        const start = parseInt(noAwalRegular.value);
        const qty   = Math.ceil(parseFloat(totalRegular.value) || 0);
        if (!start || qty <= 0) {
            previewRegular.textContent = start ? 'Isi total unit →' : '—';
            notesHidden.value = '';
            return;
        }
        const text = `NO.${pad(start)}-${pad(start + qty - 1)}`;
        previewRegular.textContent = text;
        notesHidden.value = text;
    }

    function generateChannel() {
        const up    = parseInt(channelUp.value) || 0;
        const bt    = parseInt(channelBt.value) || 0;
        const startUp = parseInt(noAwalUp.value);
        const startBt = parseInt(noAwalBt.value);

        previewUp.textContent = (startUp && up > 0)
            ? `UP NO.${pad(startUp)}-${pad(startUp + up - 1)}` : '—';
        previewBt.textContent = (startBt && bt > 0)
            ? `BT NO.${pad(startBt)}-${pad(startBt + bt - 1)}` : '—';

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

    function getSelected() {
        const opt = productSelect.options[productSelect.selectedIndex];
        return {
            type:   opt ? (opt.dataset.type   || 'regular') : 'regular',
            manual: opt ? (opt.dataset.manual  === '1')     : false,
        };
    }

    function switchMode({ type, manual }) {
        const isChannel = type === 'channel';
        sectionChannel.classList.toggle('hidden', !isChannel);
        sectionRegular.classList.toggle('hidden', isChannel);
        sectionNotesChannel.classList.toggle('hidden', !isChannel);
        sectionNotesRegular.classList.toggle('hidden', isChannel);
        sectionManualSerial.classList.toggle('hidden', !manual);

        totalRegular.disabled = isChannel;
        totalHidden.disabled  = !isChannel;
        channelUp.disabled    = !isChannel;
        channelBt.disabled    = !isChannel;
        if (manualSeriesInput) manualSeriesInput.required = manual;
        if (manualKvaInput)    manualKvaInput.required    = manual;

        if (isChannel) calcChannel(); else generateRegular();
    }

    // ── Hint: nomor urut terakhir ─────────────────────────────────────────────

    function hideHints() {
        hintRegular.style.display = 'none';
        hintChannel.style.display = 'none';
    }

    /**
     * Parse nomor akhir dari string "NO.098-100" → 100
     * Atau "UP NO.435-437" → 437
     */
    function parseEndNum(str) {
        const m = str.match(/(\d+)\s*$/);
        return m ? parseInt(m[1]) : null;
    }

    function showHintRegular(notes, date) {
        hintRegularDate.textContent = date;
        hintRegularText.textContent = notes;

        const endNum = parseEndNum(notes);
        if (endNum !== null) {
            const nextNum = endNum + 1;
            hintRegularBtn.textContent = `↳ Lanjutkan dari ${nextNum}`;
            hintRegularBtn.classList.remove('hidden');
            hintRegularBtn.onclick = () => {
                noAwalRegular.value = nextNum;
                noAwalRegular.dispatchEvent(new Event('input'));
                noAwalRegular.focus();
            };
            // Auto-fill nomor awal jika field masih kosong
            if (!noAwalRegular.value) {
                noAwalRegular.value = nextNum;
                noAwalRegular.dispatchEvent(new Event('input'));
            }
        } else {
            hintRegularBtn.classList.add('hidden');
        }

        hintRegular.style.display = 'flex';
    }

    function showHintChannel(notes, date) {
        hintChannelDate.textContent = date;

        // Pisah baris UP dan BT
        const lines = notes.split('\n').map(l => l.trim()).filter(Boolean);
        hintChannelText.textContent = lines.join('  |  ');

        // Tombol UP
        const upLine = lines.find(l => /UP/i.test(l));
        const btLine = lines.find(l => /BT/i.test(l));

        if (upLine) {
            const endUp = parseEndNum(upLine);
            if (endUp !== null) {
                const nextUp = endUp + 1;
                hintChannelBtnUp.textContent = `↳ Lanjut UP dari ${nextUp}`;
                hintChannelBtnUp.classList.remove('hidden');
                hintChannelBtnUp.onclick = () => {
                    noAwalUp.value = nextUp;
                    noAwalUp.dispatchEvent(new Event('input'));
                    noAwalUp.focus();
                };
                if (!noAwalUp.value) {
                    noAwalUp.value = nextUp;
                    noAwalUp.dispatchEvent(new Event('input'));
                }
            }
        } else {
            hintChannelBtnUp.classList.add('hidden');
        }

        if (btLine) {
            const endBt = parseEndNum(btLine);
            if (endBt !== null) {
                const nextBt = endBt + 1;
                hintChannelBtnBt.textContent = `↳ Lanjut BT dari ${nextBt}`;
                hintChannelBtnBt.classList.remove('hidden');
                hintChannelBtnBt.onclick = () => {
                    noAwalBt.value = nextBt;
                    noAwalBt.dispatchEvent(new Event('input'));
                    noAwalBt.focus();
                };
                if (!noAwalBt.value) {
                    noAwalBt.value = nextBt;
                    noAwalBt.dispatchEvent(new Event('input'));
                }
            }
        } else {
            hintChannelBtnBt.classList.add('hidden');
        }

        hintChannel.style.display = 'flex';
    }

    const LAST_SERIAL_URL = '{{ route("api.production.last-serial") }}';

    async function fetchLastSerial(productId, type, isManual) {
        hideHints();
        if (!productId || isManual) return;

        try {
            const res  = await fetch(`${LAST_SERIAL_URL}?product_id=${productId}`);
            const data = await res.json();
            if (!data.notes) return;

            if (type === 'channel') {
                showHintChannel(data.notes, data.date);
            } else {
                showHintRegular(data.notes, data.date);
            }
        } catch (e) {
            // Gagal fetch — tidak tampilkan hint
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

    productSelect.addEventListener('change', () => {
        const sel = getSelected();
        switchMode(sel);
        fetchLastSerial(productSelect.value, sel.type, sel.manual);
    });

    channelUp.addEventListener('input', calcChannel);
    channelBt.addEventListener('input', calcChannel);
    totalRegular.addEventListener('input', generateRegular);
    noAwalRegular.addEventListener('input', generateRegular);
    noAwalUp.addEventListener('input', generateChannel);
    noAwalBt.addEventListener('input', generateChannel);

    // Init (termasuk fetch jika ada old value dari form validation error)
    const initSel = getSelected();
    switchMode(initSel);
    if (productSelect.value) {
        fetchLastSerial(productSelect.value, initSel.type, initSel.manual);
    }

    // ── Cegah double-submit (penyebab data/total ganda di riwayat) ───────────
    const form = productSelect.closest('form');
    if (form) {
        let submitted = false;
        form.addEventListener('submit', function (e) {
            if (submitted) { e.preventDefault(); return; }
            submitted = true;
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.classList.add('opacity-60', 'cursor-not-allowed'); }
        });
        // Pulihkan tombol bila halaman dikembalikan dari cache (tombol Back)
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            submitted = false;
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = false; btn.classList.remove('opacity-60', 'cursor-not-allowed'); }
        });
    }

}());
</script>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
<style>
.ts-wrapper.full.focus .ts-control,
.ts-wrapper.full .ts-control { box-shadow: none; }
.ts-control {
    background: var(--ts-bg, #1e293b) !important;
    border-color: var(--ts-border, #475569) !important;
    border-radius: 0.75rem !important;
    padding: 0.75rem 1rem !important;
    color: var(--ts-color, #f1f5f9) !important;
    font-size: 0.875rem !important;
    min-height: unset !important;
}
.ts-dropdown {
    background: #1e293b !important;
    border-color: #475569 !important;
    border-radius: 0.75rem !important;
    margin-top: 4px !important;
    color: #f1f5f9 !important;
    font-size: 0.875rem !important;
}
.ts-dropdown .option { padding: 8px 12px !important; }
.ts-dropdown .option:hover,
.ts-dropdown .option.active { background: #3b82f6 !important; color: #fff !important; }
.ts-dropdown .optgroup-header {
    font-weight: 700 !important;
    font-size: 0.7rem !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 8px 12px 4px !important;
    color: #94a3b8 !important;
    background: #0f172a !important;
}
.ts-dropdown-content { max-height: 300px !important; }
.ts-wrapper .ts-control input { color: #f1f5f9 !important; }
.ts-wrapper .ts-control input::placeholder { color: #64748b !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function () {
    const ts = new TomSelect('#product_id', {
        placeholder: '-- Pilih Produk --',
        searchField: ['text'],
        maxOptions: null,
        onInitialize() {
            // ensure initial mode applied after TomSelect wraps the element
        },
        onChange(val) {
            // trigger native change so existing JS picks it up
            document.getElementById('product_id').dispatchEvent(new Event('change'));
        }
    });
}());
</script>
@endpush
@endsection
