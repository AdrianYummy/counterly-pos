<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_validation_and_prepared_image_upload(): void
    {
        Storage::fake('public');
        $staff = User::factory()->create(['role' => 'admin']);

        $this->actingAs($staff)->post('/products', [
            'name' => '', 'price' => '-1', 'stock_quantity' => '1.5',
        ])->assertSessionHasErrors(['name', 'price', 'stock_quantity']);

        $this->actingAs($staff)->post('/products', [
            'name' => 'Photo Product',
            'price' => '125.50',
            'stock_quantity' => 4,
            'image' => UploadedFile::fake()->image('product.png', 1600, 900),
        ])->assertRedirect();

        $product = Product::firstOrFail();
        $this->assertStringStartsWith('products/', $product->image);
        $this->assertStringEndsWith('.webp', $product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_stale_stock_edit_cannot_restore_stock_sold_concurrently(): void
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $product = Product::create(['name' => 'Concurrent Item', 'price' => '10.00', 'stock_quantity' => 10]);
        $product->decrement('stock_quantity', 2);

        $this->actingAs($staff)->put(route('products.update', $product), [
            'name' => 'Concurrent Item Updated',
            'price' => '10.00',
            'stock_quantity' => 10,
            'original_stock' => 10,
        ])->assertSessionHasErrors('stock_quantity');

        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertSame('Concurrent Item', $product->fresh()->name);
    }

    public function test_referenced_records_and_current_account_cannot_be_deleted(): void
    {
        $staff = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create();
        $product = Product::create(['name' => 'Referenced', 'price' => '10.00', 'stock_quantity' => 4]);
        $customer = Customer::create(['full_name' => 'Referenced Customer', 'email' => 'ref@example.test']);
        Sale::create(['product_id' => $product->id, 'customer_id' => $customer->id, 'sold_by' => $other->id, 'quantity' => 1, 'total_price' => '10.00']);

        $this->actingAs($staff)->delete(route('products.destroy', $product))->assertSessionHas('error');
        $this->actingAs($staff)->delete(route('customers.destroy', $customer))->assertSessionHas('error');
        $this->actingAs($staff)->delete(route('staff.destroy', $other))->assertSessionHas('error');
        $this->actingAs($staff)->delete(route('staff.destroy', $staff))->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id]);
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_staff_password_update_is_optional_and_hashes_new_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['username' => 'cashier', 'password' => 'Original123!']);
        $oldHash = $staff->password;

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'username' => 'cashier', 'email' => $staff->email, 'full_name' => 'Updated Cashier', 'role' => 'cashier', 'password' => '', 'password_confirmation' => '',
        ])->assertRedirect();
        $this->assertSame($oldHash, $staff->fresh()->password);

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'username' => 'cashier', 'email' => $staff->email, 'full_name' => 'Updated Cashier', 'role' => 'cashier', 'password' => 'Replacement123!', 'password_confirmation' => 'Replacement123!',
        ])->assertRedirect();
        $this->assertTrue(password_verify('Replacement123!', $staff->fresh()->password));
    }
}
