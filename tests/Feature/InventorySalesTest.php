<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InventorySalesTest extends TestCase
{
    use RefreshDatabase;

    // ---------- helper ----------
    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    private function product(array $override = []): Product
    {
        $category = Category::firstOrCreate(['name' => 'Umum']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-' . Str::upper(Str::random(8)),
            'name' => 'Produk Uji',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => 10,
            'minimum_stock' => 2,
        ], $override));
    }

    private function validProductData(array $override = []): array
    {
        return array_merge([
            'category_id' => Category::firstOrCreate(['name' => 'Umum'])->id,
            'sku' => 'NEW-001',
            'name' => 'Produk Baru',
            'purchase_price' => 1000,
            'selling_price' => 2000,
            'stock' => 5,
            'minimum_stock' => 1,
        ], $override);
    }

    // ---------- AUTH ----------
    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'user@example.test']);

        $this->post('/login', ['email' => 'user@example.test', 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_correct_password_can_login(): void
    {
        User::factory()->create(['email' => 'user@example.test']); // password factory: "password"

        $this->post('/login', ['email' => 'user@example.test', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    // ---------- PRODUCT ----------
    public function test_duplicate_sku_is_rejected(): void
    {
        $this->product(['sku' => 'DUP-001']);

        $this->actingAs($this->admin())
            ->post('/products', $this->validProductData(['sku' => 'DUP-001']))
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::where('sku', 'DUP-001')->count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post('/products', $this->validProductData(['selling_price' => -5]))
            ->assertSessionHasErrors('selling_price');

        $this->assertDatabaseMissing('products', ['sku' => 'NEW-001']);
    }

    public function test_admin_can_create_product(): void
    {
        $this->actingAs($this->admin())
            ->post('/products', $this->validProductData())
            ->assertRedirect('/products');

        $this->assertDatabaseHas('products', ['sku' => 'NEW-001', 'stock' => 5]);
    }

    public function test_product_used_in_transaction_cannot_be_deleted(): void
    {
        $product = $this->product(['stock' => 10]);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);

        $this->actingAs($this->admin())->delete("/products/{$product->id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // ---------- PURCHASE ----------
    public function test_purchase_increases_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);

        $this->actingAs($this->staff())->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 1000]],
        ])->assertRedirect();

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseHas('purchases', ['supplier_id' => $supplier->id, 'status' => 'completed']);
        $this->assertEquals(5000, Purchase::first()->total);
    }

    // ---------- SALE ----------
    public function test_sale_decreases_stock_and_total_is_computed_by_server(): void
    {
        $product = $this->product(['stock' => 10, 'selling_price' => 1500]);

        // Browser mencoba mengirim harga palsu; harus diabaikan.
        $this->actingAs($this->staff())->post('/sales', [
            'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1, 'total' => 1]],
            'total' => 1,
        ])->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertEquals(4500, Sale::first()->total);
        $this->assertEquals(1500, SaleItem::first()->unit_price);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $product = $this->product(['stock' => 4]);

        $this->actingAs($this->staff())
            ->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 6]]])
            ->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_sale_quantity_must_be_positive(): void
    {
        $product = $this->product();

        $this->actingAs($this->staff())
            ->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 0]]])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Sale::count());
    }

    public function test_failed_item_rolls_back_whole_transaction(): void
    {
        $ok = $this->product(['stock' => 10]);
        $low = $this->product(['stock' => 1]);

        $this->actingAs($this->staff())->post('/sales', [
            'items' => [
                ['product_id' => $ok->id, 'quantity' => 2],   // valid
                ['product_id' => $low->id, 'quantity' => 5],  // stok tidak cukup -> rollback semua
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $ok->fresh()->stock);   // item valid pun tidak boleh berkurang
        $this->assertSame(1, $low->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_same_product_on_two_lines_cannot_bypass_stock_check(): void
    {
        $product = $this->product(['stock' => 5]);

        $this->actingAs($this->staff())->post('/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
                ['product_id' => $product->id, 'quantity' => 3], // total 6 > stok 5
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_cancel_sale_restores_stock(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 4]]]);
        $this->assertSame(6, $product->fresh()->stock);

        $sale = Sale::first();
        $this->actingAs($this->admin())->post("/sales/{$sale->id}/cancel")->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);

        // Dibatalkan dua kali tidak boleh menambah stok lagi.
        $this->actingAs($this->admin())->post("/sales/{$sale->id}/cancel")->assertSessionHasErrors('cancel');
        $this->assertSame(10, $product->fresh()->stock);
    }

    // ---------- AUTHORIZATION ----------
    public function test_staff_cannot_access_admin_functions(): void
    {
        $staff = $this->staff();
        $product = $this->product();

        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->actingAs($staff)->get('/categories')->assertForbidden();
        $this->actingAs($staff)->get('/suppliers')->assertForbidden();
        $this->actingAs($staff)->get('/products/create')->assertForbidden();
        $this->actingAs($staff)->post('/products', $this->validProductData())->assertForbidden();
        $this->actingAs($staff)->delete("/products/{$product->id}")->assertForbidden();

        $this->assertDatabaseMissing('products', ['sku' => 'NEW-001']);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    public function test_staff_cannot_cancel_transaction(): void
    {
        $product = $this->product(['stock' => 10]);
        $staff = $this->staff();

        $this->actingAs($staff)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 2]]]);
        $sale = Sale::first();

        $this->actingAs($staff)->post("/sales/{$sale->id}/cancel")->assertForbidden();
        $this->assertSame('completed', $sale->fresh()->status);
    }

    public function test_staff_cannot_view_other_staff_transaction(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $sale = Sale::first();

        $this->actingAs($this->staff())->get("/sales/{$sale->id}")->assertForbidden();
        $this->actingAs($this->admin())->get("/sales/{$sale->id}")->assertOk();
    }

    // ---------- SEARCH / PAGINATION ----------
    public function test_search_filter_and_pagination(): void
    {
        $kopi = Category::create(['name' => 'Kopi']);
        $teh = Category::create(['name' => 'Teh']);

        foreach (range(1, 12) as $i) {
            $this->product(['category_id' => $kopi->id, 'sku' => "KOPI-{$i}", 'name' => "Kopi {$i}"]);
        }
        foreach (range(1, 3) as $i) {
            $this->product(['category_id' => $teh->id, 'sku' => "TEH-{$i}", 'name' => "Teh {$i}"]);
        }

        $user = $this->staff();

        // Pagination: 15 produk, 10 per halaman.
        $all = $this->actingAs($user)->get('/products')->viewData('products');
        $this->assertSame(15, $all->total());
        $this->assertCount(10, $all->items());

        // Search berdasarkan nama/SKU.
        $search = $this->actingAs($user)->get('/products?q=Kopi')->viewData('products');
        $this->assertSame(12, $search->total());

        $bySku = $this->actingAs($user)->get('/products?q=TEH-2')->viewData('products');
        $this->assertSame(1, $bySku->total());

        // Filter kategori.
        $byCategory = $this->actingAs($user)->get('/products?category_id=' . $teh->id)->viewData('products');
        $this->assertSame(3, $byCategory->total());
    }

    public function test_sales_filter_by_status_and_validation_of_dates(): void
    {
        $product = $this->product(['stock' => 20]);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]]);
        Sale::first()->update(['status' => 'cancelled']);

        $cancelled = $this->actingAs($admin)->get('/sales?status=cancelled')->viewData('sales');
        $this->assertSame(1, $cancelled->total());

        $this->actingAs($admin)->get('/sales?from=2026-10-10&to=2026-10-01')->assertSessionHasErrors('to');
    }

    public function test_low_stock_page_lists_only_low_stock_products(): void
    {
        $this->product(['sku' => 'LOW-1', 'stock' => 1, 'minimum_stock' => 5]);
        $this->product(['sku' => 'OK-1', 'stock' => 50, 'minimum_stock' => 5]);

        $low = $this->actingAs($this->staff())->get('/low-stock')->viewData('products');

        $this->assertSame(1, $low->total());
        $this->assertSame('LOW-1', $low->items()[0]->sku);
    }

    public function test_dashboard_numbers_come_from_database(): void
    {
        $product = $this->product(['stock' => 10, 'selling_price' => 2000]);
        $admin = $this->admin();

        $this->actingAs($admin)->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 2]]]);

        $stats = $this->actingAs($admin)->get('/dashboard')->viewData('stats');

        $this->assertSame(1, $stats['total_products']);
        $this->assertSame(1, $stats['sales_count']);
        $this->assertEquals(4000, $stats['sales_today']);
    }
}
