<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('face_device_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->string('employee_no')->nullable()->index();
            $table->string('person_name')->nullable();

            $table->string('device_serial')->nullable()->index();
            $table->string('device_ip')->nullable();

            $table->string('major_event')->nullable();
            $table->string('minor_event')->nullable();
            $table->string('verify_mode')->nullable();
            $table->string('attendance_status')->nullable();

            $table->timestamp('event_time')->nullable()->index();
            $table->string('picture_path')->nullable();

            $table->json('raw_payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('face_device_events');
    }
};
