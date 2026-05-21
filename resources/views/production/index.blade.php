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
    @php
        $totalUp      = $logs->sum('shift1_qty');
        $totalBt      = $logs->sum('shift2_qty');
        $totalTanki   = $logs->filter(fn($l) => ($l->product->category->name ?? '') === 'Tanki')->sum('total_qty');
        $totalCover   = $logs->filter(fn($l) => ($l->product->category->name ?? '') === 'Cover')->sum('total_qty');
        $grandTotal   = $logs->sum('total_qty');
    @endphp
    <div class="grid grid-cols-4 gap-4">
        {{-- UP + BT gabung --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4">
            <p class="text-xs text-blue-500 mb-2 font-medium text-center">Total Channel</p>
            <div class="grid grid-cols-2 divide-x divide-blue-200 dark:divide-blue-800">
                <div class="text-center pr-2">
                    <p class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ number_format($totalUp) }}</p>
                    <p class="text-xs text-blue-400 mt-0.5">UP</p>
                </div>
                <div class="text-center pl-2">
                    <p class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ number_format($totalBt) }}</p>
                    <p class="text-xs text-purple-600 mt-0.5">BT</p>
                </div>
            </div>
        </div>
        {{-- Total Tanki --}}
        <div class="bg-slate-800 rounded-xl p-4 text-center">
            <p class="text-xs text-white mb-1 font-medium">Total Tanki</p>
            <p class="text-xl font-bold text-white dark:text-white mt-2">
                {{ fmod((float)$totalTanki, 1) == 0 ? number_format($totalTanki) : number_format($totalTanki, 1) }}
            </p>
            <p class="text-xs text-white mt-1">unit</p>
        </div>
        {{-- Total Cover --}}
        <div class="bg-slate-800 rounded-xl p-4 text-center">
            <p class="text-xs text-white mb-1 font-medium">Total Cover</p>
            <p class="text-xl font-bold text-white dark:text-teal-300 mt-2">
                {{ fmod((float)$totalCover, 1) == 0 ? number_format($totalCover) : number_format($totalCover, 1) }}
            </p>
            <p class="text-xs text-white mt-1">unit</p>
        </div>
        {{-- Grand Total --}}
        <div class="bg-slate-800 rounded-xl p-4 text-center">
            <p class="text-xs text-slate-400 mb-1 font-medium">Grand Total</p>
            <p class="text-xl font-bold text-white mt-2">
                {{ fmod((float)$grandTotal, 1) == 0 ? number_format($grandTotal) : number_format($grandTotal, 1) }}
            </p>
            <p class="text-xs text-slate-500 mt-1">semua kategori</p>
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
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider w-10">No</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Produk</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">UP</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">BT</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Operator</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($logs as $log)
                    @php $isChannel = ($log->product->type ?? 'regular') === 'channel'; @endphp
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-4 text-center text-xs font-medium text-slate-400">
                            {{ $logs->firstItem() + $loop->index }}
                        </td>
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
                            @if($log->product->series_with_kva)
                            <div class="text-xs text-slate-400 font-mono">{{ $log->product->series_with_kva }}</div>
                            @endif
                            @if($log->notes)
                            <div class="text-xs text-slate-400 italic mt-0.5">{{ Str::limit($log->notes, 60) }}</div>
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
                        <td class="px-5 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold
                                         bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                {{ fmod((float)$log->total_qty, 1) == 0 ? number_format($log->total_qty) : number_format($log->total_qty, 1) }}
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

    {{-- ====== CHAT ADMIN (Operator only) ====== --}}
    @if(auth()->user()->role === 'operator')
    <div x-data="prodChatPanel()" class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        {{-- Header / Toggle --}}
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

        {{-- Panel (collapsible) --}}
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-slate-100 dark:border-slate-700">

            {{-- Area pesan --}}
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

            {{-- Input kirim --}}
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
            setInterval(() => this.load(), 15000);
        }
    };
}
</script>
@endpush
