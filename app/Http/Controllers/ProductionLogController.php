<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionLogRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Services\BotNotificationService;
use Illuminate\Http\Request;

class ProductionLogController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ?: null;
        $dateTo   = $request->date_to   ?: null;

        $applyFilters = function ($q) use ($request, $dateFrom, $dateTo) {
            $q->when($request->search,       fn($q) => $q->search($request->search))
              ->when($request->product_name, fn($q) => $q->whereHas('product', fn($q) => $q->where('name', $request->product_name)))
              ->when($dateFrom,              fn($q) => $q->where('production_date', '>=', $dateFrom))
              ->when($dateTo,                fn($q) => $q->where('production_date', '<=', $dateTo))
              ->when($request->month,        fn($q) => $q->whereMonth('production_date', $request->month))
              ->when($request->year,         fn($q) => $q->whereYear('production_date', $request->year))
;
        };

        // Totals khusus hari ini untuk summary bar
        $todayLogs  = ProductionLog::with(['product:id,category_id,type', 'product.category:id,name'])
            ->where('production_date', today()->toDateString())
            ->get(['id', 'product_id', 'shift1_qty', 'shift2_qty', 'total_qty']);

        $totalUp    = $todayLogs->sum('shift1_qty');
        $totalBt    = $todayLogs->sum('shift2_qty');
        $totalTanki = $todayLogs->filter(fn($l) => str_contains(strtolower($l->product->category->name ?? ''), 'tangki'))->sum('total_qty');
        $totalCover = $todayLogs->filter(fn($l) => str_contains(strtolower($l->product->category->name ?? ''), 'cover'))->sum('total_qty');
        $grandTotal = $todayLogs->sum('total_qty');
        $totalCount = $todayLogs->count();

        // Paginate by date (7 hari per halaman) agar satu tanggal tidak terpotong
        $dates = ProductionLog::tap($applyFilters)
            ->selectRaw('DISTINCT production_date')
            ->orderByDesc('production_date')
            ->paginate(16, ['production_date'])
            ->withQueryString();

        $logs = ProductionLog::with(['product.category', 'user'])
            ->tap($applyFilters)
            ->whereIn('production_date', $dates->pluck('production_date'))
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->get();

        $products   = Product::where('is_active', true)->distinct()->orderBy('name')->pluck('name');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $years      = ProductionLog::selectRaw('YEAR(production_date) as yr')
            ->distinct()->orderByDesc('yr')->pluck('yr');

        // Precompute nomor urut terakhir UP/BT — satu query untuk semua channel product
        $channelProductIds = $logs
            ->filter(fn($l) => ($l->product->type ?? '') === 'channel')
            ->pluck('product_id')->unique()->values();

        $lastChannelNums = [];
        if ($channelProductIds->isNotEmpty()) {
            $lastChannelNums = $this->lastChannelSerialsForMany($channelProductIds->all());
        }

        return view('production.index', compact(
            'logs', 'dates', 'products', 'categories', 'years',
            'dateFrom', 'dateTo',
            'totalUp', 'totalBt', 'totalTanki', 'totalCover', 'grandTotal', 'totalCount',
            'lastChannelNums'
        ));
    }

    public function create()
    {
        $products = $this->dropdownProducts();
        return view('production.create', compact('products'));
    }

    public function store(ProductionLogRequest $request)
    {
        $data = $request->validated();
        $data['user_id']       = auth()->id();
        $data['operator_name'] = auth()->user()->name;
        $data['reject_qty']    = (int) ($data['reject_qty'] ?? 0);

        // Backstop double-submit (klik ganda): abaikan kiriman identik dalam 10 detik terakhir.
        // Penting untuk produk channel yang akan di-MERGE (jika tidak, qty bisa berlipat).
        $dupeKey = 'prodlog_dupe_' . md5(implode('|', [
            $data['user_id'], $data['product_id'], $data['production_date'],
            $data['total_qty'] ?? '', $data['shift1_qty'] ?? '', $data['shift2_qty'] ?? '',
            $data['manual_series'] ?? '', $data['notes'] ?? '',
        ]));
        if (\Illuminate\Support\Facades\Cache::get($dupeKey)) {
            return redirect()->route('production.index')
                ->with('success', 'Data produksi berhasil disimpan.');
        }
        \Illuminate\Support\Facades\Cache::put($dupeKey, true, now()->addSeconds(10));

        $product    = Product::with('category')->find($data['product_id']);
        $isChannel  = $product && $product->isChannel();
        $isManual   = $product && $product->category && $product->category->has_manual_serial;

        // Auto find-or-create specific product record for manual series+KVA entries
        if ($isManual && !empty($data['manual_series'])) {
            preg_match('/^(\d{2})/', $data['manual_series'], $ym);
            $tahun = isset($ym[1]) ? (2000 + (int)$ym[1]) : now()->year;

            $specificProduct = Product::firstOrCreate(
                [
                    'category_id' => $product->category_id,
                    'series'      => $data['manual_series'],
                    'kva'         => $data['manual_kva'] ?: null,
                ],
                [
                    'name'      => $product->name,
                    'type'      => $product->type,
                    'tahun'     => $tahun,
                    'is_active' => true,
                ]
            );
            $data['product_id'] = $specificProduct->id;
            $isManual = false; // product_id is now specific; no need for manual_series merge filter
        }

        if ($isChannel) {
            $up = (int) ($data['shift1_qty'] ?? 0);
            $bt = (int) ($data['shift2_qty'] ?? 0);
            $data['shift3_qty'] = 0;
            $data['total_qty']  = ($up + $bt) / 2;
        } else {
            $data['shift1_qty'] = $data['shift1_qty'] ?? 0;
            $data['shift2_qty'] = $data['shift2_qty'] ?? 0;
            $data['shift3_qty'] = $data['shift3_qty'] ?? 0;
        }

        // Cek merge hanya untuk channel: UP dan BT diinput terpisah oleh operator berbeda.
        // Non-channel (Cover, Tangki, dll.) selalu buat entri baru per batch.
        $existing = $isChannel
            ? ProductionLog::where('product_id', $data['product_id'])
                ->where('production_date', $data['production_date'])
                ->first()
            : null;

        if ($existing) {
            // Merge: tambah ke entri yang ada
            $update = [];

            if ($isChannel) {
                $update['shift1_qty'] = $existing->shift1_qty + (int) ($data['shift1_qty'] ?? 0);
                $update['shift2_qty'] = $existing->shift2_qty + (int) ($data['shift2_qty'] ?? 0);
                $update['shift3_qty'] = 0;
                $update['total_qty']  = ($update['shift1_qty'] + $update['shift2_qty']) / 2;
            } else {
                $update['total_qty'] = $existing->total_qty + (float) ($data['total_qty'] ?? 0);
            }

            // Gabung nomor urut (notes)
            if (!empty($data['notes'])) {
                $update['notes'] = $existing->notes
                    ? $existing->notes . "\n" . $data['notes']
                    : $data['notes'];
            }

            // Update keterangan jika ada isian baru
            if (!empty($data['keterangan'])) {
                $update['keterangan'] = $existing->keterangan
                    ? $existing->keterangan . '; ' . $data['keterangan']
                    : $data['keterangan'];
            }

            $existing->update($update);
            $log = $existing->fresh(['product']);

            ActivityLog::record('update', "Tambah produksi: {$log->product->name} (total kini: {$log->total_qty} unit)", $log);
            BotNotificationService::checkAndAlertRejectRate($product, $data['production_date']);
            return redirect()->route('production.index')
                ->with('success', "Ditambahkan ke entri yang ada. Total sekarang: {$log->total_qty} unit.");
        }

        $log = ProductionLog::create($data);
        ActivityLog::record('create', "Input produksi: {$log->product->name} ({$log->total_qty} unit)", $log);
        BotNotificationService::checkAndAlertRejectRate($product, $data['production_date']);

        return redirect()->route('production.index')
            ->with('success', "Data produksi berhasil disimpan. Total: {$log->total_qty} unit.");
    }

    public function show(ProductionLog $productionLog)
    {
        $productionLog->load(['product.category', 'user']);

        $lastChannelSerials = ($productionLog->product->isChannel())
            ? $this->lastChannelSerials($productionLog->product_id)
            : ['up' => null, 'bt' => null];

        return view('production.show', compact('productionLog', 'lastChannelSerials'));
    }

    public function edit(ProductionLog $productionLog)
    {
        $products = $this->dropdownProducts();
        return view('production.edit', compact('productionLog', 'products'));
    }

    private function dropdownProducts()
    {
        return Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->orderByRaw('CAST(kva AS UNSIGNED)')
            ->orderBy('series')
            ->get();
    }

    /**
     * Satu query untuk semua channel products — jauh lebih efisien dari N calls.
     */
    private function lastChannelSerialsForMany(array $productIds): array
    {
        $result = array_fill_keys($productIds, ['up' => null, 'bt' => null]);

        ProductionLog::whereIn('product_id', $productIds)
            ->whereNotNull('notes')->where('notes', '!=', '')
            ->orderByDesc('production_date')->orderByDesc('created_at')
            ->select(['product_id', 'notes'])
            ->each(function ($log) use (&$result) {
                $pid = $log->product_id;
                if (!isset($result[$pid])) return;
                if ($result[$pid]['up'] && $result[$pid]['bt']) return;
                $lines = collect(explode("\n", $log->notes))->map(fn($l) => trim($l))->filter();
                if (!$result[$pid]['up']) {
                    $ul = $lines->first(fn($l) => preg_match('/\bUP\b/i', $l));
                    if ($ul) $result[$pid]['up'] = $ul;
                }
                if (!$result[$pid]['bt']) {
                    $bl = $lines->first(fn($l) => preg_match('/\bBT\b/i', $l));
                    if ($bl) $result[$pid]['bt'] = $bl;
                }
            });

        return $result;
    }

    /** Single-product version — dipakai di show() */
    private function lastChannelSerials(int $productId): array
    {
        return $this->lastChannelSerialsForMany([$productId])[$productId] ?? ['up' => null, 'bt' => null];
    }

    public function update(ProductionLogRequest $request, ProductionLog $productionLog)
    {
        $data = $request->validated();
        $data['reject_qty'] = (int) ($data['reject_qty'] ?? 0);
        $product = Product::find($data['product_id']);
        if ($product && $product->isChannel()) {
            $up = (int) ($data['shift1_qty'] ?? 0);
            $bt = (int) ($data['shift2_qty'] ?? 0);
            $data['shift3_qty'] = 0;
            $data['total_qty']  = ($up + $bt) / 2;
        } else {
            $data['shift1_qty'] = $data['shift1_qty'] ?? 0;
            $data['shift2_qty'] = $data['shift2_qty'] ?? 0;
            $data['shift3_qty'] = $data['shift3_qty'] ?? 0;
        }

        $productionLog->update($data);
        ActivityLog::record('update', "Edit produksi: {$productionLog->product->name} ({$productionLog->total_qty} unit)", $productionLog);
        BotNotificationService::checkAndAlertRejectRate($product, $data['production_date']);
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil diperbarui.');
    }

    /**
     * API: kembalikan nomor urut (notes) terakhir untuk produk tertentu.
     * Digunakan sebagai hint di form input produksi.
     */
    public function lastSerial(Request $request)
    {
        $productId = (int) $request->input('product_id');
        if (!$productId) {
            return response()->json(['notes' => null]);
        }

        $log = ProductionLog::where('product_id', $productId)
            ->whereNotNull('notes')
            ->where('notes', '!=', '')
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->first(['notes', 'production_date']);

        if (!$log) {
            return response()->json(['notes' => null]);
        }

        return response()->json([
            'notes' => $log->notes,
            'date'  => $log->production_date->translatedFormat('d M Y'),
        ]);
    }

    public function destroy(ProductionLog $productionLog)
    {
        $info = "{$productionLog->product->name} tgl {$productionLog->production_date->format('d/m/Y')}";
        $productionLog->delete();
        ActivityLog::record('delete', "Hapus produksi: {$info}");
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil dihapus.');
    }

    public function poll()
    {
        return response()->json([
            'ts'    => ProductionLog::max('updated_at'),
            'count' => ProductionLog::count(),
        ]);
    }
}
