<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_use_administrator_actions_even_with_direct_requests(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create(['name' => 'Protected Product', 'price' => '20.00', 'stock_quantity' => 5]);
        $customer = Customer::create(['full_name' => 'Protected Customer', 'email' => 'protected@example.test']);

        $this->actingAs($cashier)->get(route('staff.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($cashier)->delete(route('staff.destroy', $admin))->assertForbidden();
        $this->actingAs($cashier)->post(route('products.store'), [
            'name' => 'Unauthorized Product',
            'price' => '1.00',
            'stock_quantity' => 1,
        ])->assertForbidden();
        $this->actingAs($cashier)->put(route('products.update', $product), [
            'name' => 'Unauthorized Edit',
            'price' => '1.00',
            'stock_quantity' => 1,
            'original_stock' => 5,
        ])->assertForbidden();
        $this->actingAs($cashier)->delete(route('customers.destroy', $customer))->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseMissing('products', ['name' => 'Unauthorized Product']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Protected Product']);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
    }

    public function test_cashier_can_use_daily_sales_and_customer_workflows(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $product = Product::create(['name' => 'Sellable Product', 'price' => '20.00', 'stock_quantity' => 5]);

        $this->actingAs($cashier)->get(route('products.index'))->assertOk();
        $this->actingAs($cashier)->get(route('customers.create'))->assertOk();
        $this->actingAs($cashier)->get(route('sales.create'))->assertOk();
        $this->actingAs($cashier)->post(route('sales.store'), [
            'product_id' => $product->public_id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('sales', ['sold_by' => $cashier->id, 'product_id' => $product->id]);
    }

    public function test_public_urls_use_uuids_and_reject_internal_numeric_ids_for_every_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = Customer::create(['full_name' => 'Public Reference Customer', 'email' => 'public@example.test']);
        $product = Product::create(['name' => 'Public Reference Product', 'price' => '10.00', 'stock_quantity' => 1]);
        $sale = Sale::create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'sold_by' => $admin->id,
            'quantity' => 1,
            'total_price' => '10.00',
        ]);

        foreach ([
            ['route' => 'products.show', 'prefix' => 'products', 'model' => $product],
            ['route' => 'customers.show', 'prefix' => 'customers', 'model' => $customer],
            ['route' => 'staff.show', 'prefix' => 'staff', 'model' => $admin],
            ['route' => 'sales.show', 'prefix' => 'sales', 'model' => $sale],
        ] as $resource) {
            $model = $resource['model'];
            $url = route($resource['route'], $model);
            $routeReference = basename((string) parse_url($url, PHP_URL_PATH));

            $this->assertSame($model->public_id, $routeReference);
            $this->assertNotSame((string) $model->id, $routeReference);
            $this->actingAs($admin)->get('/'.$resource['prefix'].'/'.$model->id)->assertNotFound();
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_new_staff_password_is_hashed_and_never_stored_as_plain_text(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('staff.store'), [
            'username' => 'securecashier',
            'email' => 'securecashier@example.test',
            'full_name' => 'Secure Cashier',
            'role' => 'cashier',
            'password' => 'VerySecure123!',
            'password_confirmation' => 'VerySecure123!',
        ])->assertRedirect();

        $staff = User::where('username', 'securecashier')->firstOrFail();
        $this->assertNotSame('VerySecure123!', $staff->password);
        $this->assertTrue(Hash::check('VerySecure123!', $staff->password));
    }

    public function test_last_administrator_cannot_be_demoted_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'cashier']);

        $this->actingAs($admin)->put(route('staff.update', $admin), [
            'username' => $admin->username,
            'email' => $admin->email,
            'full_name' => $admin->full_name,
            'role' => 'cashier',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasErrors('role');

        $this->actingAs($admin)->delete(route('staff.destroy', $admin))->assertSessionHas('error');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_responses_include_browser_security_headers(): void
    {
        $this->get('/login')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), geolocation=(), microphone=()')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }
}
