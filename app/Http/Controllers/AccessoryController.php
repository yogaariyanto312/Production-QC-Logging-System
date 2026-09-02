<?php

namespace App\Http\Controllers;

use App\Exports\AccessoryExport;
use App\Models\Accessory;
use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;

class AccessoryController extends Controller
{
    public function index(Request $request)
    {
        [$query, $departments, $deptFilter] = $this->filteredQuery($request);

        $accessories = (clone $query)
            ->with(['product', 'user'])
            ->orderByDesc('accessory_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $allProducts = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->orderByRaw('CAST(kva AS UNSIGNED)')
            ->orderBy('series')
            ->get();

        // Kategori "seri manual" (Channel/Cover/Tangki Swasta & Typetest) menyimpan
        // seri yang sama sebagai baris Product terpisah per kategori — untuk dropdown
        // aksesoris ini membingungkan (seri 2051 muncul 3x). Gabungkan jadi satu per
        // kombinasi seri+KVA, lepas dari kategori asalnya.
        $manualSeries = $allProducts
            ->filter(fn ($p) => ($p->category?->has_manual_serial ?? false) && $p->series)
            ->unique(fn ($p) => $p->series . '|' . $p->kva)
            ->sortBy(fn ($p) => [(int) $p->kva, $p->series])
            ->values();

        $regularProducts = $allProducts
            ->filter(fn ($p) => !($p->category?->has_manual_serial ?? false));

        $accessoryNames = Accessory::query()
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        // Nomor urut terakhir per kombinasi jenis aksesoris + seri produk, untuk
        // saran otomatis nomor urut berikutnya (last + qty) saat menambah entri
        // baru. Rentetan nomor tiap seri berdiri sendiri: BOX seri 2051 yang sudah
        // sampai NO.15 tidak ikut menyeret BOX seri 2052 yang baru mulai. Kunci
        // memakai seri (bukan product_id) agar seri yang sama dari kategori berbeda
        // tetap satu rentetan; aksesoris lepas (tanpa seri) memakai kunci "nama|".
        $lastSerials = Accessory::query()
            ->select('name', 'product_id', 'serial_number', 'accessory_date')
            ->with('product:id,series')
            ->whereNotNull('serial_number')
            ->where('serial_number', '!=', '')
            ->orderByDesc('accessory_date')
            ->orderByDesc('id')
            ->get()
            ->unique(fn ($a) => $a->name . '|' . ($a->product?->series ?? ''))
            ->mapWithKeys(function ($a) {
                preg_match('/(\d+)\s*$/', $a->serial_number, $m);
                $series = $a->product?->series ?? '';

                return [$a->name . '|' . $series => [
                    'serial_number' => $a->serial_number,
                    'date'          => $a->accessory_date->translatedFormat('d M Y'),
                    'series'        => $series,
                    'last_number'   => isset($m[1]) ? (int) $m[1] : null,
                ]];
            })
            ->filter(fn ($v) => $v['last_number'] !== null);

        // Peta id produk → seri, supaya JS bisa menyusun kunci $lastSerials dari
        // pilihan dropdown "Seri Produk Terkait".
        $productSeries = $allProducts->mapWithKeys(fn ($p) => [$p->id => (string) $p->series]);

        $years = Accessory::selectRaw('DISTINCT YEAR(accessory_date) as yr')
            ->orderByDesc('yr')->pluck('yr');

        return view('accessories.index', compact(
            'accessories', 'regularProducts', 'manualSeries', 'accessoryNames', 'lastSerials', 'years',
            'productSeries', 'departments', 'deptFilter'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['user_id']       = auth()->id();
        $data['operator_name'] = auth()->user()->name;

        $accessory = Accessory::create($data);

        ActivityLog::record(
            'create',
            "Input aksesoris keluar: {$accessory->name} ({$accessory->qty} {$accessory->unit})",
            $accessory
        );

        return redirect()->route('accessories.index')
            ->with('success', "Data aksesoris berhasil disimpan. ({$accessory->qty} {$accessory->unit})");
    }

    public function update(Request $request, Accessory $accessory)
    {
        abort_unless(
            \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.edit'),
            403
        );

        $accessory->update($this->validated($request));

        ActivityLog::record(
            'update',
            "Ubah aksesoris keluar: {$accessory->name} ({$accessory->qty} {$accessory->unit})",
            $accessory
        );

        return redirect()->route('accessories.index')
            ->with('success', "Data aksesoris '{$accessory->name}' berhasil diperbarui.");
    }

    public function destroy(Accessory $accessory)
    {
        abort_unless(
            \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.delete'),
            403
        );

        $info = $accessory->name;
        $accessory->delete();
        ActivityLog::record('delete', "Hapus aksesoris keluar: {$info}");

        return redirect()->route('accessories.index')
            ->with('success', 'Data aksesoris berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        abort_unless(
            \App\Support\MenuAccess::can(auth()->user(), 'aksesoris.export'),
            403
        );

        [$query] = $this->filteredQuery($request);

        $accessories = $query->with(['product', 'user'])
            ->orderByDesc('accessory_date')
            ->orderByDesc('created_at')
            ->get();

        return ExcelFacade::download(
            new AccessoryExport($accessories),
            'aksesoris-keluar-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    private function validated(Request $request): array
    {
        // Satuan lama sempat tersimpan dengan kapitalisasi berbeda ("Unit");
        // samakan dulu agar lolos Rule::in dan tersimpan konsisten.
        if ($request->filled('unit')) {
            $request->merge(['unit' => strtolower(trim((string) $request->input('unit')))]);
        }

        return $request->validate([
            'product_id'     => ['nullable', 'exists:products,id'],
            'accessory_date' => ['required', 'date'],
            'name'           => ['required', 'string', 'max:150'],
            'serial_number'  => ['nullable', 'string', 'max:150'],
            'qty'            => ['required', 'integer', 'min:1'],
            'unit'           => ['nullable', Rule::in(Accessory::UNITS)],
            'recipient'      => ['nullable', 'string', 'max:150'],
            'keterangan'     => ['nullable', 'string', 'max:1000'],
        ]);
    }

    /** @return array{0:\Illuminate\Database\Eloquent\Builder,1:\Illuminate\Support\Collection,2:?string} */
    private function filteredQuery(Request $request): array
    {
        $query = Accessory::query();

        if ($search = trim((string) $request->input('search'))) {
            $query->search($search);
        }

        if ($month = $request->input('month')) {
            $query->whereMonth('accessory_date', (int) $month);
        }

        if ($year = $request->input('year')) {
            $query->whereYear('accessory_date', (int) $year);
        }

        [$departments, $deptFilter] = $this->departmentFilter($request, $query, 'accessories.department');

        return [$query, $departments, $deptFilter];
    }
}
