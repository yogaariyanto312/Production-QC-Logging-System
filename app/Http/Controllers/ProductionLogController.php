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
        $logs = ProductionLog::with(['product.category', 'user'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->product_id, fn($q) => $q->byProduct($request->product_id))
            ->when($request->date_from, fn($q) => $q->where('production_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('production_date', '<=', $request->date_to))
            ->when($request->month, fn($q) => $q->whereMonth('production_date', $request->month))
            ->when($request->year, fn($q) => $q->whereYear('production_date', $request->year))
            ->orderByDesc('production_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $products  = Product::where('is_active', true)->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('production.index', compact('logs', 'products', 'categories'));
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
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Hanya admin yang bisa mengedit data produksi.');
        }
        $products = Product::where('is_active', true)->with('category')->orderBy('name')->get();
        return view('production.edit', compact('productionLog', 'products'));
    }

    public function update(ProductionLogRequest $request, ProductionLog $productionLog)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $productionLog->update($request->validated());
        ActivityLog::record('update', "Edit produksi: {$productionLog->product->name} ({$productionLog->total_qty} unit)", $productionLog);
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil diperbarui.');
    }

    public function destroy(ProductionLog $productionLog)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }
        $info = "{$productionLog->product->name} tgl {$productionLog->production_date->format('d/m/Y')}";
        $productionLog->delete();
        ActivityLog::record('delete', "Hapus produksi: {$info}");
        return redirect()->route('production.index')
            ->with('success', 'Data produksi berhasil dihapus.');
    }
}
