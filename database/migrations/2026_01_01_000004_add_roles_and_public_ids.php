<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('public_id')->nullable()->unique()->after('id');
            $table->string('role', 20)->default('cashier')->after('full_name');
            $table->index('role');
        });

        foreach (['products', 'customers', 'sales'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->uuid('public_id')->nullable()->unique()->after('id');
            });
        }

        foreach (['users', 'products', 'customers', 'sales'] as $tableName) {
            DB::table($tableName)->whereNull('public_id')->orderBy('id')->eachById(function (object $record) use ($tableName) {
                DB::table($tableName)->where('id', $record->id)->update(['public_id' => (string) Str::uuid()]);
            });
        }

        $firstAdminId = DB::table('users')->where('username', 'admin')->value('id')
            ?? DB::table('users')->min('id');
        if ($firstAdminId) {
            DB::table('users')->where('id', $firstAdminId)->update(['role' => 'admin']);
        }
    }

    public function down(): void
    {
        foreach (['sales', 'customers', 'products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropUnique($tableName.'_public_id_unique');
                $table->dropColumn('public_id');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropUnique('users_public_id_unique');
            $table->dropColumn(['public_id', 'role']);
        });
    }
};
