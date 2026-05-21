@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan produksi hari ini')

@section('content')
<div class="space-y-6">

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Today Input --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 px-2 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full pulse-dot"></span>Live
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['today_total']) }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total Unit Hari Ini</p>
            <p class="text-xs text-slate-400 mt-1">{{ $stats['today_entries'] }} entri produksi</p>
        </div>

        {{-- Monthly Total --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['monthly_total']) }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total Bulan Ini</p>
            <p class="text-xs text-slate-400 mt-1">{{ now()->translatedFormat('F Y') }}</p>
        </div>

        {{-- Total Products --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['total_products'] }}</p>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Produk Aktif</p>
            <p class="text-xs text-slate-400 mt-1">Terdaftar di sistem</p>
        </div>

        {{-- Quick Action --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-blue-100 mb-3">Aksi Cepat</p>
            <a href="{{ route('production.create') }}"
               class="flex items-center gap-2 w-full py-2.5 px-4 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Input Produksi
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('reports.daily') }}"
               class="flex items-center gap-2 w-full py-2.5 px-4 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-colors mt-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Laporan Harian
            </a>
            @endif
        </div>
    </div>

    {{-- ====== CHARTS ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Line Chart - 7 hari terakhir --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-semibold text-slate-800 dark:text-white">Trend Produksi</h3>
                    <p class="text-xs text-slate-500">7 hari terakhir</p>
                </div>
            </div>
            <canvas id="lineChart" height="100"></canvas>
        </div>

        {{-- Bar Chart - Top produk --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="mb-6">
                <h3 class="text-base font-semibold text-slate-800 dark:text-white">Top 5 Produk</h3>
                <p class="text-xs text-slate-500">Bulan {{ now()->translatedFormat('F Y') }}</p>
            </div>
            <canvas id="barChart" height="180"></canvas>
        </div>
    </div>

    {{-- ====== RECENT LOGS ====== --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-base font-semibold text-slate-800 dark:text-white">Input Produksi Terbaru</h3>
            <a href="{{ route('production.index') }}"
               class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium">
                Lihat semua →
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 1</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 2</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 3</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-slate-700 dark:text-slate-300 font-medium">
                                {{ $log->production_date->format('d/m/Y') }}
                            </span>
                            @if($log->production_date->isToday())
                            <span class="ml-1 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded-md">Hari ini</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 dark:text-white">{{ $log->product->name ?? '-' }}</div>
                            @if($log->product->series)
                            <div class="text-xs text-slate-400">{{ $log->product->series }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift1_qty) }}</td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift2_qty) }}</td>
                        <td class="px-6 py-4 text-center text-slate-600 dark:text-slate-400">{{ number_format($log->shift3_qty) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ number_format($log->total_qty) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-sm">{{ $log->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                            </svg>
                            <p>Belum ada data produksi hari ini</p>
                            <a href="{{ route('production.create') }}" class="mt-2 inline-block text-blue-600 text-sm hover:underline">
                                Input sekarang →
                            </a>
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
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
const textColor = isDark ? '#94a3b8' : '#64748b';

// Line Chart - Trend Produksi
const lineCtx = document.getElementById('lineChart').getContext('2d');
const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData->pluck('date')) !!},
        datasets: [{
            label: 'Total Produksi',
            data: {!! json_encode($chartData->pluck('total')) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.08)',
            borderWidth: 2.5,
            pointBackgroundColor: '#3b82f6',
            pointRadius: 4,
            pointHoverRadius: 6,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.parsed.y.toLocaleString('id-ID')} unit`
                }
            }
        },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
            y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => v.toLocaleString('id-ID') }, beginAtZero: true }
        }
    }
});

// Bar Chart - Top Produk
const barCtx = document.getElementById('barChart').getContext('2d');
const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($productChart->pluck('name')) !!},
        datasets: [{
            label: 'Total',
            data: {!! json_encode($productChart->pluck('total')) !!},
            backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b'],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 }, callback: v => v.toLocaleString('id-ID') }, beginAtZero: true },
            y: { grid: { display: false }, ticks: { color: textColor, font: { size: 11 } } }
        }
    }
});
</script>
@endpush
