<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 18, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->unsignedSmallInteger('interval_months');
            // The intended day of the month, kept apart from next_due_date: a
            // bill on the 31st falls on Feb 28 but must return to the 31st.
            $table->unsignedTinyInteger('due_day');
            $table->date('next_due_date')->index();
            $table->unsignedSmallInteger('remind_days_before')->default(7);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            // The due date a reminder notification already went out for.
            $table->date('notified_due_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_reminder_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_reminder_id')->constrained('payment_reminders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('due_date');
            $table->date('paid_on');
            $table->decimal('amount', 18, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->string('note', 500)->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminder_payments');
        Schema::dropIfExists('payment_reminders');
    }
};
