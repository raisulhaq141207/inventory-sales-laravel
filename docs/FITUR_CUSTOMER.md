# Fitur Tambahan: Customer & Pesanan Online

Fitur ini **tambahan** di luar spesifikasi UTS (admin & staff). Fitur inti tidak berubah.

## Ringkasan
- Customer mendaftar sendiri (`/register`), login, melihat katalog (`/shop`), dan membuat pesanan (`/my-orders`).
- Staff/admin mengelola pesanan di menu **Pesanan Online** (`/orders`).
- Customer **tidak dapat** membuka halaman admin/staff (HTTP 403).

## Alur pesanan

```
Customer buat pesanan ──► status PENDING (stok langsung dikurangi / dicadangkan)
        │
        ├─ Customer / Staff batalkan ─► CANCELLED (stok dikembalikan)
        │
        └─ Staff / Admin konfirmasi ──► CONFIRMED + otomatis menjadi data Penjualan
                                          (stok TIDAK berubah lagi)
                                          │
                                          └─ Admin batalkan penjualan ─► pesanan CANCELLED, stok kembali
```

## Aturan bisnis
1. Role customer ditentukan server saat registrasi; input `role` dari browser diabaikan.
2. Harga dan total dihitung server dari database, bukan dari input browser.
3. Pesanan melebihi stok ditolak, dan tidak ada data yang berubah (satu `DB::transaction` + `lockForUpdate`).
4. Customer hanya dapat melihat/membatalkan pesanannya sendiri.
5. Hanya pesanan `pending` yang dapat dibatalkan atau dikonfirmasi.
6. Produk yang sudah dipakai pesanan tidak dapat dihapus.

## Hak akses

| Halaman | Admin | Staff | Customer |
|---|:---:|:---:|:---:|
| Dashboard, produk, pembelian, penjualan | ✔ | ✔ | ✘ (403) |
| Pesanan Online (`/orders`) | ✔ | ✔ | ✘ (403) |
| Katalog & pesanan saya (`/shop`, `/my-orders`) | ✘ (403) | ✘ (403) | ✔ |
| Kategori, pemasok, pengguna | ✔ | ✘ | ✘ |

Implementasi: `Gate::define('admin' | 'staff-area' | 'customer')` di `AppServiceProvider`, dipasang lewat middleware `can:...` di `routes/web.php`.

## Tabel baru

`orders` (user_id → customer, sale_id nullable, order_number unik, total, status, ordered_at)
`order_items` (order_id, product_id, quantity, unit_price, subtotal)

## File
- Migration: `2026_10_27_000001_create_orders_tables.php`
- Model: `Order`, `OrderItem`
- Service: `OrderService` (`place`, `cancel`, `confirm`)
- Controller: `ShopController`, `CustomerOrderController`, `OrderController`; `AuthController` (register)
- Test: `tests/Feature/CustomerOrderTest.php`
