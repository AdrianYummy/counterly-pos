<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('product_id')->index();
            $table->integer('customer_id')->nullable()->index();
            $table->integer('sold_by')->index();
            $table->integer('quantity');
            $table->decimal('total_price', 10, 2);
            $table->dateTime('created_at')->index();

            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete();
            $table->foreign('sold_by')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
