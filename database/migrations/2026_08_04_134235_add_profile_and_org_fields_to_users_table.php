<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_number')->nullable()->unique()->after('name');
            $table->string('national_id')->nullable()->after('employee_number');
            $table->date('date_of_birth')->nullable()->after('national_id');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('mobile')->nullable()->after('gender');
            $table->string('emergency_mobile')->nullable()->after('mobile');
            $table->string('profile_photo_path')->nullable()->after('emergency_mobile');
            $table->string('signature_path')->nullable()->after('profile_photo_path');
            $table->text('address')->nullable()->after('signature_path');
            $table->date('hire_date')->nullable()->after('address');
            $table->string('employment_type')->nullable()->after('hire_date');
            $table->string('status')->default('active')->after('employment_type');

            $table->foreignId('company_id')->nullable()->after('status')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('company_id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('branch_id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('department_id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->after('unit_id')
                ->constrained()->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('manager_id')->nullable()->after('position_id')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('secondary_manager_id')->nullable()->after('manager_id')
                ->constrained('users')->cascadeOnUpdate()->nullOnDelete();

            $table->string('org_code')->nullable()->unique()->after('secondary_manager_id');
            $table->date('activity_start_date')->nullable()->after('org_code');
            $table->string('locale', 5)->default('en')->after('activity_start_date');

            $table->timestamp('last_login_at')->nullable()->after('locale');
            $table->unsignedInteger('login_count')->default(0)->after('last_login_at');
            $table->string('last_login_ip')->nullable()->after('login_count');
            $table->string('last_login_device')->nullable()->after('last_login_ip');

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropConstrainedForeignId('secondary_manager_id');
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('position_id');

            $table->dropColumn([
                'employee_number',
                'national_id',
                'date_of_birth',
                'gender',
                'mobile',
                'emergency_mobile',
                'profile_photo_path',
                'signature_path',
                'address',
                'hire_date',
                'employment_type',
                'status',
                'org_code',
                'activity_start_date',
                'locale',
                'last_login_at',
                'login_count',
                'last_login_ip',
                'last_login_device',
            ]);

            $table->dropSoftDeletes();
        });
    }
};
