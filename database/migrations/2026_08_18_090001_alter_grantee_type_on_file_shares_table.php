<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-only syntax; SQLite (the test database) has no enum to alter.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE file_shares MODIFY grantee_type ENUM('user', 'role', 'everyone', 'department', 'position') NOT NULL");
    }

    public function down(): void
    {
        // MySQL-only syntax; SQLite (the test database) has no enum to alter.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE file_shares MODIFY grantee_type ENUM('user', 'role', 'everyone') NOT NULL");
    }
};
