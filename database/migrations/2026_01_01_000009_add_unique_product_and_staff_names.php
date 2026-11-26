<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('full_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_full_name_unique');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_name_unique');
        });
    }
};
