@extends('layouts.app')

@section('title', 'Riwayat Produksi')
@section('page-title', 'Riwayat Produksi')
@section('page-subtitle', 'Histori data produksi harian')

@section('content')
<div class="space-y-5">

    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <form method="GET" action="{{ route('production.index') }}" class="space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Cari Produk</label>
                    <input type="text" name="search" id="search-realtime"
                           value="{{ request('search') }}"
                           placeholder="Nama atau seri produk..."
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Produk</label>
                    <select name="product_name"
                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Produk</option>
                        @foreach($products as $name)
                        <option value="{{ $name }}" {{ request('product_name') == $name ? 'selected' : '' }}>{{ $name }}</option>
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
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-3 sm:px-5 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors whitespace-nowrap">
                    Filter
                </button>
                <a href="{{ route('production.index') }}"
                   class="px-3 sm:px-5 py-2 sm:py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600
                          text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors whitespace-nowrap">
                    Reset
                </a>
                <div class="flex-1"></div>
                @unless(auth()->user()->isVisitor())
                <a href="{{ route('production.create') }}"
                   class="px-3 sm:px-5 py-2 sm:py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Input Baru
                </a>
                @endunless
            </div>
        </form>
    </div>

    @php $fmtQty = fn($v) => fmod((float)$v, 1) == 0 ? number_format($v) : number_format($v, 1); @endphp

    {{-- Summary Bar --}}
    @if($totalCount > 0)
    @php $summaryLabel = now()->translatedFormat('d F Y'); @endphp
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-4 py-1.5 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wide">Ringkasan · {{ $summaryLabel }}</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4">
            {{-- Channel --}}
            <div class="px-3 py-2.5 text-center border-r border-b sm:border-b-0 border-slate-100 dark:border-slate-700">
                <p class="text-[10px] font-medium text-slate-400 mb-1.5">Total Channel</p>
                <div class="flex items-center justify-center gap-2">
                    <div class="text-center">
                        <span class="text-[10px] text-blue-400">UP</span>
                        <p class="text-sm font-bold text-blue-600 dark:text-blue-400 leading-tight">{{ number_format($totalUp) }}</p>
                    </div>
                    <span class="text-slate-200 dark:text-slate-700 font-light">|</span>
                    <div class="text-center">
                        <span class="text-[10px] text-purple-400">BT</span>
                        <p class="text-sm font-bold text-purple-600 dark:text-purple-400 leading-tight">{{ number_format($totalBt) }}</p>
                    </div>
                </div>
            </div>
            {{-- Tanki --}}
            <div class="px-3 py-2.5 text-center border-b sm:border-b-0 sm:border-r border-slate-100 dark:border-slate-700">
                <p class="text-[10px] font-medium text-slate-400 mb-1.5">Total Tangki</p>
                <div class="flex items-baseline justify-center gap-1">
                    <p class="text-sm font-bold text-slate-700 dark:text-white">{{ $fmtQty($totalTanki) }}</p>
                    <span class="text-[10px] text-slate-400">U</span>
                </div>
            </div>
            {{-- Cover --}}
            <div class="px-3 py-2.5 text-center border-r border-b sm:border-b-0 border-slate-100 dark:border-slate-700">
                <p class="text-[10px] font-medium text-slate-400 mb-1.5">Total Cover</p>
                <div class="flex items-baseline justify-center gap-1">
                    <p class="text-sm font-bold text-slate-700 dark:text-white">{{ $fmtQty($totalCover) }}</p>
                    <span class="text-[10px] text-slate-400">U</span>
                </div>
            </div>
            {{-- Total Keseluruhan --}}
            <div class="px-3 py-2.5 text-center border-b sm:border-b-0 border-slate-100 dark:border-slate-700">
                <p class="text-[10px] font-medium text-slate-400 mb-1.5">Total Keseluruhan</p>
                <div class="flex items-baseline justify-center gap-1">
                    <p class="text-sm font-bold text-slate-700 dark:text-white">{{ $fmtQty($grandTotal) }}</p>
                    <span class="text-[10px] text-slate-400">U</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Info pagination --}}
    @if($dates->total() > 0)
    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            Hal. <strong class="text-slate-700 dark:text-slate-300">{{ $dates->currentPage() }}</strong>
            · <strong class="text-slate-700 dark:text-slate-300">{{ $dates->count() }}</strong> hari
            dari <strong class="text-slate-700 dark:text-slate-300">{{ $dates->total() }}</strong> hari total
        </p>
        @if($dates->hasPages())
        <div class="sm:ml-auto">{{ $dates->onEachSide(0)->links() }}</div>
        @endif
    </div>
    @endif

    {{-- Grouped by date then category --}}
    @php
        $dateGroups = $logs->groupBy(fn($l) => $l->production_date->format('Y-m-d'));
    @endphp

    @forelse($dateGroups as $dateStr => $dayLogs)
    @php
        $carbon    = \Illuminate\Support\Carbon::parse($dateStr);
        $dayName   = $carbon->translatedFormat('l');
        $dateFmt   = $carbon->translatedFormat('d F Y');
        $catOrder  = ['Channel' => 0, 'Cover' => 1, 'Tangki' => 2];
        $catGroups = $dayLogs->groupBy(function($l) {
            $name = strtolower($l->product->category->name ?? '');
            if (str_contains($name, 'channel')) return 'Channel';
            if (str_contains($name, 'cover'))   return 'Cover';
            if (str_contains($name, 'tangki'))  return 'Tangki';
            return $l->product->category->name ?? 'Lainnya';
        })
        ->sortBy(fn($logs, $key) => $catOrder[$key] ?? 99)
        ->map(fn($logs) => $logs->sortBy(fn($l) => (float)($l->product->kva ?? 0)));
        $dayTotal  = $dayLogs->sum('total_qty');
    @endphp
    <div class="space-y-3">

        {{-- Date header --}}
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-1.5 bg-slate-700 dark:bg-slate-600 text-white rounded-full shadow-sm shrink-0">
                <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-xs font-semibold text-slate-300">{{ $dayName }}</span>
                <span class="text-sm font-bold">{{ $dateFmt }}</span>
            </div>
            <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
            <span class="text-xs text-slate-400 font-medium shrink-0">
                {{ $fmtQty($dayTotal) }} unit · {{ $dayLogs->count() }} entri
            </span>
        </div>

        {{-- Category cards per day --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($catGroups as $catName => $catLogs)
            @php
                $catTotal     = $catLogs->sum('total_qty');
                $catNameLower = strtolower($catName);
                if (str_contains($catNameLower, 'channel')) {
                    $cardIcon  = 'M13 10V3L4 14h7v7l9-11h-7z';
                    $cardColor = 'text-blue-600 dark:text-blue-400';
                    $cardBg    = 'bg-blue-100 dark:bg-blue-900/40';
                } elseif (str_contains($catNameLower, 'cover')) {
                    $cardIcon  = 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4';
                    $cardColor = 'text-emerald-600 dark:text-emerald-400';
                    $cardBg    = 'bg-emerald-100 dark:bg-emerald-900/40';
                } elseif (str_contains($catNameLower, 'tangki')) {
                    $cardIcon  = 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4';
                    $cardColor = 'text-amber-600 dark:text-amber-400';
                    $cardBg    = 'bg-amber-100 dark:bg-amber-900/40';
                } else {
                    $cardIcon  = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
                    $cardColor = 'text-slate-600 dark:text-slate-400';
                    $cardBg    = 'bg-slate-100 dark:bg-slate-700';
                }
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg {{ $cardBg }} flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5 {{ $cardColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cardIcon }}"/>
                            </svg>
                        </div>
                        <span class="text-sm font-bold text-slate-700 dark:text-white">{{ $catName }}</span>
                        <span class="text-xs text-slate-400">· {{ $catLogs->count() }} entri</span>
                    </div>
                    <span class="text-sm font-bold {{ $cardColor }}">
                        {{ $fmtQty($catTotal) }} <span class="text-xs font-normal text-slate-400">unit</span>
                    </span>
                </div>

                {{-- Rows --}}
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($catLogs as $log)
                    @php
                        $isChannel = ($log->product->type ?? 'regular') === 'channel';
                        $catRaw    = strtolower($log->product->category->name ?? '');
                        $isSwasta  = str_contains($catRaw, 'swasta');
                        $isType    = str_contains($catRaw, 'type');
                        $isPln     = !$isSwasta && !$isType && (str_contains($catRaw, 'pln') || str_contains($catRaw, 'channel') || str_contains($catRaw, 'cover') || str_contains($catRaw, 'tangki'));
                        $seriesColor = $isSwasta ? 'text-red-500 dark:text-red-400'
                                     : ($isType  ? 'text-blue-500 dark:text-blue-400'
                                     :             'text-slate-200 dark:text-white');
                        $typeBadge   = $isSwasta ? ['l' => 'Swasta',   'c' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300']
                                     : ($isType  ? ['l' => 'Typetest', 'c' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300']
                                     : ($isPln   ? ['l' => 'PLN',      'c' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300']
                                     : null));
                    @endphp
                    <div class="px-3 py-1.5 sm:px-4 sm:py-2 flex items-center gap-2 sm:gap-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">

                        {{-- Info kiri --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-white uppercase leading-tight">{{ preg_replace('/\s+(typetest|swasta|pln)\b/i', '', $log->product->name ?? '-') }}</p>
                                @if($log->product->series_with_kva)
                                <span class="text-[10px] sm:text-xs {{ $seriesColor }} font-mono font-semibold">{{ $log->product->series_with_kva }}</span>
                                @endif
                                @if($typeBadge)
                                <span class="text-[10px] px-1 py-0.5 rounded-full font-semibold {{ $typeBadge['c'] }}">{{ $typeBadge['l'] }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 mt-0.5 overflow-hidden">
                                @if($log->reject_qty > 0)
                                <span class="shrink-0 text-[10px] font-semibold px-1 py-0.5 rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                    ✕ {{ $log->reject_qty }}
                                </span>
                                @endif
                                @php
                                    $fallback = $isChannel ? ($lastChannelNums[$log->product_id] ?? []) : [];
                                    $chLines2 = collect(explode("\n", $log->notes ?? ''))->map(fn($l) => trim($l))->filter();
                                    $upLine2  = $chLines2->first(fn($l) => preg_match('/\bUP\b/i', $l));
                                    $btLine2  = $chLines2->first(fn($l) => preg_match('/\bBT\b/i', $l));
                                    $upDisp2  = $upLine2 ?: ($fallback['up'] ?? null);
                                    $btDisp2  = $btLine2 ?: ($fallback['bt'] ?? null);
                                @endphp
                                @if($isChannel && ($upDisp2 || $btDisp2))
                                <span class="text-[10px] sm:text-xs font-mono text-slate-400 truncate min-w-0">{{ trim(($upDisp2 ?? '') . ' ' . ($btDisp2 ?? '')) }}</span>
                                <span class="shrink-0 text-slate-300 dark:text-slate-600 text-[10px]">·</span>
                                @elseif(!$isChannel && $log->notes)
                                <span class="text-[10px] sm:text-xs text-slate-400 font-mono truncate min-w-0">{{ Str::limit($log->notes, 40) }}</span>
                                <span class="shrink-0 text-slate-300 dark:text-slate-600 text-[10px]">·</span>
                                @endif
                                <span class="shrink-0 text-[10px] sm:text-xs text-slate-400">{{ $log->user->name ?? '-' }}</span>
                            </div>
                            @if($log->keterangan)
                            <p class="text-[10px] sm:text-xs text-amber-500 dark:text-amber-400 italic mt-0.5 truncate">{{ Str::limit($log->keterangan, 40) }}</p>
                            @endif
                        </div>

                        {{-- Channel: UP/BT + Total ditumpuk vertikal --}}
                        @if($isChannel)
                        <div class="shrink-0 flex flex-col items-end gap-1">
                            <div class="flex items-center gap-1.5 text-xs">
                                <span class="text-slate-400">UP</span>
                                <span class="font-bold text-blue-500">{{ number_format($log->shift1_qty) }}</span>
                                <span class="text-slate-600">|</span>
                                <span class="text-slate-400">BT</span>
                                <span class="font-bold text-purple-500">{{ number_format($log->shift2_qty) }}</span>
                            </div>
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-xs font-bold
                                         bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                <span class="text-[10px] font-normal opacity-70">Total:</span>
                                {{ $fmtQty($log->total_qty) }}
                                <span class="text-[10px] font-normal opacity-70">Unit</span>
                            </span>
                        </div>
                        @else
                        {{-- Regular: Total saja --}}
                        <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-sm font-bold
                                     bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="text-xs font-normal opacity-70">Total:</span>
                            {{ $fmtQty($log->total_qty) }}
                            <span class="text-xs font-normal opacity-70">Unit</span>
                        </span>
                        @endif

                        {{-- Actions --}}
                        <div class="shrink-0 flex items-center gap-0.5">
                            <a href="{{ route('production.show', $log) }}"
                               class="p-1 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                               title="Detail">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @unless(auth()->user()->isVisitor())
                            <a href="{{ route('production.edit', $log) }}"
                               class="p-1 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                               title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('production.destroy', $log) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        data-confirm="Hapus data produksi {{ $log->product->name ?? '' }} tanggal {{ $log->production_date->format('d/m/Y') }}?"
                                        class="p-1 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                        title="Hapus">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endunless
                        </div>

                    </div>
                    @endforeach
                </div>

            </div>
            @endforeach
        </div>

    </div>
    @empty
    <div class="bg-white dark:bg-slate-800 rounded-2xl py-16 text-center border border-slate-100 dark:border-slate-700">
        <svg class="w-14 h-14 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
        </svg>
        <p class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada data produksi</p>
        <p class="text-slate-400 text-sm mt-1">Coba ubah filter pencarian atau tambah data baru</p>
        <a href="{{ route('production.create') }}"
           class="mt-4 inline-block px-5 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700">
            Input Produksi Sekarang
        </a>
    </div>
    @endforelse


    {{-- Chat Admin (Operator only) --}}
    @if(auth()->user()->role === 'operator')
    <div x-data="prodChatPanel()" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        <button @click="open = !open; if(open){ load(); $nextTick(scrollBottom) }"
                class="w-full flex items-center justify-between px-5 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Chat ke Admin</p>
                    <p class="text-xs text-slate-400">Kirim pertanyaan atau laporan ke admin</p>
                </div>
                <span x-show="unread > 0" x-text="unread + ' balasan baru'"
                      class="ml-2 px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full"></span>
            </div>
            <svg :class="open ? 'rotate-180' : ''" class="w-5 h-5 text-slate-400 transition-transform duration-200"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-slate-100 dark:border-slate-700">
            <div id="prod-chat-messages" class="h-72 overflow-y-auto p-4 bg-slate-50 dark:bg-slate-900/30">
                <p x-show="bubbles.length === 0"
                   class="text-sm text-slate-400 text-center py-12">Belum ada pesan. Kirim pertanyaan ke admin!</p>
                <div class="space-y-1.5">
                    <template x-for="b in bubbles" :key="b.id">
                        <div>
                            <template x-if="b.type === 'operator'">
                                <div class="flex justify-end mb-1">
                                    <div class="max-w-[75%] bg-blue-600 text-white text-sm rounded-2xl rounded-br-sm px-4 py-2.5 shadow-sm">
                                        <p x-text="b.text" class="leading-relaxed"></p>
                                        <p class="text-blue-200 text-xs mt-1 text-right" x-text="b.time"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="b.type === 'admin'">
                                <div class="flex justify-start mb-1">
                                    <div class="max-w-[75%] bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600
                                                text-slate-800 dark:text-white text-sm rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-sm">
                                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 mb-1">Admin</p>
                                        <p x-text="b.text" class="leading-relaxed"></p>
                                        <p class="text-slate-400 text-xs mt-1" x-text="b.time"></p>
                                    </div>
                                </div>
                            </template>
                            <template x-if="b.type === 'pending'">
                                <div class="flex justify-end mb-2">
                                    <span class="text-xs text-slate-400 italic">Menunggu balasan admin...</span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                <div class="flex gap-3">
                    <input x-model="newMsg" @keydown.enter.prevent="send()"
                           type="text" placeholder="Ketik pesan ke admin..."
                           class="flex-1 px-4 py-2.5 text-sm bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-white
                                  rounded-xl border border-slate-200 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button @click="send()" :disabled="!newMsg.trim() || sending"
                            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white text-sm font-semibold
                                   rounded-xl transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span x-text="sending ? 'Mengirim...' : 'Kirim'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function prodChatPanel() {
    return {
        open: false,
        messages: [],
        newMsg: '',
        unread: 0,
        sending: false,

        get bubbles() {
            const list = [];
            for (const msg of this.messages) {
                list.push({ id: 'op_' + msg.id, type: 'operator', text: msg.message, time: this.formatTime(msg.created_at) });
                if (msg.reply) {
                    list.push({ id: 'ad_' + msg.id, type: 'admin', text: msg.reply, time: this.formatTime(msg.replied_at) });
                } else {
                    list.push({ id: 'pd_' + msg.id, type: 'pending' });
                }
            }
            return list;
        },

        async load() {
            try {
                const res  = await fetch('{{ route('messages.my') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await res.json();
                const prevReplied = this.messages.filter(m => m.reply).length;
                this.messages = data;
                if (!this.open) {
                    const nowReplied = data.filter(m => m.reply).length;
                    if (nowReplied > prevReplied) this.unread += (nowReplied - prevReplied);
                } else {
                    this.unread = 0;
                }
            } catch(e) {}
        },

        async send() {
            const msg = this.newMsg.trim();
            if (!msg || this.sending) return;
            this.sending = true;
            this.newMsg  = '';
            try {
                await fetch('{{ route('messages.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message: msg })
                });
                await this.load();
                this.$nextTick(() => this.scrollBottom());
            } catch(e) {}
            this.sending = false;
        },

        scrollBottom() {
            const el = document.getElementById('prod-chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        },

        formatTime(ts) {
            if (!ts) return '';
            const d = new Date(ts);
            return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short' }) + ' ' +
                   d.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
        },

        init() {
            this.load();
            setInterval(() => this.load(), 30000);
        }
    };
}
</script>

<script>
(function () {
    const POLL_URL = '{{ route("api.production.poll") }}';
    const INTERVAL = 15000;

    let lastTs    = null;
    let lastCount = null;
    let banner    = null;

    function showBanner() {
        if (banner) return;
        banner = document.createElement('div');
        banner.id = 'prod-realtime-banner';
        banner.style.cssText = 'position:fixed;bottom:1.25rem;left:50%;transform:translateX(-50%);z-index:9999;animation:slideUp .3s ease';
        banner.innerHTML = `
            <button onclick="window.location.reload()"
                    class="flex items-center gap-2.5 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold
                           rounded-2xl shadow-xl transition-colors whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Data produksi diperbarui — klik untuk muat ulang
            </button>`;
        document.body.appendChild(banner);
    }

    async function poll() {
        try {
            const res  = await fetch(POLL_URL, { credentials: 'same-origin' });
            if (!res.ok) return;
            const data = await res.json();

            if (lastTs === null) {
                lastTs    = data.ts;
                lastCount = data.count;
                return;
            }

            if (data.ts !== lastTs || data.count !== lastCount) {
                showBanner();
            }

            lastTs    = data.ts;
            lastCount = data.count;
        } catch (_) {}
    }

    // Inisialisasi setelah 2 detik, lalu poll tiap 15 detik
    setTimeout(function () {
        poll();
        setInterval(poll, INTERVAL);
    }, 2000);
}());
</script>
<style>
@keyframes slideUp {
    from { opacity: 0; transform: translateX(-50%) translateY(12px); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0); }
}
</style>
@endpush
