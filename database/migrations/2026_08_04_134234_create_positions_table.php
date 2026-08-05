<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->string('code');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['department_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
