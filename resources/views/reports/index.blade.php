@extends('layouts.app')

@section('title', 'Laporan Produksi')
@section('page-title', 'Laporan Produksi')
@section('page-subtitle', 'Rekap dan export data produksi bulanan')

@section('content')
<div class="space-y-6">

    {{-- Filter Bulan --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Bulan</label>
                <select name="month"
                        class="px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Tahun</label>
                <select name="year"
                        class="px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                               bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                Tampilkan
            </button>
            <div class="flex-1"></div>
            {{-- Export Buttons --}}
            <a href="{{ route('reports.export-excel', ['month' => $month, 'year' => $year]) }}"
               class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('reports.export-pdf', ['month' => $month, 'year' => $year]) }}"
               class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </a>
            <a href="{{ route('reports.daily') }}"
               class="px-4 py-2.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold rounded-xl flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Laporan Harian
            </a>
        </form>
    </div>

    {{-- Report Title --}}
    <div class="flex items-center gap-3">
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">
            Rekap Produksi — {{ $months[$month] ?? '' }} {{ $year }}
        </h2>
        <span class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 text-sm font-semibold rounded-full">
            {{ $report->count() }} produk · Total: {{ number_format($report->sum('grand_total')) }} unit
        </span>
    </div>

    {{-- Monthly Report Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Seri</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 1</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 2</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 3</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Grand Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($report as $row)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <td class="px-5 py-4 font-semibold text-slate-800 dark:text-white">
                            {{ $row->product->name ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium
                                         bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400">
                                {{ $row->product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs text-slate-500">{{ $row->product->series_with_kva ?: '-' }}</td>
                        <td class="px-5 py-4 text-center font-medium text-slate-700 dark:text-slate-300">{{ number_format($row->total_shift1) }}</td>
                        <td class="px-5 py-4 text-center font-medium text-slate-700 dark:text-slate-300">{{ number_format($row->total_shift2) }}</td>
                        <td class="px-5 py-4 text-center font-medium text-slate-700 dark:text-slate-300">{{ number_format($row->total_shift3) }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold
                                         bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ number_format($row->grand_total) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                            Tidak ada data produksi untuk periode ini
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($report->count() > 0)
                <tfoot class="bg-slate-50 dark:bg-slate-900/50 border-t-2 border-slate-200 dark:border-slate-600">
                    <tr>
                        <td colspan="3" class="px-5 py-4 text-sm font-bold text-slate-700 dark:text-slate-300">TOTAL</td>
                        <td class="px-5 py-4 text-center text-sm font-bold text-slate-700 dark:text-slate-300">{{ number_format($report->sum('total_shift1')) }}</td>
                        <td class="px-5 py-4 text-center text-sm font-bold text-slate-700 dark:text-slate-300">{{ number_format($report->sum('total_shift2')) }}</td>
                        <td class="px-5 py-4 text-center text-sm font-bold text-slate-700 dark:text-slate-300">{{ number_format($report->sum('total_shift3')) }}</td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex px-3 py-1 rounded-full text-sm font-black
                                         bg-blue-600 text-white">
                                {{ number_format($report->sum('grand_total')) }}
                            </span>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>
@endsection
