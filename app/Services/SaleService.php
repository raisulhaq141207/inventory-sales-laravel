<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    /**
     * Buat penjualan. Semua langkah (header, item, pengurangan stok)
     * berada dalam SATU database transaction: jika ada yang gagal -> rollback semua.
     *
     * @param  array<int, array{product_id: int|string, quantity: int|string}>  $items
     */
    public function create(User $user, array $items): Sale
    {
        $lines = $this->mergeLines($items);

        return DB::transaction(function () use ($user, $lines) {
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => 'SL-' . now()->format('YmdHis') . '-' . random_int(100000, 999999),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($lines as $productId => $qty) {
                // lockForUpdate: baris produk dikunci sampai transaksi selesai,
                // sehingga dua penjualan bersamaan tidak bisa membaca stok yang sama (cegah overselling).
                $product = Product::lockForUpdate()->find($productId);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Produk tidak ditemukan.']);
                }

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).",
                    ]);
                }

                // Harga diambil dari database, BUKAN dari input browser.
                $unitPrice = $product->selling_price;
                $subtotal = round($unitPrice * $qty, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            // Total dihitung server.
            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Batalkan penjualan dan kembalikan stok secara konsisten.
     */
    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->status !== 'completed') {
                throw ValidationException::withMessages(['cancel' => 'Transaksi ini sudah dibatalkan.']);
            }

            foreach ($sale->items()->orderBy('product_id')->get() as $item) {
                Product::lockForUpdate()->findOrFail($item->product_id)->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });
    }

    /**
     * Gabungkan baris produk yang sama dan urutkan berdasarkan product_id
     * (urutan konsisten mengurangi risiko deadlock).
     */
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
