<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductionLogRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductionLog;
use Illuminate\Http\Request;

class ProductionLogController extends Controller
{
    public function index(Request $request)
    {
        // Default ke hari ini jika belum ada filter sama sekali
        $fresh    = !$request->hasAny(['search', 'product_name', 'date_from', 'date_to', 'month', 'year', 'page']);
        $dateFrom = $fresh ? today()->subDay()->toDateString() : ($request->date_from ?: null);
        $dateTo   = $fresh ? today()->toDateString()           : ($request->date_to   ?: null);

        $applyFilters = function ($q) use ($request, $dateFrom, $dateTo) {
            $q->when($request->search,       fn($q) => $q->search($request->search))
              ->when($request->product_name, fn($q) => $q->whereHas('product', fn($q) => $q->where('name', $request->product_name)))
              ->when($dateFrom,              fn($q) => $q->where('production_date', '>=', $dateFrom))
              ->when($dateTo,                fn($q) => $q->where('production_date', '<=', $dateTo))
              ->when($request->month,        fn($q) => $q->whereMonth('production_date', $request->month))
              ->when($request->year,         fn($q) => $q->whereYear('production_date', $request->year));
        };

        // Totals khusus hari ini untuk summary bar
        $todayLogs  = ProductionLog::with('product.category')
            ->where('production_date', today()->toDateString())
            ->get(['id', 'product_id', 'shift1_qty', 'shift2_qty', 'total_qty']);

        $totalUp    = $todayLogs->sum('shift1_qty');
        $totalBt    = $todayLogs->sum('shift2_qty');
        $totalTanki = $todayLogs->filter(fn($l) => str_contains(strtolower($l->product->category->name ?? ''), 'tangki'))->sum('total_qty');
        $totalCover = $todayLogs->filter(fn($l) => str_contains(strtolower($l->product->category->name ?? ''), 'cover'))->sum('total_qty');
        $grandTotal = $todayLogs->sum('total_qty');
        $totalCount = $todayLogs->count();

        // Paginated untuk tabel
        $logs = ProductionLog::with(['product.category', 'user'])
            ->tap($applyFilters)
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $products   = Product::where('is_active', true)->distinct()->orderBy('name')->pluck('name');
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('production.index', compact(
            'logs', 'products', 'categories',
            'dateFrom', 'dateTo',
            'totalUp', 'totalBt', 'totalTanki', 'totalCover', 'grandTotal', 'totalCount'
        ));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->with('category')->orderBy('name')->get();
        return view('production.create', compact('products'));
    }

    public function store(ProductionLogRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

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

        $log = ProductionLog::create($data);
        ActivityLog::record('create', "Input produksi: {$log->product->name} ({$log->total_qty} unit)", $log);

        return redirect()->route('production.index')
            ->with('success', "Data produksi berhasil disimpan. Total: {$log->total_qty} unit.");
    }

    public function show(ProductionLog $productionLog)
    {
        $productionLog->load(['product.category', 'user']);
        return view('production.show', compact('productionLog'));
    }

    public function edit(ProductionLog $productionLog)
    {
        // Hanya admin yang bisa edit
        if (!auth()->user()->isPrivileged()) {
            abort(403, 'Hanya admin yang bisa mengedit data produksi.');
        }
        $products = Product::where('is_active', true)->with('category')->orderBy('name')->get();
        return view('production.edit', compact('productionLog', 'products'));
    }

    public function update(ProductionLogRequest $request, ProductionLog $productionLog)
    {
        if (!auth()->user()->isPrivileged()) {
            abort(403);
        }

        $data    = $request->validated();
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
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil diperbarui.');
    }

    public function destroy(ProductionLog $productionLog)
    {
        if (!auth()->user()->isPrivileged()) {
            abort(403);
        }
        $info = "{$productionLog->product->name} tgl {$productionLog->production_date->format('d/m/Y')}";
        $productionLog->delete();
        ActivityLog::record('delete', "Hapus produksi: {$info}");
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil dihapus.');
    }
}
