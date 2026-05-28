@extends('layouts.app')

@section('title', 'Target Produksi')
@section('page-title', 'Target Produksi')
@section('page-subtitle', 'Set & pantau target produksi harian per produk')

@section('content')
<div class="space-y-5">

    {{-- Flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-700 dark:text-green-400">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Kolom kiri: Form tambah target ── --}}
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden sticky top-4">
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-5 py-4">
                    <h3 class="text-base font-bold text-white">Set Target</h3>
                    <p class="text-green-100 text-xs mt-0.5">Target produksi per produk per hari</p>
                </div>
                <form method="POST" action="{{ route('production.targets.store') }}" class="p-5 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal</label>
                        <input type="date" name="target_date" value="{{ old('target_date', $date) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                                      bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      focus:outline-none focus:ring-2 focus:ring-green-500">
                        @error('target_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Produk</label>
                        <select name="product_id"
                                class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                                       bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                       focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products->groupBy('name') as $groupName => $groupItems)
                            <optgroup label="{{ $groupName }}">
                                @foreach($groupItems as $product)
                                <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->series_with_kva ?: $product->name }}
                                </option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                        @error('product_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Target Unit</label>
                        <input type="number" name="target_qty" min="1" max="999999"
                               value="{{ old('target_qty') }}"
                               placeholder="Contoh: 500"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                                      bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      focus:outline-none focus:ring-2 focus:ring-green-500 text-center font-bold text-lg">
                        @error('target_qty')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input type="text" name="notes" maxlength="200" value="{{ old('notes') }}"
                               placeholder="Misal: Target khusus akhir bulan"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                                      bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors">
                        Simpan Target
                    </button>
                </form>
            </div>
        </div>

        {{-- ── Kolom kanan: Tabel target & progres ── --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Foto Jadwal --}}
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800 dark:text-white">Foto Jadwal</h3>
                        <p class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</p>
                    </div>
                    @if($schedulePhoto)
                    <div class="flex items-center gap-3">
                        {{-- Ganti --}}
                        <form method="POST" action="{{ route('production.targets.schedule-photo.store') }}"
                              enctype="multipart/form-data" id="replacePhotoForm">
                            @csrf
                            <input type="hidden" name="target_date" value="{{ $date }}">
                            <label class="flex items-center gap-1.5 text-xs text-blue-500 hover:text-blue-700 cursor-pointer font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Ganti
                                <input type="file" name="photo" accept="image/*,.pdf" class="hidden"
                                       onchange="document.getElementById('replacePhotoForm').submit()">
                            </label>
                        </form>
                        {{-- Hapus --}}
                        <form method="POST" action="{{ route('production.targets.schedule-photo.destroy') }}">
                            @csrf @method('DELETE')
                            <input type="hidden" name="target_date" value="{{ $date }}">
                            <button type="submit"
                                    data-confirm="Hapus foto jadwal tanggal ini?"
                                    class="flex items-center gap-1.5 text-xs text-red-400 hover:text-red-600 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                @if($schedulePhoto)
                @php $isPdf = strtolower(pathinfo($schedulePhoto->file_path, PATHINFO_EXTENSION)) === 'pdf'; @endphp
                <div class="p-3">
                    @if($isPdf)
                    {{-- PDF embed --}}
                    <iframe src="{{ route('storage.file', $schedulePhoto->file_path) }}"
                            class="w-full border-0 rounded-xl"
                            style="height: 60vh;"
                            title="Jadwal {{ $date }}">
                        <div class="p-6 text-center">
                            <p class="text-sm text-slate-500 mb-3">Browser tidak mendukung preview PDF.</p>
                            <a href="{{ route('storage.file', $schedulePhoto->file_path) }}" target="_blank"
                               class="px-4 py-2 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700">
                                Buka PDF
                            </a>
                        </div>
                    </iframe>
                    @else
                    {{-- Image preview --}}
                    <a href="{{ route('storage.file', $schedulePhoto->file_path) }}" target="_blank"
                       class="block group relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900">
                        <img src="{{ route('storage.file', $schedulePhoto->file_path) }}"
                             alt="Jadwal {{ $date }}"
                             class="w-full object-contain max-h-64 rounded-xl group-hover:opacity-90 transition-opacity cursor-zoom-in">
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <span class="bg-black/50 text-white text-xs px-3 py-1.5 rounded-full font-medium">Buka penuh</span>
                        </div>
                    </a>
                    @endif
                    <p class="text-[10px] text-slate-400 text-center mt-2">
                        Diupload oleh {{ $schedulePhoto->uploader->name ?? '-' }} · {{ $schedulePhoto->updated_at->diffForHumans() }}
                    </p>
                </div>
                @else
                {{-- Upload area --}}
                <div class="p-4">
                    <form method="POST" action="{{ route('production.targets.schedule-photo.store') }}"
                          enctype="multipart/form-data" id="uploadPhotoForm">
                        @csrf
                        <input type="hidden" name="target_date" value="{{ $date }}">
                        <label for="photoInput"
                               class="flex flex-col items-center justify-center gap-2 py-6 px-4
                                      border-2 border-dashed border-slate-200 dark:border-slate-600
                                      rounded-xl cursor-pointer transition-all
                                      hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/10">
                            <div class="w-10 h-10 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Upload jadwal</p>
                                <p class="text-xs text-slate-400 mt-0.5">JPG, PNG, WebP, PDF — maks 8 MB</p>
                            </div>
                            <input type="file" id="photoInput" name="photo" accept="image/*,.pdf" class="hidden"
                                   onchange="document.getElementById('uploadPhotoForm').submit()">
                        </label>
                    </form>
                    @error('photo')<p class="mt-2 text-xs text-red-500 text-center">{{ $message }}</p>@enderror
                </div>
                @endif
            </div>

            {{-- Filter tanggal --}}
            <form method="GET" class="flex items-center gap-3">
                <input type="date" name="date" value="{{ $date }}"
                       class="px-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                              bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    Lihat
                </button>
                <a href="?date={{ today()->toDateString() }}"
                   class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors hover:bg-slate-200">
                    Hari Ini
                </a>
            </form>

            {{-- Summary card --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-100 dark:border-slate-700 text-center">
                    <p class="text-2xl font-black text-slate-800 dark:text-white">{{ number_format($totalTarget) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Target</p>
                </div>
                <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-100 dark:border-slate-700 text-center">
                    <p id="tgt-stat-actual" class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ number_format($totalActual) }}</p>
                    <p class="text-xs text-slate-500 mt-1">Total Aktual</p>
                </div>
                <div class="rounded-xl p-4 text-center
                    {{ $totalTarget > 0 && $totalActual >= $totalTarget ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800' }}">
                    @php $overallPct = $totalTarget > 0 ? min(round(($totalActual / $totalTarget) * 100), 100) : 0; @endphp
                    <p id="tgt-stat-pct" class="text-2xl font-black {{ $overallPct >= 100 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $overallPct }}%
                    </p>
                    <p class="text-xs text-slate-500 mt-1">Pencapaian</p>
                </div>
            </div>

            {{-- Tabel target --}}
            @if($targets->isEmpty())
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700 p-12 text-center">
                <p class="text-slate-400 text-sm">Belum ada target untuk tanggal ini.</p>
                <p class="text-slate-300 dark:text-slate-600 text-xs mt-1">Set target menggunakan form di sebelah kiri.</p>
            </div>
            @else
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($targets as $target)
                    @php
                        $actual  = $actuals[$target->product_id] ?? 0;
                        $pct     = $target->target_qty > 0 ? min(round(($actual / $target->target_qty) * 100), 100) : 0;
                        $done    = $actual >= $target->target_qty;
                        $barColor = $done ? 'bg-green-500' : ($pct >= 70 ? 'bg-amber-400' : 'bg-blue-500');
                    @endphp
                    <div class="px-5 py-4" data-pid="{{ $target->product_id }}">
                        <div class="flex items-start justify-between gap-3 mb-2">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $target->product->name ?? '-' }}</p>
                                @if($target->product?->series_with_kva)
                                <p class="text-xs text-slate-400 font-mono">{{ $target->product->series_with_kva }}</p>
                                @endif
                                @if($target->notes)
                                <p class="text-xs text-slate-400 italic mt-0.5">{{ $target->notes }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="text-right">
                                    <p id="tgt-actual-{{ $target->product_id }}"
                                       class="text-sm font-bold {{ $done ? 'text-green-600 dark:text-green-400' : 'text-slate-800 dark:text-white' }}">
                                        {{ number_format($actual) }} / {{ number_format($target->target_qty) }}
                                    </p>
                                    <p class="text-xs text-slate-400">aktual / target</p>
                                </div>
                                <span id="tgt-pct-{{ $target->product_id }}"
                                      class="text-sm font-black {{ $done ? 'text-green-600' : ($pct >= 70 ? 'text-amber-500' : 'text-blue-600') }} dark:opacity-90 w-12 text-right">
                                    {{ $pct }}%
                                </span>
                                <form method="POST" action="{{ route('production.targets.destroy', $target) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            data-confirm="Hapus target {{ $target->product->name }}?"
                                            class="p-1 text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        {{-- Progress bar --}}
                        <div class="h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                            <div id="tgt-bar-{{ $target->product_id }}"
                                 class="{{ $barColor }} h-full rounded-full transition-all duration-500"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        @if($done)
                        <p id="tgt-label-{{ $target->product_id }}" class="text-[10px] text-green-600 dark:text-green-400 mt-1 font-semibold">✓ Target tercapai!</p>
                        @else
                        <p id="tgt-label-{{ $target->product_id }}" class="text-[10px] text-slate-400 mt-1">Kurang {{ number_format($target->target_qty - $actual) }} unit lagi</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Auto Refresh Toggle --}}
<div id="tgtRefreshBtn"
     class="fixed bottom-6 right-6 z-50 hidden sm:flex items-center gap-2 px-3 py-2 rounded-full shadow-lg cursor-pointer select-none
            bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:shadow-xl transition-all"
     title="Toggle auto-refresh">
    <span id="tgtDot" class="w-2 h-2 rounded-full bg-green-500 pulse-dot shrink-0"></span>
    <span id="tgtLabel" class="text-xs font-semibold text-slate-700 dark:text-slate-200">Live</span>
    <span id="tgtCountdown" class="text-xs text-slate-400">30s</span>
    <span id="tgtUpdatedAt" class="hidden text-[10px] text-slate-400 sm:inline"></span>
</div>

@endsection

@push('scripts')
<script>
const TGT_DATE     = '{{ $date }}';
const TGT_INTERVAL = 30;
let tgtEnabled  = localStorage.getItem('tgtAutoRefresh') !== 'false';
let tgtTimer    = null;
let tgtCdTimer  = null;
let tgtCdVal    = TGT_INTERVAL;

function tgtBarColor(pct) {
    return pct >= 100 ? '#22c55e' : pct >= 70 ? '#f59e0b' : '#3b82f6';
}
function tgtPctClass(pct) {
    return pct >= 100 ? 'text-green-600' : pct >= 70 ? 'text-amber-500' : 'text-blue-600';
}
function fmtNum(n) {
    return Math.round(n).toLocaleString('id-ID');
}

async function fetchTargetLive() {
    try {
        const r = await fetch(`{{ route('api.targets.live') }}?date=${TGT_DATE}`);
        if (!r.ok) return;
        const d = await r.json();

        // Summary cards
        const sa = document.getElementById('tgt-stat-actual');
        if (sa) sa.textContent = fmtNum(d.total_actual);

        const sp = document.getElementById('tgt-stat-pct');
        if (sp) {
            sp.textContent = d.overall_pct + '%';
            sp.className = sp.className.replace(/text-(green|amber|slate)-\d+/g, '');
            sp.classList.add(d.overall_pct >= 100 ? 'text-green-600' : 'text-amber-600', 'dark:opacity-90');
        }

        // Per-product rows
        for (const [pid, info] of Object.entries(d.actuals)) {
            const actualEl = document.getElementById(`tgt-actual-${pid}`);
            if (actualEl) {
                actualEl.textContent = `${fmtNum(info.actual)} / ${fmtNum(info.target)}`;
                actualEl.className = actualEl.className.replace(/text-(green|slate)-\d+/g, '').trim();
                actualEl.classList.add(info.done ? 'text-green-600' : 'text-slate-800');
            }

            const pctEl = document.getElementById(`tgt-pct-${pid}`);
            if (pctEl) {
                pctEl.textContent = info.pct + '%';
                pctEl.className = pctEl.className.replace(/text-(green|amber|blue)-\d+/g, '').trim();
                pctEl.classList.add(tgtPctClass(info.pct));
            }

            const barEl = document.getElementById(`tgt-bar-${pid}`);
            if (barEl) {
                barEl.style.width = info.pct + '%';
                barEl.style.backgroundColor = tgtBarColor(info.pct);
            }

            const labelEl = document.getElementById(`tgt-label-${pid}`);
            if (labelEl) {
                if (info.done) {
                    labelEl.textContent = '✓ Target tercapai!';
                    labelEl.className = 'text-[10px] text-green-600 dark:text-green-400 mt-1 font-semibold';
                } else {
                    labelEl.textContent = `Kurang ${fmtNum(info.remaining)} unit lagi`;
                    labelEl.className = 'text-[10px] text-slate-400 mt-1';
                }
            }
        }

        const ua = document.getElementById('tgtUpdatedAt');
        if (ua) { ua.textContent = ' · ' + d.updated_at; ua.classList.remove('hidden'); }
    } catch {}
}

function tgtStart() {
    tgtCdVal = TGT_INTERVAL;
    fetchTargetLive();
    tgtTimer   = setInterval(fetchTargetLive, TGT_INTERVAL * 1000);
    tgtCdTimer = setInterval(() => {
        tgtCdVal--;
        if (tgtCdVal <= 0) tgtCdVal = TGT_INTERVAL;
        const el = document.getElementById('tgtCountdown');
        if (el) el.textContent = tgtCdVal + 's';
    }, 1000);
    tgtSetUI(true);
}

function tgtStop() {
    clearInterval(tgtTimer); clearInterval(tgtCdTimer);
    tgtTimer = null; tgtCdTimer = null;
    tgtSetUI(false);
}

function tgtSetUI(on) {
    const dot   = document.getElementById('tgtDot');
    const label = document.getElementById('tgtLabel');
    const count = document.getElementById('tgtCountdown');
    if (dot)   { dot.classList.toggle('bg-green-500', on); dot.classList.toggle('pulse-dot', on); dot.classList.toggle('bg-slate-400', !on); }
    if (label) label.textContent = on ? 'Live' : 'Paused';
    if (count) { count.textContent = TGT_INTERVAL + 's'; count.classList.toggle('hidden', !on); }
}

document.getElementById('tgtRefreshBtn')?.addEventListener('click', () => {
    tgtEnabled = !tgtEnabled;
    localStorage.setItem('tgtAutoRefresh', tgtEnabled);
    tgtEnabled ? tgtStart() : tgtStop();
});

tgtEnabled ? tgtStart() : tgtSetUI(false);
</script>
@endpush
