<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\GambarKerja;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GambarKerjaController extends Controller
{
    // Daftar produk yang punya gambar kerja (+ produk yang belum punya, untuk admin)
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $products = Product::with(['gambarKerja' => fn($q) => $q->orderBy('urutan')])
            ->withCount('gambarKerja')
            ->where('is_active', true)
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('series', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->has_gambar, fn($q) => $q->has('gambarKerja'))
            ->orderBy('name')
            ->paginate(16)
            ->withQueryString();

        return view('gambar-kerja.index', compact('products', 'categories'));
    }

    // Halaman semua gambar kerja milik satu produk, berurutan
    public function byProduct(Product $product)
    {
        $gambarKerja = $product->gambarKerja()
            ->with('uploader')
            ->orderBy('urutan')
            ->get();

        return view('gambar-kerja.by-product', compact('product', 'gambarKerja'));
    }

    public function create(Request $request)
    {
        $products       = Product::where('is_active', true)->orderBy('name')->get();
        $selectedProduct = $request->product_id ? Product::find($request->product_id) : null;
        return view('gambar-kerja.create', compact('products', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'files'      => ['required', 'array', 'min:1'],
            'files.*'    => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:102400'],
            'keterangan' => ['nullable', 'string', 'max:300'],
        ], [
            'product_id.required' => 'Produk wajib dipilih.',
            'files.required'      => 'File gambar kerja wajib diupload.',
            'files.*.mimes'       => 'Format file harus JPG, PNG, atau PDF.',
            'files.*.max'         => 'Ukuran setiap file maksimal 100MB.',
        ]);

        // Nomor urutan lanjutan dari yang sudah ada
        $nextUrutan = GambarKerja::where('product_id', $request->product_id)->max('urutan') + 1;

        foreach ($request->file('files') as $index => $file) {
            $ext      = $file->getClientOriginalExtension();
            $fileType = in_array(strtolower($ext), ['jpg', 'jpeg', 'png']) ? 'image' : 'pdf';
            $filePath = $file->storeAs('gambar-kerja', Str::uuid() . '.' . $ext, 'public');
            $urutan   = $nextUrutan + $index;

            $gambar = GambarKerja::create([
                'product_id'  => $request->product_id,
                'judul'       => 'Gambar ' . $urutan,
                'file_path'   => $filePath,
                'file_type'   => $fileType,
                'keterangan'  => $request->keterangan,
                'uploaded_by' => auth()->id(),
                'urutan'      => $urutan,
            ]);

            ActivityLog::record('create', "Upload gambar kerja: {$gambar->judul} - {$gambar->product->name}", $gambar);
        }

        $total = count($request->file('files'));
        return redirect()->route('gambar-kerja.by-product', $request->product_id)
            ->with('success', "{$total} gambar kerja berhasil diupload.");
    }

    public function destroyByProduct(Product $product)
    {
        $gambarList = GambarKerja::where('product_id', $product->id)->get();
        foreach ($gambarList as $g) {
            Storage::disk('public')->delete($g->file_path);
        }
        GambarKerja::where('product_id', $product->id)->delete();

        ActivityLog::record('delete', "Hapus semua gambar kerja: {$product->name}");

        return redirect()->route('gambar-kerja.index')
            ->with('success', "Semua gambar kerja '{$product->name}' berhasil dihapus.");
    }

    public function destroy(GambarKerja $gambarKerja)
    {
        $productId = $gambarKerja->product_id;
        Storage::disk('public')->delete($gambarKerja->file_path);
        $judul = $gambarKerja->judul;
        $gambarKerja->delete();

        // Renomor ulang urutan supaya tetap berurutan
        GambarKerja::where('product_id', $productId)
            ->orderBy('urutan')
            ->get()
            ->each(fn($g, $i) => $g->update(['urutan' => $i + 1, 'judul' => 'Gambar ' . ($i + 1)]));

        ActivityLog::record('delete', "Hapus gambar kerja: {$judul}");

        return redirect()->route('gambar-kerja.by-product', $productId)
            ->with('success', "Gambar kerja '{$judul}' berhasil dihapus dan nomor urut diperbarui.");
    }
}
