<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_id', 'user_id']);
        });

        // Carry forward any existing single-assignee data before dropping the column.
        DB::table('tasks')->whereNotNull('assigned_to_id')->get(['id', 'assigned_to_id'])->each(function ($task) {
            DB::table('task_user')->insert([
                'task_id' => $task->id,
                'user_id' => $task->assigned_to_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_to_id')->nullable()
                ->after('priority_lookup_value_id')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
        });

        DB::table('task_user')->orderBy('id')->get()->each(function ($pivot) {
            DB::table('tasks')->where('id', $pivot->task_id)->update(['assigned_to_id' => $pivot->user_id]);
        });

        Schema::dropIfExists('task_user');
    }
};
