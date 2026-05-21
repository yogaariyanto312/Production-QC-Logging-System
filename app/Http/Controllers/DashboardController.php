<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Statistik utama
        $stats = [
            'today_total'   => ProductionLog::whereDate('production_date', $today)->sum('total_qty'),
            'today_entries' => ProductionLog::whereDate('production_date', $today)->count(),
            'monthly_total' => ProductionLog::whereMonth('production_date', $currentMonth)
                                ->whereYear('production_date', $currentYear)->sum('total_qty'),
            'total_products' => Product::where('is_active', true)->count(),
        ];

        // Data grafik 7 hari terakhir
        $chartData = ProductionLog::select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(total_qty) as total')
            )
            ->where('production_date', '>=', now()->subDays(6)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date'  => \Carbon\Carbon::parse($item->date)->format('d/m'),
                'total' => $item->total,
            ]);

        // Data grafik produk bulan ini (top 5)
        $productChart = ProductionLog::select('product_id', DB::raw('SUM(total_qty) as total'))
            ->whereMonth('production_date', $currentMonth)
            ->whereYear('production_date', $currentYear)
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('product:id,name,series')
            ->get()
            ->map(fn($item) => [
                'name'  => $item->product->name ?? 'Unknown',
                'total' => $item->total,
            ]);

        // Input terbaru
        $recentLogs = ProductionLog::with(['product.category', 'user'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Aktivitas terbaru
        $recentActivities = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'chartData', 'productChart', 'recentLogs', 'recentActivities'
        ));
    }
}
