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
        if (! is_string($password) || strlen($password) < 8 || strlen($password) > 72) {
            throw new \RuntimeException('Set DEMO_STAFF_PASSWORD to an 8–72 character value before seeding.');
        }

        foreach ([
            ['username' => 'admin', 'full_name' => 'Alex Rivera'],
            ['username' => 'cashier', 'full_name' => 'Jamie Santos'],
        ] as $staff) {
            User::updateOrCreate(['username' => $staff['username']], $staff + ['password' => Hash::make($password), 'avatar' => null]);
        }

        foreach ([
            ['name' => 'Arabica Coffee Beans 500g', 'price' => '385.00', 'stock_quantity' => 18],
            ['name' => 'Classic Milk Tea', 'price' => '95.00', 'stock_quantity' => 25],
            ['name' => 'Butter Croissant', 'price' => '78.00', 'stock_quantity' => 12],
            ['name' => 'Tuna Sandwich', 'price' => '125.00', 'stock_quantity' => 8],
            ['name' => 'Sparkling Water', 'price' => '55.00', 'stock_quantity' => 30],
            ['name' => 'Chocolate Cookie', 'price' => '45.00', 'stock_quantity' => 4],
            ['name' => 'Ceramic Mug', 'price' => '249.00', 'stock_quantity' => 6],
            ['name' => 'Canvas Tote Bag', 'price' => '199.00', 'stock_quantity' => 3],
            ['name' => 'Cold Brew Bottle', 'price' => '145.00', 'stock_quantity' => 10],
            ['name' => 'Seasonal Gift Box', 'price' => '799.00', 'stock_quantity' => 0],
        ] as $product) {
            Product::updateOrCreate(['name' => $product['name']], $product + ['image' => null]);
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
