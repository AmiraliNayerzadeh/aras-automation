<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_labelables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lookup_value_id')->constrained('lookup_values')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('labelable_type', 20);
            $table->unsignedBigInteger('labelable_id');
            $table->timestamps();
            $table->unique(['lookup_value_id', 'labelable_type', 'labelable_id'], 'file_labelables_unique');
            $table->index(['labelable_type', 'labelable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_labelables');
    }
};
