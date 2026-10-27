<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Customer membuat pesanan. Stok langsung "dikunci" (dikurangi) dalam satu
     * transaksi database, sehingga stok yang sudah dipesan tidak bisa dijual lagi.
     *
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     */
    public function place(User $customer, array $items): Order
    {
        $lines = $this->mergeLines($items);

        return DB::transaction(function () use ($customer, $lines) {
            $order = Order::create([
                'user_id' => $customer->id,
                'order_number' => 'OR-' . now()->format('YmdHis') . '-' . random_int(100000, 999999),
                'total' => 0,
                'status' => 'pending',
                'ordered_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $productId => $qty) {
                $product = Product::lockForUpdate()->find($productId);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                // Harga selalu dari database, bukan dari browser.
                $unitPrice = $product->selling_price;
                $subtotal = round($unitPrice * $qty, 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $order->update(['total' => $total]);

            return $order;
        });
    }

    /**
     * Batalkan pesanan yang masih pending dan kembalikan stok.
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'cancel' => 'Hanya pesanan berstatus pending yang dapat dibatalkan.',
                ]);
            }

            foreach ($order->items()->orderBy('product_id')->get() as $item) {
                Product::lockForUpdate()->findOrFail($item->product_id)->increment('stock', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);
        });
    }

    /**
     * Staff/admin mengonfirmasi pesanan -> otomatis menjadi data penjualan.
     * Stok TIDAK berubah lagi karena sudah dikurangi saat pesanan dibuat.
     */
    public function confirm(Order $order, User $staff): Sale
    {
        return DB::transaction(function () use ($order, $staff) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'cancel' => 'Hanya pesanan berstatus pending yang dapat dikonfirmasi.',
                ]);
            }

            $sale = Sale::create([
                'user_id' => $staff->id,
                'sale_number' => 'SL-' . now()->format('YmdHis') . '-' . random_int(100000, 999999),
                'total' => $order->total,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            foreach ($order->items as $item) {
                $sale->items()->create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $order->update(['status' => 'confirmed', 'sale_id' => $sale->id]);

            return $sale;
        });
    }

    private function mergeLines(array $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $lines[$id] = ($lines[$id] ?? 0) + (int) $item['quantity'];
        }

        ksort($lines);

        return $lines;
    }
}
