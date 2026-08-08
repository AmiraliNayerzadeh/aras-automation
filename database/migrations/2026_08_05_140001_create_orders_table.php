<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->foreignId('business_partner_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->char('currency', 3)->default('USD');
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('current_stage_lookup_value_id')->nullable()
                ->constrained('lookup_values')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('current_stage_since')->nullable();
            $table->boolean('is_closed')->default(false);
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
