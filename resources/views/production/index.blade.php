@extends('layouts.app')

@section('title', 'Riwayat Produksi')
@section('page-title', 'Riwayat Produksi')
@section('page-subtitle', 'Histori data produksi harian')

@section('content')
<div class="space-y-5">

    {{-- Filter Panel --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 shadow-sm border border-slate-100 dark:border-slate-700">
        <form method="GET" action="{{ route('production.index') }}" class="space-y-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Cari Produk</label>
                    <input type="text" name="search" id="search-realtime"
                           value="{{ request('search') }}"
                           placeholder="Nama atau seri produk..."
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Filter Produk --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Produk</label>
                    <select name="product_id"
                            class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                   bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Produk</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal From --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Filter Tanggal To --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2.5 text-sm border border-slate-300 dark:border-slate-600 rounded-xl
                                  bg-white dark:bg-slate-900 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Filter
                </button>
                <a href="{{ route('production.index') }}"
                   class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600
                          text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors">
                    Reset
                </a>
                <div class="flex-1"></div>
                <a href="{{ route('production.create') }}"
                   class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Input Baru
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Bar --}}
    @if($logs->count() > 0)
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ number_format($logs->sum('shift1_qty')) }}</p>
            <p class="text-xs text-blue-500 mt-1">Total Shift 1 (halaman ini)</p>
        </div>
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-purple-700 dark:text-purple-300">{{ number_format($logs->sum('shift2_qty')) }}</p>
            <p class="text-xs text-purple-500 mt-1">Total Shift 2 (halaman ini)</p>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-4 text-center">
            <p class="text-xl font-bold text-green-700 dark:text-green-300">{{ number_format($logs->sum('total_qty')) }}</p>
            <p class="text-xs text-green-500 mt-1">Grand Total (halaman ini)</p>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Menampilkan <strong class="text-slate-700 dark:text-slate-300">{{ $logs->count() }}</strong>
                dari <strong class="text-slate-700 dark:text-slate-300">{{ $logs->total() }}</strong> data
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 1</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 2</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Shift 3</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operator</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-medium text-slate-700 dark:text-slate-300">
                                {{ $log->production_date->format('d/m/Y') }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $log->production_date->translatedFormat('l') }}
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-medium text-slate-800 dark:text-white">{{ $log->product->name ?? '-' }}</div>
                            @if($log->product->series)
                            <div class="text-xs text-slate-400 font-mono">{{ $log->product->series }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                         bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $log->product->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-center font-medium text-slate-600 dark:text-slate-400">
                            {{ number_format($log->shift1_qty) }}
                        </td>
                        <td class="px-5 py-4 text-center font-medium text-slate-600 dark:text-slate-400">
                            {{ number_format($log->shift2_qty) }}
                        </td>
                        <td class="px-5 py-4 text-center font-medium text-slate-600 dark:text-slate-400">
                            {{ number_format($log->shift3_qty) }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                         bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ number_format($log->total_qty) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-sm text-slate-500 dark:text-slate-400">
                            {{ $log->user->name ?? '-' }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('production.show', $log) }}"
                                   class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                                   title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('production.edit', $log) }}"
                                   class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg transition-colors"
                                   title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('production.destroy', $log) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            data-confirm="Hapus data produksi {{ $log->product->name ?? '' }} tanggal {{ $log->production_date->format('d/m/Y') }}?"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                            title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
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
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
