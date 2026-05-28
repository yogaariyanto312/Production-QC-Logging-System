<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Department;
use App\Models\Note;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionTarget;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today        = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Statistik utama
        $stats = [
            'today_total'    => ProductionLog::whereDate('production_date', $today)->sum('total_qty'),
            'today_entries'  => ProductionLog::whereDate('production_date', $today)->count(),
            'monthly_total'  => ProductionLog::whereMonth('production_date', $currentMonth)
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

        // Top 5 operator hari ini
        $topOperators = ProductionLog::whereDate('production_date', $today)
            ->whereNotNull('operator_name')->where('operator_name', '!=', '')
            ->select('operator_name', DB::raw('SUM(total_qty) as total'))
            ->groupBy('operator_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Defect rate per produk bulan ini (hanya yang ada reject)
        $defectRates = ProductionLog::whereMonth('production_date', $currentMonth)
            ->whereYear('production_date', $currentYear)
            ->where('reject_qty', '>', 0)
            ->select('product_id',
                DB::raw('SUM(total_qty) as total_prod'),
                DB::raw('SUM(reject_qty) as total_reject')
            )
            ->groupBy('product_id')
            ->with('product:id,name')
            ->orderByDesc('total_reject')
            ->limit(7)
            ->get()
            ->map(fn($r) => [
                'name'   => $r->product->name ?? '-',
                'rate'   => round(($r->total_reject / max($r->total_prod + $r->total_reject, 1)) * 100, 1),
                'reject' => (int) $r->total_reject,
            ])
            ->values();

        // Input terbaru — hanya data hari ini
        $recentLogs = ProductionLog::with(['product.category', 'user'])
            ->whereDate('production_date', $today)
            ->orderByDesc('created_at')
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

        // Target vs Aktual hari ini
        $todayTargets = ProductionTarget::with('product')
            ->where('target_date', $today)
            ->get();
        $totalTarget = $todayTargets->sum('target_qty');
        $totalActual = ProductionLog::whereDate('production_date', $today)->sum('total_qty');
        $targetPct   = $totalTarget > 0 ? min(round(($totalActual / $totalTarget) * 100), 100) : null;

        // Reject stats hari ini
        $rejectStats    = ProductionLog::whereDate('production_date', $today)
            ->selectRaw('SUM(reject_qty) as total_reject, SUM(total_qty) as total_prod')
            ->first();
        $todayReject    = (int) ($rejectStats->total_reject ?? 0);
        $todayRejectPct = ($rejectStats->total_prod + $todayReject) > 0
            ? round(($todayReject / ($rejectStats->total_prod + $todayReject)) * 100, 1)
            : 0;

        // Kategori untuk widget admin
        $categories = Category::withCount('products')->orderBy('name')->get();

        // Departemen untuk widget admin
        $departments = Department::withCount(['operators' => fn($q) => $q->where('role', 'operator')])
            ->orderBy('name')
            ->get();

        return view('dashboard.index', compact(
            'stats', 'chartData', 'productChart', 'recentLogs', 'recentActivities',
            'categories', 'departments', 'recentNotes',
            'todayTargets', 'totalTarget', 'totalActual', 'targetPct',
            'todayReject', 'todayRejectPct',
            'topOperators', 'defectRates'
        ));
    }

    public function liveStats()
    {
        $today        = now()->toDateString();
        $currentMonth = now()->month;
        $currentYear  = now()->year;

        $todayTotal   = (float) ProductionLog::whereDate('production_date', $today)->sum('total_qty');
        $todayEntries = ProductionLog::whereDate('production_date', $today)->count();
        $monthlyTotal = (float) ProductionLog::whereMonth('production_date', $currentMonth)
                            ->whereYear('production_date', $currentYear)->sum('total_qty');

        $rejectStats    = ProductionLog::whereDate('production_date', $today)
            ->selectRaw('SUM(reject_qty) as total_reject, SUM(total_qty) as total_prod')
            ->first();
        $todayReject    = (int) ($rejectStats->total_reject ?? 0);
        $todayRejectPct = ($rejectStats->total_prod + $todayReject) > 0
            ? round(($todayReject / ($rejectStats->total_prod + $todayReject)) * 100, 1)
            : 0;

        $totalTarget = ProductionTarget::where('target_date', $today)->sum('target_qty');
        $totalActual = $todayTotal;
        $targetPct   = $totalTarget > 0 ? min(round(($totalActual / $totalTarget) * 100), 100) : null;

        $topOperators = ProductionLog::whereDate('production_date', $today)
            ->whereNotNull('operator_name')->where('operator_name', '!=', '')
            ->select('operator_name', DB::raw('SUM(total_qty) as total'))
            ->groupBy('operator_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => ['name' => $r->operator_name, 'total' => (float) $r->total]);

        return response()->json([
            'today_total'    => $todayTotal,
            'today_entries'  => $todayEntries,
            'monthly_total'  => $monthlyTotal,
            'today_reject'   => $todayReject,
            'reject_pct'     => $todayRejectPct,
            'target_pct'     => $targetPct,
            'total_target'   => (int) $totalTarget,
            'total_actual'   => $totalActual,
            'top_operators'  => $topOperators,
            'updated_at'     => now()->timezone('Asia/Jakarta')->format('H:i:s'),
        ]);
    }
}
