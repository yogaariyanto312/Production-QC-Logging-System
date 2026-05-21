@extends('layouts.app')

@section('title', 'Detail Produksi')
@section('page-title', 'Detail Produksi')
@section('page-subtitle', 'Informasi lengkap data produksi')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-linear-to-r from-slate-700 to-slate-800 px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ $productionLog->product->name ?? '-' }}</h2>
                    @if($productionLog->product->series_with_kva)
                    <p class="text-slate-300 text-sm font-mono mt-1">{{ $productionLog->product->series_with_kva }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-3xl font-black text-white">
                        {{ fmod((float)$productionLog->total_qty, 1) == 0 ? number_format($productionLog->total_qty) : number_format($productionLog->total_qty, 1) }}
                    </p>
                    <p class="text-slate-400 text-xs">Total Unit</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-5">

            {{-- Channel breakdown --}}
            @if(($productionLog->product->type ?? 'regular') === 'channel')
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
                    <p class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">Channel UP</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($productionLog->shift1_qty) }}</p>
                    <p class="text-xs text-blue-500">unit</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 text-center">
                    <p class="text-xs font-medium text-purple-600 dark:text-purple-400 mb-1">Channel BT</p>
                    <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($productionLog->shift2_qty) }}</p>
                    <p class="text-xs text-purple-500">unit</p>
                </div>
            </div>
            <p class="text-xs text-center text-slate-400">Total = (UP + BT) ÷ 2 = ({{ $productionLog->shift1_qty }} + {{ $productionLog->shift2_qty }}) ÷ 2 = {{ fmod((float)$productionLog->total_qty, 1) == 0 ? number_format($productionLog->total_qty) : number_format($productionLog->total_qty, 1) }}</p>
            @endif

            {{-- Info rows --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-700">
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Tanggal Produksi</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $productionLog->production_date->translatedFormat('l, d F Y') }}
                    </span>
                </div>
                @if($productionLog->operator_name)
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Nama Operator</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">{{ $productionLog->operator_name }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Kategori</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $productionLog->product->category->name ?? '-' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Satuan</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $productionLog->product->unit ?? 'unit' }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Diinput oleh</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $productionLog->user->name ?? '-' }}
                        <span class="text-xs text-slate-400 capitalize">({{ $productionLog->user->role ?? '' }})</span>
                    </span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm text-slate-500">Waktu Input</span>
                    <span class="text-sm font-medium text-slate-800 dark:text-white">
                        {{ $productionLog->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                @if($productionLog->notes)
                <div class="py-3">
                    <span class="text-sm text-slate-500">Catatan</span>
                    <p class="mt-1 text-sm text-slate-800 dark:text-white bg-slate-50 dark:bg-slate-700/50 rounded-lg p-3">
                        {{ $productionLog->notes }}
                    </p>
                </div>
                @endif
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('production.index') }}"
                   class="flex-1 py-3 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold text-sm transition-colors">
                    ← Kembali
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('production.edit', $productionLog) }}"
                   class="flex-1 py-3 text-center text-white bg-amber-500 hover:bg-amber-600 rounded-xl font-semibold text-sm transition-colors">
                    Edit Data
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
