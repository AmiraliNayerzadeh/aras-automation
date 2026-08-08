<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->foreignId('category_id')->nullable()
                ->constrained('product_categories')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()
                ->constrained('product_brands')->cascadeOnUpdate()->nullOnDelete();
            $table->string('unit')->nullable();
            $table->unsignedInteger('package_quantity')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
