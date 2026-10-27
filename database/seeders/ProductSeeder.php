<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // [kategori, sku, nama, harga beli, harga jual, stok, stok minimum]
        $products = [
            ['Elektronik', 'ELK-001', 'Mouse Wireless', 90000, 125000, 40, 10],
            ['Elektronik', 'ELK-002', 'Keyboard Mekanik', 350000, 450000, 4, 5],
            ['Elektronik', 'ELK-003', 'Flashdisk 64GB', 60000, 85000, 60, 15],
            ['Alat Tulis', 'ATK-001', 'Pulpen Hitam (Box)', 20000, 30000, 100, 20],
            ['Alat Tulis', 'ATK-002', 'Buku Tulis A5', 5000, 8000, 200, 50],
            ['Alat Tulis', 'ATK-003', 'Stapler Besar', 25000, 38000, 3, 5],
            ['Perabot', 'PRB-001', 'Kursi Kantor', 550000, 750000, 8, 3],
            ['Perabot', 'PRB-002', 'Meja Kerja 120cm', 900000, 1200000, 5, 2],
            ['Makanan & Minuman', 'MKN-001', 'Kopi Arabika 250g', 45000, 65000, 30, 10],
            ['Makanan & Minuman', 'MKN-002', 'Teh Celup (Box)', 12000, 18000, 2, 10],
            ['Kebersihan', 'KBR-001', 'Sabun Cuci Tangan 500ml', 14000, 22000, 45, 12],
            ['Kebersihan', 'KBR-002', 'Tisu Gulung (Pack)', 18000, 27000, 70, 20],
        ];

        foreach ($products as [$cat, $sku, $name, $buy, $sell, $stock, $min]) {
            Product::updateOrCreate(['sku' => $sku], [
                'category_id' => Category::where('name', $cat)->value('id'),
                'name' => $name,
                'purchase_price' => $buy,
                'selling_price' => $sell,
                'stock' => $stock,
                'minimum_stock' => $min,
            ]);
        }
    }
}
