<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()
                ->constrained('folders')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title')->nullable();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('share_token')->nullable()->unique();
            $table->timestamp('share_token_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_entries');
    }
};
