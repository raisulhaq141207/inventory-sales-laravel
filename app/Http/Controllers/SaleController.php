<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private SaleService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $sales = Sale::with('user')
            ->filter($filters)
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        // Hanya product_id & quantity yang dipakai; harga & total dihitung server.
        $sale = $this->service->create($request->user(), $request->validated('items'));

        return redirect()->route('sales.show', $sale)->with('success', 'Penjualan berhasil disimpan.');
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless($request->user()->isAdmin() || $sale->user_id === $request->user()->id, 403);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        $this->service->cancel($sale);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}
