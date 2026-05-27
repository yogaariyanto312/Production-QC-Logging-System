@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan produksi hari ini')

@section('content')
<div class="space-y-4">

    {{-- ====== STAT CARDS ====== --}}
    <div class="{{ auth()->user()->isVisitor() ? 'grid grid-cols-1 sm:grid-cols-3 gap-3' : 'grid grid-cols-2 lg:grid-cols-4 gap-3' }}">

        {{-- Today --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xl font-bold text-slate-800 dark:text-white leading-tight">{{ number_format($stats['today_total']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Total Unit Hari Ini</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $stats['today_entries'] }} entri</p>
            </div>
            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 px-2 py-0.5 rounded-full shrink-0">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full pulse-dot"></span>Live
            </span>
        </div>

        {{-- Monthly --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/40 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xl font-bold text-slate-800 dark:text-white leading-tight">{{ number_format($stats['monthly_total']) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Total Bulan Ini</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        {{-- Products --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 flex items-center gap-3">
            <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xl font-bold text-slate-800 dark:text-white leading-tight">{{ $stats['total_products'] }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">Produk Aktif</p>
                <p class="text-xs text-slate-400 mt-0.5">Terdaftar di sistem</p>
            </div>
        </div>

        {{-- Quick Action --}}
        @unless(auth()->user()->isVisitor())
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl p-4 shadow-sm flex flex-col justify-center gap-2">
            <p class="text-xs font-semibold text-blue-100">Aksi Cepat</p>
            <a href="{{ route('production.create') }}"
               class="flex items-center gap-2 w-full py-2 px-3 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-medium transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Input Produksi
            </a>
            @if(auth()->user()->isPrivileged())
            <a href="{{ route('reports.daily') }}"
               class="flex items-center gap-2 w-full py-2 px-3 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-medium transition-colors">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Laporan Harian
            </a>
            @endif
        </div>
        @endunless
    </div>

    {{-- ====== CHARTS ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Trend Produksi</h3>
                    <p class="text-xs text-slate-400">7 hari terakhir</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-blue-500"></span>Total Unit</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-orange-400"></span>Entri</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-4 h-0.5 bg-yellow-400"></span>Rata-rata</span>
                </div>
            </div>
            <canvas id="lineChart" height="95"></canvas>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-100 dark:border-slate-700 flex flex-col">
            <div class="mb-3">
                <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Produk per Tipe</h3>
                <p class="text-xs text-slate-400">{{ now()->translatedFormat('F Y') }}</p>
            </div>
            {{-- Canvas --}}
            <div class="relative mx-auto" style="height:260px;width:260px">
                <canvas id="barChart"></canvas>
            </div>
            {{-- Legend HTML --}}
            <div id="doughnutLegend" class="mt-3 space-y-1.5"></div>
        </div>
    </div>

    {{-- ====== KATEGORI + DEPARTEMEN (privileged only) ====== --}}
    @if(auth()->user()->isPrivileged())
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Catatan --}}
        @php
            $noteColors = [
                'blue'   => ['bg' => 'bg-blue-500',   'light' => 'bg-blue-50 dark:bg-blue-900/20',   'text' => 'text-blue-600 dark:text-blue-400'],
                'green'  => ['bg' => 'bg-green-500',  'light' => 'bg-green-50 dark:bg-green-900/20', 'text' => 'text-green-600 dark:text-green-400'],
                'yellow' => ['bg' => 'bg-yellow-400', 'light' => 'bg-yellow-50 dark:bg-yellow-900/20','text' => 'text-yellow-600 dark:text-yellow-400'],
                'amber'  => ['bg' => 'bg-amber-500',  'light' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400'],
                'red'    => ['bg' => 'bg-red-500',    'light' => 'bg-red-50 dark:bg-red-900/20',     'text' => 'text-red-600 dark:text-red-400'],
                'purple' => ['bg' => 'bg-purple-500', 'light' => 'bg-purple-50 dark:bg-purple-900/20','text' => 'text-purple-600 dark:text-purple-400'],
                'teal'   => ['bg' => 'bg-teal-500',   'light' => 'bg-teal-50 dark:bg-teal-900/20',   'text' => 'text-teal-600 dark:text-teal-400'],
                'slate'  => ['bg' => 'bg-slate-500',  'light' => 'bg-slate-100 dark:bg-slate-700',   'text' => 'text-slate-600 dark:text-slate-400'],
            ];
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col">
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-amber-100 dark:bg-amber-900/40 rounded-lg flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Catatan</h3>
                        <p class="text-xs text-slate-400">{{ $recentNotes->count() }} catatan terbaru</p>
                    </div>
                </div>
                <a href="{{ route('notes.index') }}"
                   class="flex items-center gap-1 px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah
                </a>
            </div>

            {{-- List catatan --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-700 flex-1 overflow-y-auto max-h-64">
                @forelse($recentNotes as $note)
                @php $nc = $noteColors[$note->color ?? 'slate']; @endphp
                <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                    {{-- Strip warna --}}
                    <span class="w-1 h-full min-h-9 rounded-full {{ $nc['bg'] }} shrink-0 mt-0.5"></span>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate
                                  {{ $note->is_done ? 'line-through text-slate-400' : 'text-slate-800 dark:text-white' }}">
                            {{ $note->title }}
                        </p>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            @if($note->content)
                            <p class="text-xs text-slate-400 truncate max-w-40">{{ $note->content }}</p>
                            @endif
                            @if($note->due_date)
                            <span class="text-xs {{ $note->due_date->isPast() && !$note->is_done ? 'text-red-500' : 'text-slate-400' }} shrink-0">
                                · {{ $note->due_date->translatedFormat('d M') }}
                            </span>
                            @endif
                            @if($note->target_user_id && $note->target_user_id !== auth()->id())
                            <span class="text-xs text-slate-400 shrink-0">· → {{ $note->targetUser->name ?? '-' }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Status badge --}}
                    @if($note->is_done)
                    <span class="shrink-0 text-xs px-1.5 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded font-medium">
                        ✓ Selesai
                    </span>
                    @else
                    <span class="shrink-0 w-2 h-2 rounded-full bg-amber-400 mt-1.5"></span>
                    @endif
                </div>
                @empty
                <div class="flex flex-col items-center justify-center py-10 text-slate-400">
                    <svg class="w-8 h-8 mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <p class="text-sm">Belum ada catatan</p>
                </div>
                @endforelse
            </div>

            {{-- Footer --}}
            <div class="px-4 py-2 border-t border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30">
                <a href="{{ route('notes.index') }}" class="text-xs text-amber-600 hover:text-amber-700 dark:text-amber-400 font-medium">
                    Lihat semua catatan →
                </a>
            </div>
        </div>

        {{-- Kalender --}}
        @php
            $calNow      = \Carbon\Carbon::now();
            $daysInMonth = $calNow->daysInMonth;
            $startOffset = ($calNow->copy()->startOfMonth()->dayOfWeek + 6) % 7;
            $today       = (int) $calNow->day;
            $calYear     = (int) $calNow->year;
            $calMonth    = (int) $calNow->month;

            // Hari libur nasional Indonesia (tahun dinamis)
            $holidays = collect([
                // Tahun 2025
                ['date' => '2025-01-01', 'name' => 'Tahun Baru Masehi'],
                ['date' => '2025-01-27', 'name' => 'Isra Mi\'raj'],
                ['date' => '2025-01-29', 'name' => 'Tahun Baru Imlek'],
                ['date' => '2025-03-29', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2025-04-18', 'name' => 'Wafat Isa Al Masih'],
                ['date' => '2025-03-31', 'name' => 'Idul Fitri 1446 H'],
                ['date' => '2025-04-01', 'name' => 'Idul Fitri 1446 H (Hari ke-2)'],
                ['date' => '2025-05-01', 'name' => 'Hari Buruh Internasional'],
                ['date' => '2025-05-12', 'name' => 'Hari Raya Waisak'],
                ['date' => '2025-05-29', 'name' => 'Kenaikan Isa Al Masih'],
                ['date' => '2025-06-01', 'name' => 'Hari Lahir Pancasila'],
                ['date' => '2025-06-06', 'name' => 'Idul Adha 1446 H'],
                ['date' => '2025-06-27', 'name' => 'Tahun Baru Islam 1447 H'],
                ['date' => '2025-08-17', 'name' => 'HUT Kemerdekaan RI'],
                ['date' => '2025-09-05', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2025-12-25', 'name' => 'Hari Natal'],
                // Tahun 2026
                ['date' => '2026-01-01', 'name' => 'Tahun Baru Masehi'],
                ['date' => '2026-01-17', 'name' => 'Isra Mi\'raj'],
                ['date' => '2026-02-17', 'name' => 'Tahun Baru Imlek'],
                ['date' => '2026-03-19', 'name' => 'Hari Suci Nyepi'],
                ['date' => '2026-03-20', 'name' => 'Idul Fitri 1447 H'],
                ['date' => '2026-03-21', 'name' => 'Idul Fitri 1447 H (Hari ke-2)'],
                ['date' => '2026-04-03', 'name' => 'Wafat Isa Al Masih'],
                ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional'],
                ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Al Masih'],
                ['date' => '2026-05-22', 'name' => 'Hari Raya Waisak'],
                ['date' => '2026-05-27', 'name' => 'Idul Adha 1447 H (Hari Raya Kurban)'],
                ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila'],
                ['date' => '2026-06-17', 'name' => 'Tahun Baru Islam 1448 H'],
                ['date' => '2026-08-17', 'name' => 'HUT Kemerdekaan RI'],
                ['date' => '2026-08-26', 'name' => 'Maulid Nabi Muhammad SAW'],
                ['date' => '2026-12-25', 'name' => 'Hari Natal'],
            ])->keyBy('date');
        @endphp
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
            {{-- Header bulan --}}
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                <div class="w-7 h-7 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800 dark:text-white">{{ $calNow->translatedFormat('F Y') }}</h3>
                    <p class="text-xs text-slate-400">{{ $calNow->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <div class="p-4">
                {{-- Nama hari --}}
                <div class="grid grid-cols-7 mb-1">
                    @foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d)
                    <div class="text-center py-1 text-xs font-semibold
                                {{ in_array($d, ['Sab','Min']) ? 'text-rose-400' : 'text-slate-400 dark:text-slate-500' }}">
                        {{ $d }}
                    </div>
                    @endforeach
                </div>

                {{-- Grid tanggal --}}
                <div class="grid grid-cols-7 gap-y-0.5">
                    @for($i = 0; $i < $startOffset; $i++)<div></div>@endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateStr   = sprintf('%04d-%02d-%02d', $calYear, $calMonth, $day);
                        $dow       = ($calNow->copy()->day($day)->dayOfWeek + 6) % 7;
                        $isWeekend = $dow >= 5;
                        $isToday   = $day === $today;
                        $holiday   = $holidays->get($dateStr);
                        $isHoliday = (bool) $holiday;
                    @endphp
                    <div class="relative group flex flex-col items-center py-0.5">
                        {{-- Tanggal --}}
                        <div class="flex items-center justify-center rounded-full w-7 h-7 text-xs font-medium transition-colors
                            {{ $isToday                              ? 'bg-blue-600 text-white font-bold shadow-md' : '' }}
                            {{ !$isToday && ($isWeekend||$isHoliday) ? 'text-rose-400' : '' }}
                            {{ !$isToday && !$isWeekend && !$isHoliday ? 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' : '' }}">
                            {{ $day }}
                        </div>
                        {{-- Dot libur --}}
                        <span class="block w-1 h-1 rounded-full mt-0.5 {{ $isHoliday ? 'bg-rose-400' : 'invisible' }}"></span>

                        {{-- Tooltip hari libur --}}
                        @if($isHoliday)
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-30
                                    hidden group-hover:block w-max max-w-45 bg-slate-900
                                    text-white rounded-xl px-3 py-2 shadow-2xl pointer-events-none text-center"
                             style="font-size:11px">
                            <p class="font-bold text-rose-300">{{ \Carbon\Carbon::parse($dateStr)->translatedFormat('d F Y') }}</p>
                            <p class="text-slate-200 mt-0.5 leading-snug">{{ $holiday['name'] }}</p>
                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-slate-900"></div>
                        </div>
                        @endif
                    </div>
                    @endfor
                </div>

                {{-- Legend --}}
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center gap-5 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-4 h-4 rounded-full bg-blue-600 shrink-0"></span>Hari ini
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 shrink-0"></span>Hari libur nasional
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ====== MODAL EDIT CEPAT KATEGORI ====== --}}
    <div id="editCategoryModal"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 bg-linear-to-r from-amber-500 to-orange-500">
                <h3 class="text-sm font-bold text-white">Edit Cepat Kategori</h3>
                <button onclick="closeEditModal()" class="p-1 rounded-lg text-amber-100 hover:text-white hover:bg-white/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="editCategoryForm" method="POST" class="p-5 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" id="modal_name" name="name"
                           class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kode</label>
                        <input type="text" id="modal_code" name="code"
                               class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm font-mono uppercase" placeholder="Opsional">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer pb-1">
                            <input type="checkbox" id="modal_is_active" name="is_active" value="1"
                                   class="w-4 h-4 rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Aktif</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Keterangan</label>
                    <textarea id="modal_description" name="description" rows="2"
                              class="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm resize-none"
                              placeholder="Deskripsi..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeEditModal()"
                            class="flex-1 py-2 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-semibold transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif

    {{-- ====== RECENT LOGS ====== --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Input Produksi Terbaru</h3>
            <a href="{{ route('production.index') }}" class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                Lihat semua →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 uppercase tracking-wide">Tanggal</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 uppercase tracking-wide">Produk</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 uppercase tracking-wide">S1</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 uppercase tracking-wide">S2</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 uppercase tracking-wide">S3</th>
                        <th class="px-4 py-2.5 text-center font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-slate-500 uppercase tracking-wide">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-2.5 whitespace-nowrap">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $log->production_date->format('d/m/Y') }}</span>
                            @if($log->production_date->isToday())
                            <span class="ml-1 px-1 py-0.5 text-xs bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded">Hari ini</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5">
                            <div class="font-medium text-slate-800 dark:text-white">{{ $log->product->name ?? '-' }}</div>
                            @if($log->product->series_with_kva)
                            <div class="text-slate-400 font-mono">{{ $log->product->series_with_kva }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center text-slate-500 dark:text-slate-400">{{ number_format($log->shift1_qty) }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-500 dark:text-slate-400">{{ number_format($log->shift2_qty) }}</td>
                        <td class="px-4 py-2.5 text-center text-slate-500 dark:text-slate-400">{{ number_format($log->shift3_qty) }}</td>
                        <td class="px-4 py-2.5 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ number_format($log->total_qty) }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-500 dark:text-slate-400">{{ $log->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            <p class="text-sm">Belum ada data produksi hari ini</p>
                            @unless(auth()->user()->isVisitor())
                            <a href="{{ route('production.create') }}" class="mt-1 inline-block text-blue-600 text-xs hover:underline">Input sekarang →</a>
                            @endunless
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
const modal     = document.getElementById('editCategoryModal');
const modalForm = document.getElementById('editCategoryForm');
function openEditModal(id, name, code, description, isActive) {
    modalForm.action = '/categories/' + id;
    document.getElementById('modal_name').value        = name;
    document.getElementById('modal_code').value        = code;
    document.getElementById('modal_description').value = description;
    document.getElementById('modal_is_active').checked = isActive;
    modal.classList.remove('hidden'); modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    setTimeout(() => document.getElementById('modal_name').focus(), 50);
}
function closeEditModal() {
    modal.classList.add('hidden'); modal.classList.remove('flex');
    document.body.style.overflow = '';
}
modal?.addEventListener('click', e => { if (e.target === modal) closeEditModal(); });
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !modal?.classList.contains('hidden')) closeEditModal();
});
</script>
<script>
const isDark    = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#94a3b8' : '#64748b';

new Chart(document.getElementById('lineChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData->pluck('date')) !!},
        datasets: [
            {
                type: 'bar',
                label: 'Total Unit',
                data: {!! json_encode($chartData->pluck('total')) !!},
                backgroundColor: 'rgba(59,130,246,0.85)',
                borderRadius: 5,
                borderSkipped: false,
                yAxisID: 'yLeft',
                order: 2,
            },
            {
                type: 'bar',
                label: 'Entri',
                data: {!! json_encode($chartData->pluck('entries')) !!},
                backgroundColor: 'rgba(251,146,60,0.85)',
                borderRadius: 5,
                borderSkipped: false,
                yAxisID: 'yRight',
                order: 3,
            },
            {
                type: 'line',
                label: 'Rata-rata/Entri',
                data: {!! json_encode($chartData->pluck('avg')) !!},
                borderColor: '#facc15',
                backgroundColor: 'transparent',
                borderWidth: 2.5,
                pointBackgroundColor: '#facc15',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                yAxisID: 'yRight',
                order: 1,
            },
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        if (ctx.dataset.label === 'Total Unit')      return `  Total: ${ctx.parsed.y.toLocaleString('id-ID')} unit`;
                        if (ctx.dataset.label === 'Entri')           return `  Entri: ${ctx.parsed.y} records`;
                        if (ctx.dataset.label === 'Rata-rata/Entri') return `  Rata-rata: ${ctx.parsed.y} unit/entri`;
                        return ctx.parsed.y;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { size: 10 } }
            },
            yLeft: {
                type: 'linear',
                position: 'left',
                grid: { color: gridColor },
                ticks: { color: '#3b82f6', font: { size: 10 }, callback: v => v.toLocaleString('id-ID') },
                beginAtZero: true,
                title: { display: true, text: 'Unit', color: '#3b82f6', font: { size: 9 } }
            },
            yRight: {
                type: 'linear',
                position: 'right',
                grid: { drawOnChartArea: false },
                ticks: { color: '#fb923c', font: { size: 10 } },
                beginAtZero: true,
                title: { display: true, text: 'Entri / Rata-rata', color: '#fb923c', font: { size: 9 } }
            }
        }
    }
});

const doughnutLabels = {!! json_encode($productChart->pluck('name')) !!};
const doughnutData   = {!! json_encode($productChart->pluck('total')) !!};
const doughnutColors = ['#38bdf8','#818cf8','#34d399','#fb923c','#f472b6','#facc15','#a78bfa'];
const doughnutTotal  = doughnutData.reduce((a, b) => a + b, 0);

// Plugin: teks di tengah donut
const centerTextPlugin = {
    id: 'centerText',
    afterDraw(chart) {
        if (chart.config.type !== 'doughnut') return;
        const { ctx, chartArea: { left, right, top, bottom } } = chart;
        const cx = (left + right) / 2;
        const cy = (top + bottom) / 2;
        ctx.save();
        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
        ctx.font      = `600 12px Inter,sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.fillText('Total', cx, cy - 16);
        ctx.font      = `bold 26px Inter,sans-serif`;
        ctx.fillStyle = '#ffffff';
        ctx.fillText(doughnutTotal.toLocaleString('id-ID'), cx, cy + 10);
        ctx.font      = `11px Inter,sans-serif`;
        ctx.fillStyle = 'rgba(255,255,255,0.6)';
        ctx.fillText('unit', cx, cy + 28);
        ctx.restore();
    }
};

new Chart(document.getElementById('barChart').getContext('2d'), {
    type: 'doughnut',
    plugins: [centerTextPlugin],
    data: {
        labels: doughnutLabels,
        datasets: [{
            data: doughnutData,
            backgroundColor: doughnutLabels.map((_, i) => doughnutColors[i % doughnutColors.length]),
            borderWidth: 3,
            borderColor: isDark ? '#1e293b' : '#ffffff',
            hoverOffset: 12,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: { display: false },
            datalabels: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const pct = doughnutTotal > 0 ? Math.round(ctx.parsed / doughnutTotal * 100) : 0;
                        return `  ${ctx.parsed.toLocaleString('id-ID')} unit (${pct}%)`;
                    }
                }
            }
        }
    }
});

// Render legend HTML
const legendEl = document.getElementById('doughnutLegend');
if (legendEl && doughnutData.length) {
    legendEl.innerHTML = doughnutLabels.map((label, i) => {
        const val = doughnutData[i];
        const pct = doughnutTotal > 0 ? Math.round(val / doughnutTotal * 100) : 0;
        const w   = doughnutTotal > 0 ? Math.round(val / doughnutTotal * 100) : 0;
        return `
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:${doughnutColors[i % doughnutColors.length]}"></span>
            <span class="text-xs text-slate-600 dark:text-slate-300 flex-1 truncate">${label}</span>
            <span class="text-xs font-semibold text-slate-800 dark:text-white">${val.toLocaleString('id-ID')} <span class="font-normal text-slate-400">Unit</span></span>
            <div class="w-16 bg-slate-100 dark:bg-slate-700 rounded-full h-1.5 overflow-hidden">
                <div class="h-full rounded-full" style="width:${w}%;background:${doughnutColors[i % doughnutColors.length]}"></div>
            </div>
            <span class="text-xs text-slate-400 w-7 text-right">${pct}%</span>
        </div>`;
    }).join('');
}
</script>
@endpush
