@extends('layouts.app')

@section('title', 'Aksesoris Keluar')
@section('page-title', 'Aksesoris Keluar')
@section('page-subtitle', 'Catatan aksesoris yang keluar dari produksi')

@section('content')
@php
    $role = auth()->user()->role;
    $canCreate = \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.create');
    $canEdit   = \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.edit');
    $canDelete = \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.delete');
    $canExport = \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.export');
    $canInput  = $canCreate || $canEdit || $canDelete;
    $colCount  = $canInput ? 7 : 6;
@endphp
<div class="space-y-5">

    {{-- Filter + tombol tambah --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="flex flex-col lg:flex-row lg:items-end gap-4">
            <form method="GET" action="{{ route('accessories.index') }}" class="grid grid-cols-2 lg:grid-cols-4 gap-3 flex-1">
                <div class="col-span-2 lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nama aksesoris, seri, penerima, tujuan..."
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Bulan</label>
                    <select name="month"
                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Tahun</label>
                    <select name="year"
                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $yr)
                        <option value="{{ $yr }}" {{ (string) request('year') === (string) $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                @if($departments->isNotEmpty())
                <div class="col-span-2 lg:col-span-4">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Departemen</label>
                    <select name="department" onchange="this.form.submit()"
                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Departemen</option>
                        @foreach($departments as $d)
                        <option value="{{ $d }}" {{ $deptFilter === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-span-2 lg:col-span-4 flex gap-2">
                    <button type="submit"
                            class="px-4 py-2.5 text-sm font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200
                                   rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition">Filter</button>
                    @if(request()->hasAny(['search', 'month', 'year', 'department']))
                    <a href="{{ route('accessories.index') }}"
                       class="px-4 py-2.5 text-sm font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition">Reset</a>
                    @endif
                </div>
            </form>

            <div class="shrink-0 flex gap-2">
                @if($canExport)
                <a href="{{ route('accessories.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200
                          bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export
                </a>
                @endif
                @if($canCreate)
                <button type="button" onclick="document.getElementById('modal-accessory').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white
                               bg-blue-600 hover:bg-blue-700 rounded-xl shadow-lg shadow-blue-900/20 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Aksesoris
                </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Info pagination — mengikuti gaya Riwayat Produksi: keterangan halaman selalu
         tampil, tombol navigasi menyusul begitu datanya lebih dari satu halaman. --}}
    @if($accessories->total() > 0)
    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Hal. <strong class="text-slate-700 dark:text-slate-300">{{ $accessories->currentPage() }}</strong>
            · <strong class="text-slate-700 dark:text-slate-300">{{ $accessories->count() }}</strong> entri
            dari <strong class="text-slate-700 dark:text-slate-300">{{ $accessories->total() }}</strong> entri total
        </p>
        @if($accessories->hasPages())
        <div class="sm:ml-auto">{{ $accessories->onEachSide(0)->links() }}</div>
        @endif
    </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-400 dark:text-slate-500">
                    <tr class="text-xs uppercase tracking-wide">
                        {{-- w-px + whitespace-nowrap = kolom menyusut selebar isinya; kolom
                             Keterangan yang w-full menyerap semua sisa lebar tabel. --}}
                        <th class="px-4 py-3 text-left font-semibold w-px whitespace-nowrap">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold w-px whitespace-nowrap">Aksesoris</th>
                        <th class="px-4 py-3 text-left font-semibold w-px whitespace-nowrap">Produk / No. Urut</th>
                        <th class="px-4 py-3 text-center font-semibold w-px whitespace-nowrap">Jumlah</th>
                        <th class="px-4 py-3 text-left font-semibold w-px whitespace-nowrap">Diinput</th>
                        <th class="px-4 py-3 text-left font-semibold w-full">Keterangan</th>
                        @if($canInput)<th class="px-4 py-3 text-center font-semibold w-px whitespace-nowrap">Aksi</th>@endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($accessories as $a)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-900/40 transition">
                        <td class="px-4 py-3 whitespace-nowrap text-slate-600 dark:text-slate-300">
                            {{ $a->accessory_date->translatedFormat('d M Y') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="font-semibold text-slate-800 dark:text-white">{{ $a->name }}</div>
                        </td>
                        <td class="px-4 py-3 leading-tight whitespace-nowrap">
                            @if($a->product)
                            <div class="text-slate-600 dark:text-slate-300">{{ $a->product->series ?: $a->product->name }}</div>
                            <div class="text-xs text-slate-400">
                                {{ $a->product->kva ? $a->product->kva . ' KVA' : '' }}{{ $a->serial_number ? ' · ' . $a->serial_number : '' }}
                            </div>
                            @elseif($a->serial_number)
                            <div class="text-slate-600 dark:text-slate-300 font-mono text-xs">{{ $a->serial_number }}</div>
                            @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center min-w-8 px-2 py-1 rounded-lg text-sm
                                         bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold">
                                {{ $a->qty }}
                            </span>
                            <span class="text-xs text-slate-400 ml-1.5">{{ $a->unit }}</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-400">{{ $a->operator_name ?? optional($a->user)->name }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400 leading-snug">
                            {{ $a->keterangan ?: '' }}
                        </td>
                        @if($canInput)
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center justify-center gap-2">
                                @if($canEdit)
                                <button type="button" onclick="openEditAccessory(this)"
                                        data-accessory="{{ json_encode([
                                            'id'             => $a->id,
                                            'name'           => $a->name,
                                            'product_id'     => $a->product_id,
                                            'serial_number'  => $a->serial_number,
                                            'accessory_date' => $a->accessory_date->toDateString(),
                                            'qty'            => $a->qty,
                                            'unit'           => $a->unit,
                                            'keterangan'     => $a->keterangan,
                                        ]) }}"
                                        class="text-slate-400 hover:text-blue-500 transition" title="Edit">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                @endif
                                @if($canDelete)
                                <form method="POST" action="{{ route('accessories.destroy', $a) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition" title="Hapus"
                                            data-confirm="Hapus data aksesoris '{{ $a->name }}' ({{ $a->qty }} {{ $a->unit }})?"
                                            data-confirm-title="Hapus Aksesoris">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $colCount }}" class="px-3 py-10 text-center text-slate-400">
                            Belum ada data aksesoris keluar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if($canCreate)
{{-- Modal Tambah --}}
<div id="modal-accessory" class="hidden fixed inset-0 z-50 p-4">
    <div class="absolute inset-0 bg-black/60"
         onclick="document.getElementById('modal-accessory').classList.add('hidden')"></div>

    <div class="relative flex min-h-full items-center justify-center">
    <div class="relative w-full max-w-3xl max-h-[92vh] flex flex-col bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        {{-- Header gradient --}}
        <div class="flex items-center justify-between px-6 py-4 bg-linear-to-r from-blue-600 to-blue-700 shrink-0">
            <div>
                <h2 class="text-base font-bold text-white leading-tight">Tambah Aksesoris Keluar</h2>
                <p class="text-blue-100 text-xs mt-0.5">Catat aksesoris yang keluar dari produksi</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-accessory').classList.add('hidden')"
                    class="p-1.5 text-white/80 hover:text-white hover:bg-white/15 rounded-lg transition-colors" title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('accessories.store') }}" class="p-6 overflow-y-auto">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                {{-- Nama aksesoris — full width (field utama) --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Nama / Jenis Aksesoris <span class="text-red-500">*</span>
                    </label>
                    <select id="accessory-name" name="name" required
                           class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                  border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value=""></option>
                        @php $oldName = old('name'); @endphp
                        @if($oldName && !$accessoryNames->contains($oldName))
                        <option value="{{ $oldName }}" selected>{{ $oldName }}</option>
                        @endif
                        @foreach($accessoryNames as $n)
                        <option value="{{ $n }}" {{ $oldName === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                {{-- Tanggal --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="accessory_date" value="{{ old('accessory_date', now()->toDateString()) }}" required
                           class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                  border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Jumlah + Satuan --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="accessory-qty" name="qty" value="{{ old('qty', 1) }}" min="1" max="99999" required
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Satuan</label>
                        <select name="unit"
                                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                       border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(\App\Models\Accessory::UNITS as $u)
                            <option value="{{ $u }}" {{ strtolower(old('unit', 'pcs')) === $u ? 'selected' : '' }}>{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Seri, nomor urut & keterangan (semua opsional, tampil langsung) --}}
                <div class="sm:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                        {{-- Seri produk terkait --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Seri Produk Terkait <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <select id="accessory-product" name="product_id"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                           border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Tanpa seri (aksesoris lepas) --</option>
                                @if($manualSeries->isNotEmpty())
                                <optgroup label="Nomor Seri">
                                    @foreach($manualSeries as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->series }}{{ $product->kva ? ' · ' . $product->kva . ' KVA' : '' }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @foreach($regularProducts->groupBy('name') as $groupName => $groupItems)
                                <optgroup label="{{ $groupName }}">
                                    @foreach($groupItems as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ ($product->series ?: '—') . ($product->kva ? ' · ' . $product->kva . ' KVA' : '') }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Nomor urut aksesoris --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nomor Urut Aksesoris <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <div id="hint-accessory-serial" class="mb-3 items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl px-3 py-2.5" style="display:none">
                                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div class="flex-1 min-w-0 text-xs">
                                    <p class="text-slate-500 dark:text-slate-400">
                                        Entri terakhir <span id="hint-accessory-serial-scope" class="font-semibold text-slate-600 dark:text-slate-300"></span>
                                        <span id="hint-accessory-serial-date" class="font-semibold text-amber-600 dark:text-amber-400"></span>:
                                    </p>
                                    <p id="hint-accessory-serial-text" class="mt-0.5 font-mono font-bold text-sm text-slate-800 dark:text-white truncate"></p>
                                    <button type="button" id="hint-accessory-serial-btn"
                                            class="hidden mt-1 text-blue-600 dark:text-blue-400 hover:underline font-medium"></button>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1">
                                    <label class="block text-xs text-slate-400 mb-1">Nomor Awal</label>
                                    <input type="number" id="accessory-no-awal" min="1" placeholder="Contoh: 15"
                                           class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                                  border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="pt-4 text-slate-400 font-bold">→</div>
                                <div class="flex-1">
                                    <label class="block text-xs text-slate-400 mb-1">Preview</label>
                                    <div class="px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono text-sm text-blue-600 dark:text-blue-400 min-h-12">
                                        <span id="accessory-serial-preview">—</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="serial_number" id="accessory-serial-number" value="{{ old('serial_number') }}">
                        </div>

                        {{-- Keterangan --}}
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <textarea name="keterangan" rows="2" maxlength="1000"
                                      class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                             border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="button" onclick="document.getElementById('modal-accessory').classList.add('hidden')"
                        class="flex-1 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-200
                               bg-slate-100 dark:bg-slate-700 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700
                               rounded-xl shadow-lg shadow-blue-900/20 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

@if($errors->any() || old('name') || old('product_id'))
<script>
    // Buka kembali modal bila ada error validasi
    document.getElementById('modal-accessory')?.classList.remove('hidden');
</script>
@endif
@endif

@if($canEdit)
{{-- Modal Edit --}}
<div id="modal-accessory-edit" class="hidden fixed inset-0 z-50 p-4">
    <div class="absolute inset-0 bg-black/60" onclick="closeEditAccessory()"></div>

    <div class="relative flex min-h-full items-center justify-center">
    <div class="relative w-full max-w-3xl max-h-[92vh] flex flex-col bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 bg-linear-to-r from-amber-500 to-orange-500 shrink-0">
            <div>
                <h2 class="text-base font-bold text-white leading-tight">Edit Aksesoris Keluar</h2>
                <p class="text-amber-50 text-xs mt-0.5">Perbarui catatan aksesoris yang keluar dari produksi</p>
            </div>
            <button type="button" onclick="closeEditAccessory()"
                    class="p-1.5 text-white/80 hover:text-white hover:bg-white/15 rounded-lg transition-colors" title="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="form-accessory-edit" method="POST" action="" class="p-6 overflow-y-auto">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Nama / Jenis Aksesoris <span class="text-red-500">*</span>
                    </label>
                    <select id="accessory-name-edit" name="name" required
                           class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                  border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value=""></option>
                        @foreach($accessoryNames as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="edit_accessory_date" name="accessory_date" required
                           class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                  border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Jumlah <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="edit_qty" name="qty" min="1" max="99999" required
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Satuan</label>
                        <select id="edit_unit" name="unit"
                                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                       border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            @foreach(\App\Models\Accessory::UNITS as $u)
                            <option value="{{ $u }}">{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Seri Produk Terkait <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <select id="accessory-product-edit" name="product_id"
                                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                           border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <option value="">-- Tanpa seri (aksesoris lepas) --</option>
                                @if($manualSeries->isNotEmpty())
                                <optgroup label="Nomor Seri">
                                    @foreach($manualSeries as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->series }}{{ $product->kva ? ' · ' . $product->kva . ' KVA' : '' }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @foreach($regularProducts->groupBy('name') as $groupName => $groupItems)
                                <optgroup label="{{ $groupName }}">
                                    @foreach($groupItems as $product)
                                    <option value="{{ $product->id }}">
                                        {{ ($product->series ?: '—') . ($product->kva ? ' · ' . $product->kva . ' KVA' : '') }}
                                    </option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nomor Urut Aksesoris <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input type="text" id="edit_serial_number" name="serial_number" maxlength="150"
                                   placeholder="mis. NO.512 (kosongkan jika lepas)"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Keterangan <span class="text-slate-400 font-normal">(opsional)</span></label>
                            <textarea id="edit_keterangan" name="keterangan" rows="2" maxlength="1000"
                                      class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                             border border-slate-300 dark:border-slate-600 focus:outline-none focus:ring-2 focus:ring-amber-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-6">
                <button type="button" onclick="closeEditAccessory()"
                        class="flex-1 px-4 py-3 text-sm font-medium text-slate-700 dark:text-slate-200
                               bg-slate-100 dark:bg-slate-700 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600
                               rounded-xl shadow-lg shadow-amber-900/20 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    </div>
</div>
@endif

@if($canCreate || $canEdit)
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
    display: block !important;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-6px) scale(0.98);
    transform-origin: top center;
    pointer-events: none;
    transition: opacity 0.16s ease, transform 0.16s ease, visibility 0.16s ease;
}
.ts-wrapper.dropdown-active .ts-dropdown {
    opacity: 1;
    visibility: visible;
    transform: none;
    pointer-events: auto;
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
    @if($canCreate)
    window.accessoryNameTS = new TomSelect('#accessory-name', {
        create: true,
        persist: false,
        createOnBlur: true,
        placeholder: 'Pilih atau ketik nama aksesoris baru...',
        maxOptions: null,
        onChange: updateAccessorySerialHint,
    });
    window.accessoryProductTS = new TomSelect('#accessory-product', {
        placeholder: '-- Tanpa seri (aksesoris lepas) --',
        searchField: ['text'],
        maxOptions: null,
        allowEmptyOption: true,
        onChange: updateAccessorySerialHint,
    });
    document.getElementById('accessory-qty')?.addEventListener('input', updateAccessorySerialHint);
    document.getElementById('accessory-no-awal')?.addEventListener('input', generateAccessorySerial);
    @endif

    @if($canEdit)
    window.accessoryNameEditTS = new TomSelect('#accessory-name-edit', {
        create: true,
        persist: false,
        createOnBlur: true,
        placeholder: 'Pilih atau ketik nama aksesoris baru...',
        maxOptions: null,
    });
    window.accessoryProductEditTS = new TomSelect('#accessory-product-edit', {
        placeholder: '-- Tanpa seri (aksesoris lepas) --',
        searchField: ['text'],
        maxOptions: null,
        allowEmptyOption: true,
    });
    @endif
}());

@if($canCreate)
const lastSerials  = @json($lastSerials);
const productSeries = @json($productSeries);

// Saran nomor urut berikutnya = nomor urut terakhir untuk jenis aksesoris DAN seri
// produk yang sama + jumlah yang sedang diinput (mis. BOX seri 2051 terakhir NO.15,
// tambah 3 → No. 018). Tiap seri punya rentetan sendiri, jadi pindah ke seri 2052
// tidak melanjutkan nomor milik seri 2051.
// Format nomor mengikuti Input Produksi: minimal 3 digit (15 → 015).
function padAccessoryNo(n) {
    const s = String(Math.round(n));
    return s.length < 3 ? s.padStart(3, '0') : s;
}

// Rangkai "Nomor Awal" + jumlah menjadi nomor urut final, sama seperti preview di
// Input Produksi: 1 pcs → NO.015, 3 pcs mulai 15 → NO.015-017. Hasilnya masuk ke
// hidden input `serial_number` yang benar-benar dikirim ke server.
function generateAccessorySerial() {
    const startInput = document.getElementById('accessory-no-awal');
    const preview    = document.getElementById('accessory-serial-preview');
    const hidden     = document.getElementById('accessory-serial-number');
    if (!startInput || !preview || !hidden) return;

    const start = parseInt(startInput.value, 10);
    const qty   = parseInt(document.getElementById('accessory-qty')?.value, 10) || 0;

    if (!start || qty <= 0) {
        preview.textContent = start ? 'Isi jumlah →' : '—';
        hidden.value = '';
        return;
    }

    const text = qty === 1
        ? `NO.${padAccessoryNo(start)}`
        : `NO.${padAccessoryNo(start)}-${padAccessoryNo(start + qty - 1)}`;

    preview.textContent = text;
    hidden.value = text;
}

function updateAccessorySerialHint() {
    const hintBox = document.getElementById('hint-accessory-serial');
    if (!hintBox) return;

    const name      = window.accessoryNameTS ? window.accessoryNameTS.getValue() : '';
    const productId = window.accessoryProductTS ? window.accessoryProductTS.getValue() : '';
    const series    = productId ? (productSeries[productId] || '') : '';
    const info      = lastSerials[name + '|' + series];

    generateAccessorySerial();

    if (!info) {
        hintBox.style.display = 'none';
        return;
    }

    document.getElementById('hint-accessory-serial-scope').textContent = info.series ? 'seri ' + info.series : '(tanpa seri)';
    document.getElementById('hint-accessory-serial-date').textContent = info.date;
    document.getElementById('hint-accessory-serial-text').textContent = info.serial_number;

    // Lanjut dari nomor SETELAH yang terakhir (terakhir NO.15 → mulai 16), bukan
    // last + qty seperti sebelumnya yang melompati nomor.
    const nextNumber = info.last_number + 1;
    const startInput = document.getElementById('accessory-no-awal');
    const btn        = document.getElementById('hint-accessory-serial-btn');

    btn.textContent = `↳ Lanjutkan dari ${nextNumber}`;
    btn.classList.remove('hidden');
    btn.onclick = () => {
        startInput.value = nextNumber;
        generateAccessorySerial();
        startInput.focus();
    };

    // Auto-isi bila nomor awal masih kosong, seperti di Input Produksi.
    if (!startInput.value) {
        startInput.value = nextNumber;
        generateAccessorySerial();
    }

    hintBox.style.display = 'flex';
}

// Pulihkan "Nomor Awal" dari nilai lama bila form terbuka kembali setelah validasi
// gagal — tanpa ini generateAccessorySerial() akan mengosongkan hidden input.
(function () {
    const hidden     = document.getElementById('accessory-serial-number');
    const startInput = document.getElementById('accessory-no-awal');
    if (!hidden || !startInput || !hidden.value || startInput.value) return;

    const m = hidden.value.match(/(\d+)/);
    if (m) startInput.value = parseInt(m[1], 10);
}());

// Tampilkan hint sejak awal bila form terbuka kembali dengan isian lama (mis.
// setelah validasi gagal). Dipanggil di sini, bukan di IIFE di atas, karena
// lastSerials/productSeries baru terdefinisi setelah baris ini.
updateAccessorySerialHint();
@endif

function openEditAccessory(btn) {
    const a = JSON.parse(btn.dataset.accessory);
    const form = document.getElementById('form-accessory-edit');
    form.action = '{{ url('/accessories') }}/' + a.id;

    if (window.accessoryNameEditTS) window.accessoryNameEditTS.setValue(a.name || '', true);
    if (window.accessoryProductEditTS) window.accessoryProductEditTS.setValue(a.product_id ? String(a.product_id) : '', true);
    document.getElementById('edit_accessory_date').value = a.accessory_date || '';
    document.getElementById('edit_qty').value = a.qty || 1;
    // Data lama sempat tersimpan "Unit" (huruf besar); samakan ke opsi dropdown
    // agar tidak jatuh ke pilihan kosong saat modal edit dibuka.
    document.getElementById('edit_unit').value = (a.unit || 'pcs').toLowerCase();
    document.getElementById('edit_serial_number').value = a.serial_number || '';
    document.getElementById('edit_keterangan').value = a.keterangan || '';

    document.getElementById('modal-accessory-edit').classList.remove('hidden');
}
function closeEditAccessory() {
    document.getElementById('modal-accessory-edit').classList.add('hidden');
}
</script>
@endpush
@endif
@endsection
