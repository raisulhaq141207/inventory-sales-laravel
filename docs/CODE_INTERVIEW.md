# Catatan Persiapan Code Ownership Interview

Jawaban singkat + lokasi kode untuk pertanyaan contoh di slide UTS.

| Pertanyaan | Jawaban singkat | Lokasi |
|---|---|---|
| Mengapa `Sale` memiliki relasi `hasMany` ke `SaleItem`? | Satu penjualan berisi banyak item. Foreign key `sale_id` ada di tabel `sale_items` (sisi "banyak"); `SaleItem` memakai `belongsTo(Sale)`. | `app/Models/Sale.php`, `SaleItem.php`, migration `..._create_sales_tables.php` |
| Di mana sistem memastikan stok tidak negatif? | Tiga lapis: (1) `SaleService::create` memeriksa `$product->stock < $qty` sebelum `decrement`; (2) cek dilakukan setelah `lockForUpdate`; (3) kolom `stock` bertipe `unsignedInteger`. | `app/Services/SaleService.php`, migration `..._create_products_table.php` |
| Mengapa total dihitung di server? | Input browser bisa dimanipulasi. Harga diambil dari `products.selling_price` di database, subtotal dan total dihitung server. Test `test_sale_decreases_stock_and_total_is_computed_by_server` mengirim harga palsu dan terbukti diabaikan. | `SaleService::create`, `tests/Feature/InventorySalesTest.php` |
| Risiko jika pengurangan stok di luar `DB::transaction`? | Jika error terjadi di tengah proses, header/item tersimpan tetapi stok tidak berubah (atau sebaliknya) → data setengah tersimpan dan stok tidak sinkron. Di dalam transaction semua di-rollback. | `SaleService::create`, `PurchaseService::create` |
| Bagaimana mencegah Staff mengakses fungsi Admin lewat URL langsung? | Gate `admin` didefinisikan di `AppServiceProvider`, dipasang pada route lewat middleware `can:admin`. Server mengembalikan 403 walau tombol disembunyikan. | `app/Providers/AppServiceProvider.php`, `routes/web.php`, test `test_staff_cannot_access_admin_functions` |
| Apa yang terjadi jika dua penjualan memproses stok produk yang sama hampir bersamaan? | `Product::lockForUpdate()` mengunci baris produk (pada MySQL/InnoDB) sampai transaksi selesai. Transaksi kedua menunggu, lalu membaca stok terbaru, sehingga overselling dicegah. Produk juga diproses berurutan berdasarkan `product_id` untuk mengurangi risiko deadlock. | `SaleService::create`, `mergeLines()` |
| Tunjukkan validasi SKU unik dan peran constraint database. | Validasi Laravel `Rule::unique('products','sku')->ignore(...)` memberi pesan error yang ramah. Unique index di database tetap melindungi jika ada dua request bersamaan yang lolos validasi. | `app/Http/Requests/ProductRequest.php`, migration products |
| Ubah aturan minimum stock dan tunjukkan dampaknya. | Ubah `minimum_stock` produk (halaman Edit) atau ubah `scopeLowStock` di `Product` (mis. `<` menjadi `<=`). Dampaknya terlihat di badge "Rendah", halaman Stok Rendah, dan dashboard. | `app/Models/Product.php`, `ProductController::listing`, `DashboardController` |

## Alur satu request penjualan (untuk dijelaskan)

1. `POST /sales` → middleware `auth` → `SaleController@store`.
2. `StoreSaleRequest` memvalidasi `items` (array, `product_id` ada di DB, `quantity` integer ≥ 1).
3. `SaleService::create` menggabungkan baris produk yang sama, lalu membuka `DB::transaction`.
4. Header `sales` dibuat → untuk tiap produk: kunci baris, cek stok, hitung subtotal dari harga DB, simpan `sale_items`, kurangi stok.
5. Jika ada yang gagal → `ValidationException` → rollback semua → kembali ke form dengan pesan error dan input lama.
6. Jika sukses → total diperbarui → redirect ke detail penjualan dengan pesan sukses.

## Pertanyaan tambahan yang mungkin muncul

- **Mengapa memakai Service class?** Menjaga controller tetap ringkas dan logika transaksi dapat diuji/dipakai ulang.
- **Mengapa pembatalan, bukan edit transaksi?** Riwayat tetap utuh dan pemulihan stok konsisten.
- **Mengapa `with('category')`?** Eager loading, mencegah N+1 query saat daftar produk.
- **Mengapa `withQueryString()`?** Agar filter pencarian tetap terbawa saat pindah halaman.
- **Mengapa `restrictOnDelete`?** Data master yang sudah dipakai transaksi tidak boleh hilang.
