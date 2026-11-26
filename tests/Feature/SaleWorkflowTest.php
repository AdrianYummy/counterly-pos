<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create();
    }

    public function test_named_customer_sale_uses_server_values_and_deducts_stock(): void
    {
        $product = Product::create(['name' => 'Test Item', 'price' => '25.00', 'stock_quantity' => 10]);
        $customer = Customer::create(['full_name' => 'Test Customer', 'email' => 'customer@example.test']);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'quantity' => 3,
            'total_price' => '0.01',
            'sold_by' => 999,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_quantity' => 7]);
        $this->assertDatabaseHas('sales', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $this->staff->id,
            'quantity' => 3,
            'total_price' => 75,
        ]);
    }

    public function test_insufficient_stock_rejects_sale_without_any_write(): void
    {
        $product = Product::create(['name' => 'Limited Item', 'price' => '25.00', 'stock_quantity' => 7]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->id,
            'quantity' => 8,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertSame(0, Sale::count());
    }

    public function test_walk_in_and_exact_stock_sale_succeeds(): void
    {
        $product = Product::create(['name' => 'Last Item', 'price' => '99.95', 'stock_quantity' => 5]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->id,
            'customer_id' => '',
            'quantity' => 5,
        ])->assertRedirect();

        $sale = Sale::firstOrFail();
        $this->assertNull($sale->customer_id);
        $this->assertSame('499.75', $sale->total_price);
        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->actingAs($this->staff)->get('/sales')->assertSee('Walk-in customer');
    }

    public function test_amount_overflow_is_rejected_without_stock_change(): void
    {
        $product = Product::create(['name' => 'Expensive Item', 'price' => '99999999.99', 'stock_quantity' => 2]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_historical_total_does_not_change_with_product_price(): void
    {
        $product = Product::create(['name' => 'Stable History', 'price' => '50.00', 'stock_quantity' => 5]);
        $this->actingAs($this->staff)->post('/sales', ['product_id' => $product->id, 'quantity' => 2]);
        $product->update(['price' => '80.00']);

        $this->assertSame('100.00', Sale::firstOrFail()->fresh()->total_price);
    }
}
