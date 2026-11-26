<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_products_receive_local_stock_photos_without_replacing_custom_uploads(): void
    {
        config()->set('counterly.demo_password', 'Testing123!');

        $this->seed(DatabaseSeeder::class);

        $catalog = [
            'Arabica Coffee Beans 500g' => 'products/catalog-arabica-coffee-beans.jpg',
            'Classic Milk Tea' => 'products/catalog-classic-milk-tea.jpg',
            'Butter Croissant' => 'products/catalog-butter-croissant.jpg',
            'Tuna Sandwich' => 'products/catalog-tuna-sandwich.jpg',
            'Sparkling Water' => 'products/catalog-sparkling-water.jpg',
            'Chocolate Cookie' => 'products/catalog-chocolate-cookie.jpg',
            'Banana Bread' => 'products/catalog-banana-bread.jpg',
            'Ceramic Mug' => 'products/catalog-ceramic-mug.jpg',
            'Canvas Tote Bag' => 'products/catalog-canvas-tote-bag.jpg',
            'Cold Brew Bottle' => 'products/catalog-cold-brew-bottle.jpg',
            'Seasonal Gift Box' => 'products/catalog-seasonal-gift-box.jpg',
        ];

        foreach ($catalog as $name => $image) {
            $this->assertSame($image, Product::where('name', $name)->value('image'));
            $this->assertFileExists(Storage::disk('public')->path($image));
        }

        Product::where('name', 'Classic Milk Tea')->update(['image' => 'products/custom-milk-tea.webp']);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(
            'products/custom-milk-tea.webp',
            Product::where('name', 'Classic Milk Tea')->value('image'),
        );
    }
}
