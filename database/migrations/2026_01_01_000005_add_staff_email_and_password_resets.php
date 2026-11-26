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
            $table->string('email', 150)->nullable()->unique()->after('username');
        });

        DB::table('users')->whereNull('email')->orderBy('id')->eachById(function (object $user) {
            $base = Str::lower((string) preg_replace('/[^A-Za-z0-9._-]/', '', $user->username)) ?: 'staff';
            DB::table('users')->where('id', $user->id)->update([
                'email' => in_array($user->username, ['admin', 'cashier'], true)
                    ? $user->username.'@cafeflow.test'
                    : $base.'.'.$user->id.'@cafeflow.test',
            ]);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_email_unique');
            $table->dropColumn('email');
        });
    }
};
