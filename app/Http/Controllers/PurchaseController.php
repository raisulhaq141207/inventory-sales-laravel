<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(private PurchaseService $service)
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

        $purchases = Purchase::with(['supplier', 'user'])
            ->filter($filters)
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest('purchased_at')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->service->create(
            $request->user(),
            (int) $request->validated('supplier_id'),
            $request->validated('items')
        );

        return redirect()->route('purchases.show', $purchase)->with('success', 'Pembelian berhasil disimpan, stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_unless($request->user()->isAdmin() || $purchase->user_id === $request->user()->id, 403);

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        $this->service->cancel($purchase);

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dibatalkan dan stok disesuaikan.');
    }
}
