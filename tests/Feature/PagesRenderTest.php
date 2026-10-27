<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private function assertPageOk(string $url): void
    {
        $response = $this->get($url);

        $this->assertSame(200, $response->getStatusCode(), "Halaman {$url} gagal dirender (status {$response->getStatusCode()}).");
    }

    public function test_all_pages_render_for_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Umum']);
        $supplier = Supplier::create(['name' => 'Pemasok Uji']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'RND-001',
            'name' => 'Produk Render',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => 10,
            'minimum_stock' => 2,
        ]);

        $this->actingAs($admin);

        $this->post('/sales', ['items' => [['product_id' => $product->id, 'quantity' => 1]]])->assertRedirect();
        $this->post('/purchases', [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'unit_cost' => 1000]],
        ])->assertRedirect();

        $sale = Sale::firstOrFail();
        $purchase = Purchase::firstOrFail();

        $urls = [
            '/dashboard',
            '/products', '/products/create', "/products/{$product->id}", "/products/{$product->id}/edit", '/low-stock',
            '/categories', '/categories/create', "/categories/{$category->id}/edit",
            '/suppliers', '/suppliers/create', "/suppliers/{$supplier->id}/edit",
            '/users', '/users/create', "/users/{$admin->id}/edit",
            '/purchases', '/purchases/create', "/purchases/{$purchase->id}",
            '/sales', '/sales/create', "/sales/{$sale->id}",
        ];

        foreach ($urls as $url) {
            $this->assertPageOk($url);
        }
    }

    public function test_main_pages_render_for_staff(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        Product::create([
            'category_id' => Category::create(['name' => 'Umum'])->id,
            'sku' => 'RND-002',
            'name' => 'Produk Staff',
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => 5,
            'minimum_stock' => 1,
        ]);

        $this->actingAs($staff);

        foreach (['/dashboard', '/products', '/low-stock', '/purchases', '/purchases/create', '/sales', '/sales/create'] as $url) {
            $this->assertPageOk($url);
        }
    }

    public function test_login_page_renders_for_guest(): void
    {
        $this->assertPageOk('/login');
    }
}
