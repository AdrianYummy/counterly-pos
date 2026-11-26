<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = config('counterly.demo_password');
        if (! is_string($password)
            || strlen($password) < 10
            || strlen($password) > 72
            || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/\d/', $password)
            || ! preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new \RuntimeException('Set DEMO_STAFF_PASSWORD to a 10–72 character value containing uppercase, lowercase, number, and symbol characters before seeding.');
        }

        foreach ([
            ['username' => 'admin', 'email' => 'admin@cafeflow.test', 'full_name' => 'Alex Rivera', 'role' => 'admin'],
            ['username' => 'cashier', 'email' => 'cashier@cafeflow.test', 'full_name' => 'Jamie Santos', 'role' => 'cashier'],
        ] as $staff) {
            User::updateOrCreate(['username' => $staff['username']], $staff + ['password' => Hash::make($password), 'avatar' => null]);
        }

        foreach ([
            ['name' => 'Arabica Coffee Beans 500g', 'price' => '385.00', 'stock_quantity' => 18, 'image' => 'products/catalog-arabica-coffee-beans.jpg'],
            ['name' => 'Classic Milk Tea', 'price' => '95.00', 'stock_quantity' => 25, 'image' => 'products/catalog-classic-milk-tea.jpg'],
            ['name' => 'Butter Croissant', 'price' => '78.00', 'stock_quantity' => 12, 'image' => 'products/catalog-butter-croissant.jpg'],
            ['name' => 'Tuna Sandwich', 'price' => '125.00', 'stock_quantity' => 8, 'image' => 'products/catalog-tuna-sandwich.jpg'],
            ['name' => 'Sparkling Water', 'price' => '55.00', 'stock_quantity' => 30, 'image' => 'products/catalog-sparkling-water.jpg'],
            ['name' => 'Chocolate Cookie', 'price' => '45.00', 'stock_quantity' => 4, 'image' => 'products/catalog-chocolate-cookie.jpg'],
            ['name' => 'Banana Bread', 'price' => '150.00', 'stock_quantity' => 4, 'image' => 'products/catalog-banana-bread.jpg'],
            ['name' => 'Ceramic Mug', 'price' => '249.00', 'stock_quantity' => 6, 'image' => 'products/catalog-ceramic-mug.jpg'],
            ['name' => 'Canvas Tote Bag', 'price' => '199.00', 'stock_quantity' => 3, 'image' => 'products/catalog-canvas-tote-bag.jpg'],
            ['name' => 'Cold Brew Bottle', 'price' => '145.00', 'stock_quantity' => 10, 'image' => 'products/catalog-cold-brew-bottle.jpg'],
            ['name' => 'Seasonal Gift Box', 'price' => '799.00', 'stock_quantity' => 0, 'image' => 'products/catalog-seasonal-gift-box.jpg'],
        ] as $product) {
            $catalogImage = $product['image'];
            unset($product['image']);

            $storedProduct = Product::firstOrNew(['name' => $product['name']]);
            $storedProduct->fill($product);

            if (! $storedProduct->image) {
                $storedProduct->image = $catalogImage;
            }

            $storedProduct->save();
        }

        foreach ([
            ['full_name' => 'Mika dela Cruz', 'email' => 'mika.delacruz@example.test', 'phone' => '+63 917 000 1001'],
            ['full_name' => 'Noah Villanueva', 'email' => 'noah.v@example.test', 'phone' => '0918 000 1002'],
            ['full_name' => 'Sofia Lim', 'email' => 'sofia.lim@example.test', 'phone' => null],
            ['full_name' => 'Ethan Reyes', 'email' => 'ethan.reyes@example.test', 'phone' => '+63 919 000 1004'],
            ['full_name' => 'Amara Garcia', 'email' => 'amara.garcia@example.test', 'phone' => '0920 000 1005'],
        ] as $customer) {
            Customer::updateOrCreate(['email' => $customer['email']], $customer);
        }
    }
}
