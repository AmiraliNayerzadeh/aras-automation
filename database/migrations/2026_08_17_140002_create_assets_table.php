<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('title');
            $table->foreignId('category_id')->nullable()
                ->constrained('asset_categories')->cascadeOnUpdate()->nullOnDelete();

            $table->string('serial_number')->nullable();
            $table->text('description')->nullable();

            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();

            $table->string('status')->default('in_storage');

            $table->foreignId('current_holder_id')->nullable()
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->string('image_path')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
