<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_shares', function (Blueprint $table) {
            $table->id();
            $table->string('shareable_type');
            $table->unsignedBigInteger('shareable_id');
            $table->enum('grantee_type', ['user', 'role', 'everyone']);
            $table->unsignedBigInteger('grantee_id')->nullable();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->index(['shareable_type', 'shareable_id']);
            $table->index(['grantee_type', 'grantee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_shares');
    }
};
