<?php

use App\Models\Hr\AttendanceCorrection;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\User;
use App\Services\AttendanceCalculator;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 18:00:00');

    Permission::findOrCreate('face-device-events.view');

    $this->owner = User::factory()->create(['email' => AttendanceCorrection::EDITOR_EMAIL, 'status' => 'active']);
    $this->owner->givePermissionTo('face-device-events.view');

    $this->superAdmin = User::factory()->create(['status' => 'active']);
    $this->superAdmin->assignRole(Role::findOrCreate('super-admin'));
    $this->superAdmin->givePermissionTo('face-device-events.view');

    $this->employee = User::factory()->create(['status' => 'active']);

    // 2026-10-28 is a Wednesday: a default work day.
    $this->day = '2026-10-28';
    $this->event = fn (string $time) => FaceDeviceEvent::create([
        'user_id' => $this->employee->id,
        'employee_no' => '7',
        'event_time' => $this->day.' '.$time,
    ]);

    $this->dayRow = function () {
        $date = Carbon::parse($this->day);

        return app(AttendanceCalculator::class)
            ->forDateRange(User::whereKey($this->employee->id)->with('workShifts')->get(), $date, $date)
            ->first();
    };

    $this->payload = fn (array $overrides = []) => $overrides + [
        'user_id' => $this->employee->id,
        'date' => $this->day,
        'check_in' => '08:30',
        'check_out' => '17:00',
        'reason' => 'Power cut at closing time',
    ];
});

afterEach(fn () => Carbon::setTestNow());

test('nobody but the named account can correct attendance', function () {
    $this->actingAs($this->superAdmin)->post(route('admin.attendance-corrections.store'), ($this->payload)())->assertForbidden();
    $this->actingAs($this->employee)->post(route('admin.attendance-corrections.store'), ($this->payload)())->assertForbidden();

    expect(AttendanceCorrection::count())->toBe(0);

    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)())
        ->assertSessionHas('status', 'attendance-corrected');

    expect(AttendanceCorrection::count())->toBe(1);
});

test('the email check ignores case', function () {
    $this->owner->update(['email' => strtoupper(AttendanceCorrection::EDITOR_EMAIL)]);

    $this->actingAs($this->owner->fresh())->post(route('admin.attendance-corrections.store'), ($this->payload)())
        ->assertSessionHasNoErrors()->assertRedirect();

    expect(AttendanceCorrection::count())->toBe(1);
});

test('a correction cannot be removed by anyone else', function () {
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)());

    $this->actingAs($this->superAdmin)->delete(route('admin.attendance-corrections.destroy', AttendanceCorrection::first()))->assertForbidden();

    expect(AttendanceCorrection::count())->toBe(1);
});

test('the edit button shows only for the named account', function () {
    $url = route('admin.attendance-report.index', ['user_id' => $this->employee->id, 'date_from' => $this->day, 'date_to' => $this->day]);

    $this->actingAs($this->owner)->get($url)->assertOk()->assertSee('attendance-correction-modal', false);
    $this->actingAs($this->superAdmin)->get($url)->assertOk()->assertDontSee('attendance-correction-modal', false);
});

test('a missing check-out is fixed and overtime recalculated', function () {
    ($this->event)('08:30:00');
    expect(($this->dayRow)()['status'])->toBe('incomplete');

    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)(['check_out' => '18:00']));

    $row = ($this->dayRow)();

    expect($row['status'])->toBe('present')
        ->and($row['check_in']->format('H:i'))->toBe('08:30')
        ->and($row['check_out']->format('H:i'))->toBe('18:00')
        ->and($row['worked_minutes'])->toBe(570)
        ->and($row['correction'])->not->toBeNull();
});

test('a wrong device exit is replaced, and restoring brings it back', function () {
    ($this->event)('08:30:00');
    ($this->event)('23:50:00');

    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)());
    expect(($this->dayRow)()['check_out']->format('H:i'))->toBe('17:00');

    $this->actingAs($this->owner)->delete(route('admin.attendance-corrections.destroy', AttendanceCorrection::first()))
        ->assertSessionHas('status', 'attendance-correction-removed');

    expect(($this->dayRow)()['check_out']->format('H:i'))->toBe('23:50')
        ->and(($this->dayRow)()['correction'])->toBeNull()
        ->and(FaceDeviceEvent::count())->toBe(2);
});

test('an absent day can be filled in', function () {
    expect(($this->dayRow)()['status'])->toBe('absent');

    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)());

    expect(($this->dayRow)()['status'])->toBe('present');
});

test('saving twice updates the same correction', function () {
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)());
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)(['check_out' => '16:00', 'reason' => 'Second thoughts']));

    expect(AttendanceCorrection::count())->toBe(1)
        ->and(AttendanceCorrection::first()->reason)->toBe('Second thoughts');
});

test('a bad correction is refused', function (array $overrides, string $field) {
    $this->actingAs($this->owner)
        ->post(route('admin.attendance-corrections.store'), ($this->payload)($overrides))
        ->assertSessionHasErrors($field);

    expect(AttendanceCorrection::count())->toBe(0);
})->with([
    'exit before entry' => [['check_out' => '07:00'], 'check_out'],
    'no times at all' => [['check_in' => null, 'check_out' => null], 'check_out'],
    'a future day' => [['date' => '2026-11-03'], 'date'],
]);

test('entry alone is allowed and leaves the day incomplete', function () {
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)(['check_out' => null]))
        ->assertSessionHasNoErrors();

    expect(($this->dayRow)()['status'])->toBe('incomplete');
});

test('the reason is optional', function () {
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)(['reason' => '']))
        ->assertSessionHasNoErrors();

    expect(AttendanceCorrection::first()->reason)->toBeNull()
        ->and(($this->dayRow)()['status'])->toBe('present');
});

test('a written reason shows in the report', function () {
    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)());

    $this->actingAs($this->superAdmin)
        ->get(route('admin.attendance-report.index', ['user_id' => $this->employee->id, 'date_from' => $this->day, 'date_to' => $this->day]))
        ->assertOk()
        ->assertSee('Power cut at closing time');
});

test('a correction without a reason leaves no mark on the report', function () {
    $url = route('admin.attendance-report.index', ['user_id' => $this->employee->id, 'date_from' => $this->day, 'date_to' => $this->day]);
    $before = $this->actingAs($this->superAdmin)->get($url)->getContent();

    $this->actingAs($this->owner)->post(route('admin.attendance-corrections.store'), ($this->payload)(['reason' => null]));
    $after = $this->actingAs($this->superAdmin)->get($url)->assertOk()->assertSee('08:30:00')->getContent();

    $marker = fn (string $html) => substr_count($html, 'text-secondary-light text-xs mt-4');

    expect($marker($after))->toBe($marker($before));
});
