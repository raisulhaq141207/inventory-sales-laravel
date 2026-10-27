<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    // ---------- helper ----------
    private function customer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function staff(): User
    {
        return User::factory()->create(['role' => 'staff']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
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

    private function placeOrder(User $customer, Product $product, int $qty): Order
    {
        $this->actingAs($customer)
            ->post('/my-orders', ['items' => [['product_id' => $product->id, 'quantity' => $qty]]])
            ->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    // ---------- REGISTRASI ----------
    public function test_register_creates_customer_account(): void
    {
        $this->post('/register', [
            'name' => 'Budi Customer',
            'email' => 'budi@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/shop');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'budi@example.test', 'role' => 'customer']);
    }

    public function test_register_cannot_choose_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup',
            'email' => 'hacker@example.test',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => 'admin', // dicoba dikirim dari browser
        ]);

        $this->assertDatabaseHas('users', ['email' => 'hacker@example.test', 'role' => 'customer']);
    }

    public function test_register_validation(): void
    {
        User::factory()->create(['email' => 'ada@example.test']);

        $this->post('/register', [
            'name' => '',
            'email' => 'ada@example.test',          // sudah dipakai
            'password' => 'pendek',                 // < 8 karakter
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_customer_login_goes_to_shop(): void
    {
        User::factory()->create(['email' => 'c@example.test', 'role' => 'customer']);

        $this->post('/login', ['email' => 'c@example.test', 'password' => 'password'])
            ->assertRedirect('/shop');
    }

    // ---------- OTORISASI ----------
    public function test_customer_cannot_access_staff_or_admin_pages(): void
    {
        $customer = $this->customer();

        foreach (['/dashboard', '/products', '/sales', '/purchases', '/orders', '/users', '/categories', '/low-stock'] as $url) {
            $this->actingAs($customer)->get($url)->assertForbidden();
        }
    }

    public function test_staff_and_guest_cannot_access_customer_area(): void
    {
        $this->get('/shop')->assertRedirect('/login');
        $this->actingAs($this->staff())->get('/shop')->assertForbidden();
        $this->actingAs($this->admin())->get('/my-orders')->assertForbidden();
    }

    // ---------- PEMESANAN ----------
    public function test_customer_order_reserves_stock_and_total_is_computed_by_server(): void
    {
        $product = $this->product(['stock' => 10, 'selling_price' => 1500]);
        $customer = $this->customer();

        $this->actingAs($customer)->post('/my-orders', [
            'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1, 'total' => 1]],
            'total' => 1, // harga palsu dari browser harus diabaikan
        ])->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame('pending', $order->status);
        $this->assertSame($customer->id, $order->user_id);
        $this->assertEquals(4500, $order->total);
        $this->assertEquals(1500, OrderItem::firstOrFail()->unit_price);
    }

    public function test_order_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $product = $this->product(['stock' => 4]);

        $this->actingAs($this->customer())
            ->post('/my-orders', ['items' => [['product_id' => $product->id, 'quantity' => 6]]])
            ->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
    }

    public function test_failed_item_rolls_back_whole_order(): void
    {
        $ok = $this->product(['stock' => 10]);
        $low = $this->product(['stock' => 1]);

        $this->actingAs($this->customer())->post('/my-orders', [
            'items' => [
                ['product_id' => $ok->id, 'quantity' => 2],
                ['product_id' => $low->id, 'quantity' => 5], // gagal -> semua rollback
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $ok->fresh()->stock);
        $this->assertSame(1, $low->fresh()->stock);
        $this->assertSame(0, Order::count());
    }

    public function test_order_quantity_must_be_positive(): void
    {
        $product = $this->product();

        $this->actingAs($this->customer())
            ->post('/my-orders', ['items' => [['product_id' => $product->id, 'quantity' => 0]]])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Order::count());
    }

    // ---------- KEPEMILIKAN & PEMBATALAN ----------
    public function test_customer_cannot_view_or_cancel_other_customer_order(): void
    {
        $product = $this->product();
        $order = $this->placeOrder($this->customer(), $product, 2);

        $other = $this->customer();

        $this->actingAs($other)->get("/my-orders/{$order->id}")->assertForbidden();
        $this->actingAs($other)->post("/my-orders/{$order->id}/cancel")->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_customer_only_sees_own_orders_in_list(): void
    {
        $product = $this->product(['stock' => 20]);
        $a = $this->customer();
        $b = $this->customer();

        $this->placeOrder($a, $product, 1);
        $this->placeOrder($b, $product, 1);

        $list = $this->actingAs($a)->get('/my-orders')->viewData('orders');

        $this->assertSame(1, $list->total());
        $this->assertSame($a->id, $list->items()[0]->user_id);
    }

    public function test_customer_cancel_pending_order_restores_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $product, 4);
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($customer)->post("/my-orders/{$order->id}/cancel")->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->status);

        // Dibatalkan dua kali tidak boleh menambah stok lagi.
        $this->actingAs($customer)->post("/my-orders/{$order->id}/cancel")->assertSessionHasErrors('cancel');
        $this->assertSame(10, $product->fresh()->stock);
    }

    // ---------- KONFIRMASI STAFF ----------
    public function test_staff_confirm_creates_sale_without_changing_stock_again(): void
    {
        $product = $this->product(['stock' => 10, 'selling_price' => 2000]);
        $order = $this->placeOrder($this->customer(), $product, 3);
        $staff = $this->staff();

        $this->actingAs($staff)->post("/orders/{$order->id}/confirm")->assertRedirect();

        $order->refresh();
        $sale = Sale::firstOrFail();

        $this->assertSame('confirmed', $order->status);
        $this->assertSame($sale->id, $order->sale_id);
        $this->assertSame($staff->id, $sale->user_id);
        $this->assertEquals(6000, $sale->total);
        $this->assertSame(1, $sale->items()->count());
        $this->assertSame(7, $product->fresh()->stock); // tidak berkurang dua kali
    }

    public function test_customer_cannot_confirm_order(): void
    {
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $this->product(), 1);

        $this->actingAs($customer)->post("/orders/{$order->id}/confirm")->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(0, Sale::count());
    }

    public function test_confirmed_order_cannot_be_cancelled_or_confirmed_again(): void
    {
        $product = $this->product(['stock' => 10]);
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $product, 2);

        $this->actingAs($this->staff())->post("/orders/{$order->id}/confirm");

        $this->actingAs($customer)->post("/my-orders/{$order->id}/cancel")->assertSessionHasErrors('cancel');
        $this->actingAs($this->staff())->post("/orders/{$order->id}/confirm")->assertSessionHasErrors('cancel');

        $this->assertSame(1, Sale::count());
        $this->assertSame(8, $product->fresh()->stock);
    }

    public function test_admin_cancel_sale_from_order_cancels_order_and_restores_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $order = $this->placeOrder($this->customer(), $product, 3);

        $this->actingAs($this->staff())->post("/orders/{$order->id}/confirm");
        $sale = Sale::firstOrFail();

        $this->actingAs($this->admin())->post("/sales/{$sale->id}/cancel")->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_staff_cancel_pending_order_restores_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $order = $this->placeOrder($this->customer(), $product, 5);

        $this->actingAs($this->staff())->post("/orders/{$order->id}/cancel")->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_product_used_in_order_cannot_be_deleted(): void
    {
        $product = $this->product(['stock' => 10]);
        $this->placeOrder($this->customer(), $product, 1);

        $this->actingAs($this->admin())->delete("/products/{$product->id}")->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // ---------- RENDER HALAMAN ----------
    public function test_customer_and_order_pages_render(): void
    {
        $product = $this->product(['stock' => 10]);
        $customer = $this->customer();
        $order = $this->placeOrder($customer, $product, 1);

        $customerUrls = ['/shop', '/my-orders', '/my-orders/create', "/my-orders/{$order->id}"];
        foreach ($customerUrls as $url) {
            $status = $this->actingAs($customer)->get($url)->getStatusCode();
            $this->assertSame(200, $status, "Halaman customer {$url} gagal dirender ({$status}).");
        }

        $staff = $this->staff();
        foreach (['/orders', "/orders/{$order->id}"] as $url) {
            $status = $this->actingAs($staff)->get($url)->getStatusCode();
            $this->assertSame(200, $status, "Halaman staff {$url} gagal dirender ({$status}).");
        }

        foreach (['/login', '/register'] as $url) {
            $this->post('/logout');
            $status = $this->get($url)->getStatusCode();
            $this->assertSame(200, $status, "Halaman {$url} gagal dirender ({$status}).");
        }
    }
}
