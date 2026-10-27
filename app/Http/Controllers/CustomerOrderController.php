<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function __construct(private OrderService $service)
    {
    }

    public function index(Request $request)
    {
        // Customer hanya melihat pesanannya sendiri.
        $orders = Order::where('user_id', $request->user()->id)
            ->latest('ordered_at')
            ->paginate(10);

        return view('my-orders.index', compact('orders'));
    }

    public function create()
    {
        return view('my-orders.create', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    // Aturan validasi item pesanan sama dengan penjualan (product_id & quantity).
    public function store(StoreSaleRequest $request)
    {
        $order = $this->service->place($request->user(), $request->validated('items'));

        return redirect()->route('my-orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat dan menunggu konfirmasi.');
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load('items.product');

        return view('my-orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $this->service->cancel($order);

        return redirect()->route('my-orders.show', $order)
            ->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
