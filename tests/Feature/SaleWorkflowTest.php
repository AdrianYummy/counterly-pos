<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\SaleReceiptNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
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
            'product_id' => $product->public_id,
            'customer_id' => $customer->public_id,
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
        $stockLog = ActivityLog::where('subject_type', 'product')->where('action', 'sold')->firstOrFail();
        $this->assertSame($this->staff->id, $stockLog->actor_id);
        $this->assertSame('10', $stockLog->changes['stock_quantity']['from']);
        $this->assertSame('7', $stockLog->changes['stock_quantity']['to']);
        $this->assertSame('3', $stockLog->changes['quantity_sold']['to']);
    }

    public function test_multiple_products_are_recorded_as_one_transaction_and_shown_on_one_receipt(): void
    {
        $coffee = Product::create(['name' => 'Cart Coffee', 'price' => '120.00', 'stock_quantity' => 10]);
        $pastry = Product::create(['name' => 'Cart Pastry', 'price' => '75.00', 'stock_quantity' => 8]);
        $customer = Customer::create(['full_name' => 'Cart Customer', 'email' => 'cart@example.test']);

        $response = $this->actingAs($this->staff)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $coffee->public_id, 'quantity' => 2],
                ['product_id' => $pastry->public_id, 'quantity' => 3],
            ],
            'customer_id' => $customer->public_id,
        ]);

        $response->assertRedirect();
        $this->assertSame(8, $coffee->fresh()->stock_quantity);
        $this->assertSame(5, $pastry->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 2);

        $sales = Sale::orderBy('id')->get();
        $this->assertNotNull($sales->first()->transaction_id);
        $this->assertSame($sales->first()->transaction_id, $sales->last()->transaction_id);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Cart Coffee')
            ->assertSee('Cart Pastry')
            ->assertSee('465.00');
    }

    public function test_multi_product_checkout_rolls_back_every_item_when_one_has_insufficient_stock(): void
    {
        $available = Product::create(['name' => 'Available Cart Item', 'price' => '50.00', 'stock_quantity' => 5]);
        $limited = Product::create(['name' => 'Limited Cart Item', 'price' => '40.00', 'stock_quantity' => 1]);

        $this->actingAs($this->staff)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $available->public_id, 'quantity' => 2],
                ['product_id' => $limited->public_id, 'quantity' => 2],
            ],
        ])->assertSessionHasErrors('items.1.quantity');

        $this->assertSame(5, $available->fresh()->stock_quantity);
        $this->assertSame(1, $limited->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_point_of_sale_contains_multi_product_cart_and_remove_controls(): void
    {
        Product::create(['name' => 'Selectable Cart Item', 'price' => '80.00', 'stock_quantity' => 4]);

        $this->actingAs($this->staff)->get(route('sales.create'))
            ->assertOk()
            ->assertSee('Build the order')
            ->assertSee('data-cart-item-template', false)
            ->assertSee('data-cart-remove', false)
            ->assertSee('data-quantity-plus', false);
    }

    public function test_insufficient_stock_rejects_sale_without_any_write(): void
    {
        $product = Product::create(['name' => 'Limited Item', 'price' => '25.00', 'stock_quantity' => 7]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->public_id,
            'quantity' => 8,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(7, $product->fresh()->stock_quantity);
        $this->assertSame(0, Sale::count());
    }

    public function test_walk_in_and_exact_stock_sale_succeeds(): void
    {
        $product = Product::create(['name' => 'Last Item', 'price' => '99.95', 'stock_quantity' => 5]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->public_id,
            'customer_id' => '',
            'quantity' => 5,
        ])->assertRedirect();

        $sale = Sale::firstOrFail();
        $this->assertNull($sale->customer_id);
        $this->assertSame('499.75', $sale->total_price);
        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->actingAs($this->staff)->get('/sales')->assertSee('Walk-in Customer');
    }

    public function test_amount_overflow_is_rejected_without_stock_change(): void
    {
        $product = Product::create(['name' => 'Expensive Item', 'price' => '99999999.99', 'stock_quantity' => 2]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->public_id,
            'quantity' => 2,
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_historical_total_does_not_change_with_product_price(): void
    {
        $product = Product::create(['name' => 'Stable History', 'price' => '50.00', 'stock_quantity' => 5]);
        $this->actingAs($this->staff)->post('/sales', ['product_id' => $product->public_id, 'quantity' => 2]);
        $product->update(['price' => '80.00']);

        $this->assertSame('100.00', Sale::firstOrFail()->fresh()->total_price);
    }

    public function test_named_customer_can_receive_a_sale_receipt_email(): void
    {
        Notification::fake();
        $product = Product::create(['name' => 'Receipt Item', 'price' => '35.00', 'stock_quantity' => 3]);
        $customer = Customer::create(['full_name' => 'Receipt Customer', 'email' => 'receipt@example.test']);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->public_id,
            'customer_id' => $customer->public_id,
            'quantity' => 1,
            'email_receipt' => '1',
        ])->assertRedirect();

        Notification::assertSentTo($customer, SaleReceiptNotification::class, function (SaleReceiptNotification $notification) use ($customer) {
            $mail = $notification->toMail($customer);
            $this->assertStringContainsString('CaféFlow receipt', $mail->subject);

            return true;
        });
    }

    public function test_walk_in_sale_does_not_attempt_a_receipt_email(): void
    {
        Notification::fake();
        $product = Product::create(['name' => 'Walk-in Item', 'price' => '35.00', 'stock_quantity' => 3]);

        $this->actingAs($this->staff)->post('/sales', [
            'product_id' => $product->public_id,
            'quantity' => 1,
            'email_receipt' => '1',
        ])->assertRedirect();

        Notification::assertNothingSent();
    }

    public function test_receipt_email_can_be_resent_from_transaction_details(): void
    {
        Notification::fake();
        $product = Product::create(['name' => 'Resend Item', 'price' => '35.00', 'stock_quantity' => 3]);
        $customer = Customer::create(['full_name' => 'Resend Customer', 'email' => 'resend@example.test']);
        $sale = Sale::create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $this->staff->id,
            'quantity' => 1,
            'total_price' => '35.00',
        ]);

        $this->actingAs($this->staff)->post(route('sales.email-receipt', $sale))
            ->assertSessionHas('success');

        Notification::assertSentTo($customer, SaleReceiptNotification::class);
    }
}
