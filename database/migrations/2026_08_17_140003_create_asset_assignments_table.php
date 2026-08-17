<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('location')->default('on_site');
            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();

            $table->foreignId('assigned_by_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('returned_by_id')->nullable()
                ->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
