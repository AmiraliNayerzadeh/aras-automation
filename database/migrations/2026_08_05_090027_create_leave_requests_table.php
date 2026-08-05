<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('lookup_value_id')->constrained('lookup_values')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('substitute_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->date('from_date');
            $table->date('to_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('day_count', 5, 2);
            $table->text('description')->nullable();

            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
