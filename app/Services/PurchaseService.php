<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string, unit_cost: int|float|string}>  $items
     */
    public function create(User $user, int $supplierId, array $items): Purchase
    {
        usort($items, fn ($a, $b) => (int) $a['product_id'] <=> (int) $b['product_id']);

        return DB::transaction(function () use ($user, $supplierId, $items) {
            $purchase = Purchase::create([
                'supplier_id' => $supplierId,
                'user_id' => $user->id,
                'purchase_number' => 'PO-' . now()->format('YmdHis') . '-' . random_int(100000, 999999),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find((int) $item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }

                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1 || $unitCost < 0) {
                    throw ValidationException::withMessages(['items' => 'Kuantitas atau harga tidak valid.']);
                }

                $subtotal = round($unitCost * $qty, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $qty);
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /**
     * Batalkan pembelian: stok dikurangi kembali.
     * Ditolak jika stok saat ini sudah tidak cukup (agar stok tidak negatif).
     */
    public function cancel(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            if ($purchase->status !== 'completed') {
                throw ValidationException::withMessages(['cancel' => 'Transaksi ini sudah dibatalkan.']);
            }

            foreach ($purchase->items()->orderBy('product_id')->get() as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cancel' => "Tidak bisa dibatalkan: stok {$product->name} saat ini ({$product->stock}) kurang dari jumlah pembelian ({$item->quantity}).",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });
    }
}
