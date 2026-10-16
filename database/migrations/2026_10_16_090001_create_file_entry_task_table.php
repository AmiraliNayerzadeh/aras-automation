<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_entry_task', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('file_entry_id')->constrained('file_entries')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('attached_by_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->unique(['task_id', 'file_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_entry_task');
    }
};
