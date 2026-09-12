<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('is_confidential')->default(false)->after('owner_id');
        });

        Schema::table('file_entries', function (Blueprint $table) {
            $table->boolean('is_confidential')->default(false)->after('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('is_confidential');
        });

        Schema::table('file_entries', function (Blueprint $table) {
            $table->dropColumn('is_confidential');
        });
    }
};
