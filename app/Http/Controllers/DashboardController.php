<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Note;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ActivityLog;
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

        // Data grafik 7 hari terakhir (combo: total unit + entri + rata-rata)
        $rawChart = ProductionLog::select(
                DB::raw('DATE(production_date) as date'),
                DB::raw('SUM(total_qty) as total'),
                DB::raw('COUNT(*) as entries')
            )
            ->where('production_date', '>=', now()->subDays(6)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartData = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d       = now()->subDays($i)->toDateString();
            $row     = $rawChart->get($d);
            $total   = $row ? (int) $row->total   : 0;
            $entries = $row ? (int) $row->entries : 0;
            $chartData->push([
                'date'    => \Carbon\Carbon::parse($d)->locale('id')->isoFormat('dddd, D/M'),
                'total'   => $total,
                'entries' => $entries,
                'avg'     => $entries > 0 ? round($total / $entries, 1) : 0,
            ]);
        }

        // Data grafik produk bulan ini — dikelompokkan per tipe (COVER / CHANNEL / TANGKI)
        $productChart = ProductionLog::select('product_id', DB::raw('SUM(total_qty) as total'))
            ->whereMonth('production_date', $currentMonth)
            ->whereYear('production_date', $currentYear)
            ->groupBy('product_id')
            ->with('product:id,name')
            ->get()
            ->groupBy(fn($item) => strtoupper(explode(' ', trim($item->product->name ?? 'Unknown'))[0]))
            ->map(fn($items, $key) => [
                'name'  => $key,
                'total' => $items->sum('total'),
            ])
            ->sortByDesc('total')
            ->values();

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

        // Catatan untuk widget dashboard
        $userId = auth()->id();
        $recentNotes = Note::with(['user:id,name', 'targetUser:id,name'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('target_user_id', $userId);
            })
            ->orderByRaw('is_done ASC, CASE WHEN due_date IS NULL THEN 1 ELSE 0 END ASC, due_date ASC, created_at DESC')
            ->limit(5)
            ->get();

        // Kategori untuk widget admin
        $categories = Category::withCount('products')->orderBy('name')->get();

        // Departemen untuk widget admin
        $departments = Department::withCount(['operators' => fn($q) => $q->where('role', 'operator')])
            ->orderBy('name')
            ->get();

        return view('dashboard.index', compact(
            'stats', 'chartData', 'productChart', 'recentLogs', 'recentActivities',
            'categories', 'departments', 'recentNotes'
        ));
    }
}
