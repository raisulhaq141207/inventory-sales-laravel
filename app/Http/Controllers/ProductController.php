<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    public function lowStock(Request $request)
    {
        return $this->listing($request, true);
    }

    private function listing(Request $request, bool $lowOnly)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ]);

        // with('category') = eager loading, mencegah N+1 query.
        $products = Product::with('category')
            ->search($filters['q'] ?? null)
            ->when($filters['category_id'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($lowOnly, fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
            'lowOnly' => $lowOnly,
        ]);
    }

    public function create()
    {
        return view('products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(ProductRequest $request)
    {
        Product::create($request->validated());

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Produk yang sudah dipakai transaksi tidak boleh dihapus (riwayat harus utuh).
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Produk sudah dipakai dalam transaksi dan tidak dapat dihapus.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
