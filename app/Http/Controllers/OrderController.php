<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

// Pengelolaan pesanan online oleh staff / admin.
class OrderController extends Controller
{
    public function __construct(private OrderService $service)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'in:pending,confirmed,cancelled'],
        ]);

        $orders = Order::with('user')
            ->filter($filters)
            ->latest('ordered_at')
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'sale']);

        return view('orders.show', compact('order'));
    }

    public function confirm(Request $request, Order $order)
    {
        $sale = $this->service->confirm($order, $request->user());

        return redirect()->route('orders.show', $order)
            ->with('success', "Pesanan dikonfirmasi dan tercatat sebagai penjualan {$sale->sale_number}.");
    }

    public function cancel(Order $order)
    {
        $this->service->cancel($order);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibatalkan dan stok dikembalikan.');
    }
}
