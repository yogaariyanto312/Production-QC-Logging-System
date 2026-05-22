@extends('layouts.app')

@section('title', 'Upload Gambar Kerja')
@section('page-title', 'Upload Gambar Kerja')
@section('page-subtitle', $selectedProduct ? $selectedProduct->name : 'Tambah gambar teknik produk')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
            <h2 class="text-lg font-bold text-white">Upload Gambar Kerja</h2>
            <p class="text-blue-100 text-sm mt-1">
                Bisa pilih banyak file sekaligus — nomor urut otomatis
                @if($selectedProduct)
                    · <span class="font-semibold">{{ $selectedProduct->name }}</span>
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('gambar-kerja.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            {{-- Produk --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Produk <span class="text-red-500">*</span>
                </label>
                <select name="product_id"
                        class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                               text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500
                               @error('product_id') border-red-500 @enderror">
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}"
                        {{ old('product_id', $selectedProduct?->id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}{{ $p->series_with_kva ? ' — ' . $p->series_with_kva : '' }}
                    </option>
                    @endforeach
                </select>
                @error('product_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Info nomor urut --}}
            <div class="flex items-start gap-2.5 px-3 py-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-blue-700 dark:text-blue-300">
                    Gambar akan diberi nama otomatis: <strong>Gambar 1, Gambar 2, ...</strong>
                    melanjutkan dari gambar yang sudah ada.
                </p>
            </div>

            {{-- Upload File --}}
            <div x-data="fileUploader()">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    File Gambar / PDF <span class="text-red-500">*</span>
                </label>

                <div class="relative border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-xl
                            hover:border-blue-400 transition-colors"
                     :class="files.length ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/10' : ''">
                    <input type="file" name="files[]" accept=".jpg,.jpeg,.png,.pdf" multiple
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                           @change="handleFiles($event)">
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center pointer-events-none">
                        <svg class="w-10 h-10 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <template x-if="!files.length">
                            <div>
                                <p class="text-sm text-slate-500">Klik atau <span class="text-blue-600 font-medium">seret file</span> ke sini</p>
                                <p class="text-xs text-slate-400 mt-1">Pilih banyak file sekaligus — JPG, PNG, PDF, maks. 100MB/file</p>
                            </div>
                        </template>
                        <template x-if="files.length">
                            <p class="text-sm font-semibold text-blue-600" x-text="files.length + ' file dipilih — klik untuk ganti'"></p>
                        </template>
                    </div>
                </div>

                {{-- Daftar file --}}
                <template x-if="files.length">
                    <ul class="mt-3 space-y-2">
                        <template x-for="(f, i) in files" :key="i">
                            <li class="flex items-center gap-3 px-3 py-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl text-sm">
                                <span class="shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold"
                                      :class="f.isPdf ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'"
                                      x-text="f.isPdf ? 'PDF' : 'IMG'"></span>
                                <span class="flex-1 truncate text-slate-700 dark:text-slate-300" x-text="f.name"></span>
                                <span class="shrink-0 text-xs text-slate-400" x-text="f.size"></span>
                            </li>
                        </template>
                    </ul>
                </template>

                @error('files')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                @error('files.*')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Keterangan
                    <span class="ml-1 text-xs font-normal text-slate-400">(berlaku untuk semua file)</span>
                </label>
                <textarea name="keterangan" rows="2"
                          placeholder="Contoh: Revisi ke-2, Tampak depan dan samping..."
                          class="w-full px-4 py-3 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900
                                 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('keterangan') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ $selectedProduct ? route('gambar-kerja.by-product', $selectedProduct) : route('gambar-kerja.index') }}"
                   class="flex-1 py-3 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function fileUploader() {
    return {
        files: [],
        handleFiles(event) {
            this.files = Array.from(event.target.files).map(f => ({
                name:  f.name,
                isPdf: f.name.toLowerCase().endsWith('.pdf'),
                size:  f.size > 1048576
                    ? (f.size / 1048576).toFixed(1) + ' MB'
                    : Math.round(f.size / 1024) + ' KB',
            }));
        }
    };
}
</script>
@endpush
