<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('email', 'admin@counterly.test')->update(['email' => 'admin@cafeflow.test']);
        DB::table('users')->where('email', 'cashier@counterly.test')->update(['email' => 'cashier@cafeflow.test']);
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@cafeflow.test')->update(['email' => 'admin@counterly.test']);
        DB::table('users')->where('email', 'cashier@cafeflow.test')->update(['email' => 'cashier@counterly.test']);
    }
};
