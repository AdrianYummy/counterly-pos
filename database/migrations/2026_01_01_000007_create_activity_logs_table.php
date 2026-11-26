<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('actor_id')->nullable()->index();
            $table->string('actor_name', 100);
            $table->string('subject_type', 20)->index();
            $table->uuid('subject_public_id');
            $table->string('subject_name', 150);
            $table->json('changes');
            $table->dateTime('created_at')->index();

            $table->foreign('actor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
