<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchandising_visits', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('folder_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('location_accuracy')->nullable()->after('longitude');
            $table->unsignedInteger('distance_m')->nullable()->after('location_accuracy');
            // verified | no_location | store_unmapped
            $table->string('location_status', 20)->nullable()->after('distance_m')->index();
            $table->index('created_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('merchandising_visits_seen_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('merchandising_visits_seen_at'));

        Schema::table('merchandising_visits', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['location_status']);
            $table->dropColumn(['latitude', 'longitude', 'location_accuracy', 'distance_m', 'location_status']);
        });
    }
};
