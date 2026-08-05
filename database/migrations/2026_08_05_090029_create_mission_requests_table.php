<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mission_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lookup_value_id')->constrained('lookup_values')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('destination');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('purpose');
            $table->string('outbound_transport')->nullable();
            $table->string('return_transport')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('actual_cost', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->text('mission_report')->nullable();

            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_requests');
    }
};
