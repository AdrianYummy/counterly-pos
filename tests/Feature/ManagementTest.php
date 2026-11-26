<?php

namespace Tests\Feature;

use App\Mail\WelcomeUserMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_staff_receives_a_welcome_email_without_a_password(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('staff.store'), [
            'username' => 'new.barista',
            'email' => 'new.barista@example.test',
            'full_name' => 'New Barista',
            'role' => 'cashier',
            'password' => 'Temporary123!',
            'password_confirmation' => 'Temporary123!',
        ])->assertRedirect();

        $staff = User::where('username', 'new.barista')->firstOrFail();
        Mail::assertSent(WelcomeUserMail::class, function (WelcomeUserMail $mail) use ($staff) {
            return $mail->hasTo($staff->email)
                && $mail->user->is($staff)
                && ! str_contains($mail->render(), 'Temporary123!');
        });
    }

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

    public function test_customer_and_staff_edits_are_recorded_without_password_secrets(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'full_name' => 'Audit Administrator']);
        $cashier = User::factory()->create(['role' => 'cashier', 'full_name' => 'Editing Cashier']);
        $customer = Customer::create(['full_name' => 'Original Customer', 'email' => 'original@example.test', 'phone' => null]);

        $this->actingAs($cashier)->put(route('customers.update', $customer), [
            'full_name' => 'Updated Customer',
            'email' => 'updated@example.test',
            'phone' => '+63 912 345 6789',
        ])->assertRedirect();

        $this->actingAs($admin)->put(route('staff.update', $cashier), [
            'username' => $cashier->username,
            'email' => $cashier->email,
            'full_name' => 'Updated Cashier',
            'role' => 'cashier',
            'password' => 'Replacement123!',
            'password_confirmation' => 'Replacement123!',
        ])->assertRedirect();

        $customerLog = ActivityLog::where('subject_type', 'customer')->firstOrFail();
        $this->assertSame('Editing Cashier', $customerLog->actor_name);
        $this->assertSame('updated', $customerLog->action);
        $this->assertSame('Original Customer', $customerLog->changes['full_name']['from']);
        $this->assertSame('Updated Customer', $customerLog->changes['full_name']['to']);

        $staffLog = ActivityLog::where('subject_type', 'staff')->firstOrFail();
        $this->assertSame('updated', $staffLog->action);
        $this->assertSame('Updated securely', $staffLog->changes['password']['to']);
        $this->assertStringNotContainsString('Replacement123!', json_encode($staffLog->changes, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString($cashier->fresh()->password, json_encode($staffLog->changes, JSON_THROW_ON_ERROR));

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent record activity')
            ->assertSee('Editing Cashier')
            ->assertSee('Updated Customer')
            ->assertSee('Updated securely');
    }

    public function test_accountability_covers_product_stock_and_record_lifecycle_changes(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'full_name' => 'Accountability Admin']);

        $this->actingAs($admin)->post(route('products.store'), [
            'name' => 'Audited Coffee',
            'price' => '100.00',
            'stock_quantity' => 5,
        ])->assertRedirect();
        $product = Product::where('name', 'Audited Coffee')->firstOrFail();

        $this->actingAs($admin)->put(route('products.update', $product), [
            'name' => 'Audited Coffee',
            'price' => '110.00',
            'stock_quantity' => 18,
            'original_stock' => 5,
        ])->assertRedirect();

        $stockLog = ActivityLog::where('subject_type', 'product')->where('action', 'updated')->firstOrFail();
        $this->assertSame('Accountability Admin', $stockLog->actor_name);
        $this->assertSame('5', $stockLog->changes['stock_quantity']['from']);
        $this->assertSame('18', $stockLog->changes['stock_quantity']['to']);
        $this->assertSame('100.00', $stockLog->changes['price']['from']);
        $this->assertSame('110.00', $stockLog->changes['price']['to']);

        $this->actingAs($admin)->post(route('customers.store'), [
            'full_name' => 'Lifecycle Customer',
            'email' => 'lifecycle.customer@example.test',
            'phone' => '',
        ])->assertRedirect();
        $customer = Customer::where('email', 'lifecycle.customer@example.test')->firstOrFail();

        $this->actingAs($admin)->post(route('staff.store'), [
            'username' => 'lifecycle.cashier',
            'email' => 'lifecycle.cashier@example.test',
            'full_name' => 'Lifecycle Cashier',
            'role' => 'cashier',
            'password' => 'SecureLifecycle123!',
            'password_confirmation' => 'SecureLifecycle123!',
        ])->assertRedirect();
        $staff = User::where('username', 'lifecycle.cashier')->firstOrFail();

        $this->actingAs($admin)->delete(route('products.destroy', $product))->assertRedirect();
        $this->actingAs($admin)->delete(route('customers.destroy', $customer))->assertRedirect();
        $this->actingAs($admin)->delete(route('staff.destroy', $staff))->assertRedirect();

        foreach (['product', 'customer', 'staff'] as $subjectType) {
            $this->assertDatabaseHas('activity_logs', ['subject_type' => $subjectType, 'action' => 'created', 'actor_id' => $admin->id]);
            $this->assertDatabaseHas('activity_logs', ['subject_type' => $subjectType, 'action' => 'deleted', 'actor_id' => $admin->id]);
        }

        $staffCreation = ActivityLog::where('subject_type', 'staff')->where('action', 'created')->firstOrFail();
        $serializedChanges = json_encode($staffCreation->changes, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('SecureLifecycle123!', $serializedChanges);
        $this->assertStringNotContainsString($staff->password, $serializedChanges);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Product / stock')
            ->assertSee('Accountability Admin')
            ->assertSee('Stock Quantity')
            ->assertSee('deleted');
    }

    public function test_dashboard_shows_seven_day_sales_analytics_and_groups_checkout_lines(): void
    {
        $now = CarbonImmutable::parse('2026-11-27 12:00:00', 'Asia/Manila');
        $this->travelTo($now);
        $admin = User::factory()->create(['role' => 'admin']);
        $coffee = Product::create(['name' => 'Analytics Coffee', 'price' => '100.00', 'stock_quantity' => 20]);
        $pastry = Product::create(['name' => 'Analytics Pastry', 'price' => '50.00', 'stock_quantity' => 20]);
        $todayTransaction = (string) Str::uuid();

        Sale::forceCreate([
            'transaction_id' => $todayTransaction,
            'product_id' => $coffee->id,
            'sold_by' => $admin->id,
            'quantity' => 2,
            'total_price' => '200.00',
            'created_at' => $now->utc(),
        ]);
        Sale::forceCreate([
            'transaction_id' => $todayTransaction,
            'product_id' => $pastry->id,
            'sold_by' => $admin->id,
            'quantity' => 1,
            'total_price' => '50.00',
            'created_at' => $now->utc(),
        ]);
        Sale::forceCreate([
            'transaction_id' => (string) Str::uuid(),
            'product_id' => $coffee->id,
            'sold_by' => $admin->id,
            'quantity' => 1,
            'total_price' => '100.00',
            'created_at' => $now->subDays(2)->utc(),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sales trend')
            ->assertSee('Top products')
            ->assertSee('Analytics Coffee')
            ->assertViewHas('todaySales', 250.0)
            ->assertViewHas('todayTransactionCount', 1)
            ->assertViewHas('sevenDayRevenue', 350.0)
            ->assertViewHas('salesTrend', fn ($trend) => $trend->last()['revenue'] === 250.0 && $trend->last()['transactions'] === 1)
            ->assertViewHas('topProducts', fn ($products) => $products->first()['name'] === 'Analytics Coffee' && $products->first()['units'] === 3);
    }

    public function test_image_editors_appear_before_product_and_staff_fields(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'cashier']);
        $product = Product::create(['name' => 'Photo Order Product', 'price' => '95.00', 'stock_quantity' => 4]);

        $this->actingAs($admin)->get(route('products.edit', $product))
            ->assertOk()
            ->assertSeeInOrder(['Product photo', 'Product name']);

        $this->actingAs($admin)->get(route('staff.edit', $staff))
            ->assertOk()
            ->assertSeeInOrder(['Profile photo', 'Username']);
    }

    public function test_large_record_lists_are_paginated_ten_at_a_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (range(1, 12) as $number) {
            Product::create(['name' => 'Paged Product '.$number, 'price' => '50.00', 'stock_quantity' => 20]);
            Customer::create(['full_name' => 'Paged Customer '.$number, 'email' => 'customer'.$number.'@example.test']);
        }

        User::factory()->count(11)->create(['role' => 'cashier']);
        $product = Product::firstOrFail();
        $customer = Customer::firstOrFail();

        foreach (range(1, 12) as $number) {
            Sale::create([
                'product_id' => $product->id,
                'customer_id' => $customer->id,
                'sold_by' => $admin->id,
                'quantity' => 1,
                'total_price' => '50.00',
            ]);
            ActivityLog::create([
                'actor_id' => $admin->id,
                'actor_name' => $admin->full_name,
                'subject_type' => 'customer',
                'subject_public_id' => $customer->public_id,
                'subject_name' => 'Audit Record '.$number,
                'changes' => ['full_name' => ['from' => 'Before', 'to' => 'After '.$number]],
            ]);
        }

        foreach ([
            ['route' => 'products.index', 'key' => 'products', 'page' => 'page'],
            ['route' => 'customers.index', 'key' => 'customers', 'page' => 'page'],
            ['route' => 'staff.index', 'key' => 'staffMembers', 'page' => 'page'],
            ['route' => 'sales.index', 'key' => 'sales', 'page' => 'page'],
            ['route' => 'dashboard', 'key' => 'recentEdits', 'page' => 'activity_page'],
        ] as $listing) {
            $this->actingAs($admin)->get(route($listing['route']))
                ->assertOk()
                ->assertViewHas($listing['key'], fn ($paginator) => $paginator->count() === 10 && $paginator->total() === 12)
                ->assertSee('aria-label="Go to page 2"', false);

            $this->actingAs($admin)->get(route($listing['route'], [$listing['page'] => 2]))
                ->assertOk()
                ->assertViewHas($listing['key'], fn ($paginator) => $paginator->count() === 2 && $paginator->currentPage() === 2);
        }
    }

    public function test_duplicate_product_staff_and_customer_names_are_rejected_case_insensitively(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create(['name' => 'House Blend', 'price' => '120.00', 'stock_quantity' => 10]);
        $otherProduct = Product::create(['name' => 'Seasonal Roast', 'price' => '150.00', 'stock_quantity' => 8]);
        $staff = User::factory()->create(['role' => 'cashier', 'full_name' => 'Jamie Barista']);
        $customer = Customer::create(['full_name' => 'Maria Customer', 'email' => 'maria.customer@example.test']);
        Customer::create(['full_name' => 'Lea Customer', 'email' => 'lea.customer@example.test']);

        $this->actingAs($admin)->post(route('products.store'), [
            'name' => '  house   blend  ',
            'price' => '130.00',
            'stock_quantity' => 5,
        ])->assertSessionHasErrors(['name' => 'A product with this name already exists.']);

        $this->actingAs($admin)->put(route('products.update', $otherProduct), [
            'name' => 'HOUSE BLEND',
            'price' => '150.00',
            'stock_quantity' => 8,
            'original_stock' => 8,
        ])->assertSessionHasErrors(['name' => 'A product with this name already exists.']);

        $this->actingAs($admin)->post(route('staff.store'), [
            'username' => 'duplicate.name',
            'email' => 'duplicate.name@example.test',
            'full_name' => '  jamie   barista ',
            'role' => 'cashier',
            'password' => 'DuplicateName123!',
            'password_confirmation' => 'DuplicateName123!',
        ])->assertSessionHasErrors(['full_name' => 'A staff account with this full name already exists.']);

        $this->actingAs($admin)->put(route('staff.update', $staff), [
            'username' => $staff->username,
            'email' => $staff->email,
            'full_name' => 'Jamie Barista',
            'role' => 'cashier',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect();

        $this->actingAs($admin)->post(route('customers.store'), [
            'full_name' => '  maria   customer ',
            'email' => 'different.customer@example.test',
            'phone' => '',
        ])->assertSessionHasErrors(['full_name' => 'A customer with this full name already exists.']);

        $this->actingAs($admin)->post(route('customers.store'), [
            'full_name' => 'Different Customer',
            'email' => 'MARIA.CUSTOMER@EXAMPLE.TEST',
            'phone' => '',
        ])->assertSessionHasErrors(['email' => 'A customer with this email address already exists.']);

        $this->actingAs($admin)->put(route('customers.update', $customer), [
            'full_name' => 'Maria Customer',
            'email' => 'maria.customer@example.test',
            'phone' => '',
        ])->assertRedirect();

        $this->assertSame(2, Product::count());
        $this->assertSame('Seasonal Roast', $otherProduct->fresh()->name);
        $this->assertSame(2, User::count());
        $this->assertSame(2, Customer::count());
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'House Blend']);
    }

    public function test_database_unique_indexes_reject_exact_duplicate_names(): void
    {
        Product::create(['name' => 'Database Unique Product', 'price' => '10.00', 'stock_quantity' => 1]);

        try {
            Product::create(['name' => 'Database Unique Product', 'price' => '20.00', 'stock_quantity' => 2]);
            $this->fail('The product name unique index did not reject an exact duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('products', 1);
        }

        User::factory()->create(['full_name' => 'Database Unique Staff']);

        try {
            User::factory()->create(['full_name' => 'Database Unique Staff']);
            $this->fail('The staff full-name unique index did not reject an exact duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('users', 1);
        }

        Customer::create(['full_name' => 'Database Unique Customer', 'email' => 'database.unique@example.test']);

        try {
            Customer::create(['full_name' => 'Database Unique Customer', 'email' => 'different@example.test']);
            $this->fail('The customer full-name unique index did not reject an exact duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('customers', 1);
        }

        try {
            Customer::create(['full_name' => 'Different Customer', 'email' => 'database.unique@example.test']);
            $this->fail('The customer email unique index did not reject an exact duplicate.');
        } catch (QueryException) {
            $this->assertDatabaseCount('customers', 1);
        }
    }
}
