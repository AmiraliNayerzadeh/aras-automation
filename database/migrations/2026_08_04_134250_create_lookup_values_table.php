<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lookup_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lookup_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code');
            $table->json('label');
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['lookup_type_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lookup_values');
    }
};
