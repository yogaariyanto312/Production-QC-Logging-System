<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionTarget;
use App\Models\SchedulePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductionTargetController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?: today()->toDateString();

        $targets = ProductionTarget::with(['product', 'creator'])
            ->where('target_date', $date)
            ->orderBy('product_id')
            ->get();

        // Hitung aktual per produk untuk tanggal ini
        $actuals = ProductionLog::whereDate('production_date', $date)
            ->selectRaw('product_id, SUM(total_qty) as actual_qty, SUM(reject_qty) as total_reject')
            ->groupBy('product_id')
            ->pluck('actual_qty', 'product_id');

        $totalTarget = $targets->sum('target_qty');
        $totalActual = ProductionLog::whereDate('production_date', $date)->sum('total_qty');

        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->orderByRaw('CAST(kva AS UNSIGNED)')
            ->orderBy('series')
            ->get();
        $schedulePhoto = SchedulePhoto::with('uploader')->where('target_date', $date)->first();

        return view('production.targets.index', compact(
            'targets', 'actuals', 'date', 'products',
            'totalTarget', 'totalActual', 'schedulePhoto'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id'    => ['required', 'exists:products,id'],
            'target_date'   => ['required', 'date'],
            'target_qty'    => ['required', 'integer', 'min:1', 'max:999999'],
            'notes'         => ['nullable', 'string', 'max:200'],
            'manual_series' => ['nullable', 'string', 'max:100'],
            'manual_kva'    => ['nullable', 'string', 'max:50'],
        ]);

        $productId = $request->product_id;
        $product   = Product::with('category')->find($productId);
        $isManual  = $product && $product->category && $product->category->has_manual_serial;

        if ($isManual && $request->filled('manual_series')) {
            preg_match('/^(\d{2})/', $request->manual_series, $ym);
            $tahun = isset($ym[1]) ? (2000 + (int)$ym[1]) : now()->year;

            $product = Product::firstOrCreate(
                ['category_id' => $product->category_id, 'series' => $request->manual_series, 'kva' => $request->manual_kva ?: null],
                ['name' => $product->name, 'type' => $product->type, 'tahun' => $tahun, 'is_active' => true]
            );
            $productId = $product->id;
        }

        $target = ProductionTarget::updateOrCreate(
            ['product_id' => $productId, 'target_date' => $request->target_date],
            ['target_qty' => $request->target_qty, 'notes' => $request->notes, 'created_by' => auth()->id()]
        );

        ActivityLog::record('create', "Set target produksi: {$product->name} = {$request->target_qty} unit pada {$request->target_date}", $target);

        return back()->with('success', "Target untuk {$product->name} berhasil disimpan.");
    }

    public function liveData(Request $request)
    {
        $date    = $request->date ?: today()->toDateString();
        $targets = ProductionTarget::where('target_date', $date)->get();

        $actuals = ProductionLog::whereDate('production_date', $date)
            ->selectRaw('product_id, SUM(total_qty) as actual_qty')
            ->groupBy('product_id')
            ->pluck('actual_qty', 'product_id');

        $totalTarget = $targets->sum('target_qty');
        $totalActual = (float) ProductionLog::whereDate('production_date', $date)->sum('total_qty');
        $overallPct  = $totalTarget > 0 ? min(round(($totalActual / $totalTarget) * 100), 100) : 0;

        $map = [];
        foreach ($targets as $t) {
            $actual = (float) ($actuals[$t->product_id] ?? 0);
            $pct    = $t->target_qty > 0 ? min(round(($actual / $t->target_qty) * 100), 100) : 0;
            $map[$t->product_id] = [
                'actual'     => $actual,
                'target'     => (int) $t->target_qty,
                'pct'        => $pct,
                'done'       => $actual >= $t->target_qty,
                'remaining'  => max(0, (int) $t->target_qty - (int) $actual),
            ];
        }

        return response()->json([
            'total_actual' => $totalActual,
            'total_target' => (int) $totalTarget,
            'overall_pct'  => $overallPct,
            'actuals'      => $map,
            'updated_at'   => now()->timezone('Asia/Jakarta')->format('H:i:s'),
        ]);
    }

    public function uploadSchedulePhoto(Request $request)
    {
        $request->validate([
            'target_date' => ['required', 'date'],
            'photo'       => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);

        $date  = $request->target_date;
        $old   = SchedulePhoto::where('target_date', $date)->first();
        if ($old) {
            Storage::disk('public')->delete($old->file_path);
        }

        $path = $request->file('photo')->store("schedule-photos/{$date}", 'public');

        SchedulePhoto::updateOrCreate(
            ['target_date' => $date],
            ['file_path' => $path, 'uploaded_by' => auth()->id()]
        );

        return back()->with('success', 'Foto jadwal berhasil diupload.');
    }

    public function deleteSchedulePhoto(Request $request)
    {
        $photo = SchedulePhoto::where('target_date', $request->target_date)->first();
        if ($photo) {
            Storage::disk('public')->delete($photo->file_path);
            $photo->delete();
        }
        return back()->with('success', 'Foto jadwal berhasil dihapus.');
    }

    public function destroy(ProductionTarget $productionTarget)
    {
        $info = "{$productionTarget->product->name} tgl {$productionTarget->target_date->format('d/m/Y')}";
        $productionTarget->delete();
        ActivityLog::record('delete', "Hapus target produksi: {$info}");
        return back()->with('success', 'Target berhasil dihapus.');
    }
}
