<?php

use App\Models\Finance\PaymentReminder;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\User;
use App\Services\SettingsService;
use App\Support\DashboardShortcuts;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    app(SettingsService::class)->set('timezone', 'Asia/Tehran');

    $this->employee = User::factory()->create(['status' => 'active', 'name' => 'Sara']);
    $this->employee->assignRole('employee');

    $this->admin = User::factory()->create(['status' => 'active', 'name' => 'Boss']);
    $this->admin->assignRole('super-admin');

    $this->tiles = fn (User $user) => collect(app(DashboardShortcuts::class)->for($user))->pluck('key')->all();
});

afterEach(fn () => Carbon::setTestNow());

test('the dashboard welcomes the user by name', function () {
    $this->actingAs($this->employee)->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('dashboard.welcome', ['name' => 'Sara']), false);
});

test('an employee only gets the shortcuts their role allows', function () {
    expect(($this->tiles)($this->employee))->toBe(['leave', 'mission', 'approvals', 'task', 'files']);

    $this->actingAs($this->employee)->get(route('dashboard'))
        ->assertSee(route('leave-requests.create'))
        ->assertDontSee(route('admin.users.create'))
        ->assertDontSee(route('admin.payment-reminders.index'));
});

test('a warehouse user gets warehouse shortcuts and no leave approval inbox', function () {
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('warehouse');

    expect(($this->tiles)($user))->toContain('stock', 'assets', 'leave')->not->toContain('approvals', 'order', 'user');
});

test('every shortcut opens without an error for someone allowed to see it', function () {
    foreach (app(DashboardShortcuts::class)->for($this->admin) as $shortcut) {
        $this->actingAs($this->admin)->get($shortcut['url'])->assertOk();
    }

    expect(($this->tiles)($this->admin))->toHaveCount(14);
});

test('company figures are only for people who manage users or structure', function () {
    $this->actingAs($this->employee)->get(route('dashboard'))->assertViewHas('stats', null)->assertDontSee(__('app.stat_users'));
    $this->actingAs($this->admin)->get(route('dashboard'))->assertSee(__('app.stat_users'));
});

test('the greeting follows the company timezone, not UTC', function (string $utc, string $expected) {
    Carbon::setTestNow($utc);

    $this->actingAs($this->employee)->get(route('dashboard'))->assertViewHas('greeting', $expected);
})->with([
    '04:00 UTC is 07:30 in Tehran' => ['2026-11-14 04:00:00', 'morning'],
    '10:00 UTC is 13:30 in Tehran' => ['2026-11-14 10:00:00', 'afternoon'],
    '14:00 UTC is 17:30 in Tehran' => ['2026-11-14 14:00:00', 'evening'],
    '20:00 UTC is 23:30 in Tehran' => ['2026-11-14 20:00:00', 'night'],
]);

test('Persian users see the Solar Hijri date', function () {
    Carbon::setTestNow('2026-11-14 08:00:00');

    $this->actingAs($this->employee)->withSession(['locale' => 'fa'])->get(route('dashboard'))
        ->assertViewHas('todayLabel', fn (string $label) => str_contains($label, 'آبان') && str_contains($label, '۱۴۰۵'));
});

test("today's check-in time is shown once the device has seen the user", function () {
    Carbon::setTestNow('2026-11-14 06:00:00');

    $this->actingAs($this->employee)->get(route('dashboard'))->assertViewHas('checkInToday', null);

    FaceDeviceEvent::create(['user_id' => $this->employee->id, 'employee_no' => '1', 'event_time' => '2026-11-14 08:12:00']);
    FaceDeviceEvent::create(['user_id' => $this->employee->id, 'employee_no' => '1', 'event_time' => '2026-11-13 17:00:00']);

    $this->actingAs($this->employee)->get(route('dashboard'))
        ->assertViewHas('checkInToday', '08:12')
        ->assertSee(__('dashboard.checked_in_at', ['time' => '08:12']));
});

test('the payments tile counts what is due', function () {
    Carbon::setTestNow('2026-11-14 08:00:00');
    PaymentReminder::create(['title' => 'Server', 'interval_months' => 1, 'due_day' => 16, 'next_due_date' => '2026-11-16', 'remind_days_before' => 7]);

    $tile = collect(app(DashboardShortcuts::class)->for($this->admin))->firstWhere('key', 'payments');

    expect($tile['badge'])->toBe(1);
});

test('a user without any shortcut still gets a working dashboard', function () {
    $user = User::factory()->create(['status' => 'active']);

    expect(($this->tiles)($user))->toBe([]);
    $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee(__('dashboard.shortcuts_title'));
});

test('a broken timezone setting falls back instead of failing', function () {
    app(SettingsService::class)->set('timezone', 'Mars/Olympus');

    $this->actingAs($this->employee)->get(route('dashboard'))->assertOk();
});
