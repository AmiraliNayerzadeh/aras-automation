<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\EmploymentType;
use App\Enums\UserStatus;
use App\Models\Hr\WorkShift;
use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Organization\Unit;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password',
    'employee_number', 'national_id', 'date_of_birth', 'gender', 'mobile', 'emergency_mobile',
    'profile_photo_path', 'signature_path', 'address', 'hire_date', 'employment_type', 'is_remote', 'status',
    'company_id', 'branch_id', 'department_id', 'unit_id', 'position_id',
    'manager_id', 'secondary_manager_id', 'org_code', 'activity_start_date', 'locale',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsActivity, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'activity_start_date' => 'date',
            'last_login_at' => 'datetime',
            'employment_type' => EmploymentType::class,
            'status' => UserStatus::class,
            'is_remote' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('user');
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : asset('assets/user-default.jpg'));
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function secondaryManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'secondary_manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class);
    }
}
