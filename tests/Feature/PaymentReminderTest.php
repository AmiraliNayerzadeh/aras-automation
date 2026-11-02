<?php

use App\Models\Finance\PaymentReminder;
use App\Models\User;
use App\Notifications\PaymentReminderDueNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

beforeEach(function () {
    Carbon::setTestNow('2026-11-02 10:00:00');

    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->givePermissionTo('payment-reminders.manage');
    $this->employee = User::factory()->create(['status' => 'active']);

    $this->reminder = fn (array $attributes = []) => PaymentReminder::create($attributes + [
        'title' => 'Server',
        'amount' => 100,
        'currency' => 'USD',
        'interval_months' => 1,
        'due_day' => Carbon::parse($attributes['next_due_date'] ?? '2026-11-05')->day,
        'next_due_date' => '2026-11-05',
        'remind_days_before' => 7,
    ]);
});

afterEach(fn () => Carbon::setTestNow());

function payPayload(PaymentReminder $reminder): array
{
    return ['due_date' => $reminder->next_due_date->toDateString(), 'paid_on' => '2026-11-02'];
}

test('only admins can reach payment reminders', function () {
    $this->actingAs($this->employee)->get(route('admin.payment-reminders.index'))->assertForbidden();
    $this->actingAs($this->admin)->get(route('admin.payment-reminders.index'))->assertOk();
});

test('the migration grants the permission to the admin roles', function () {
    expect(Spatie\Permission\Models\Permission::where('name', 'payment-reminders.manage')->exists())->toBeTrue();
});

test('a reminder can be created and keeps its day of the month', function () {
    $this->actingAs($this->admin)->post(route('admin.payment-reminders.store'), [
        'title' => 'Domain',
        'interval_months' => 12,
        'next_due_date' => '2027-10-20',
        'remind_days_before' => 30,
    ])->assertRedirect(route('admin.payment-reminders.index'));

    expect(PaymentReminder::first())
        ->due_day->toBe(20)
        ->created_by->toBe($this->admin->id);
});

test('due dates advance by the cycle and survive short months', function (int $interval, int $day, string $from, string $expected) {
    $reminder = new PaymentReminder(['interval_months' => $interval, 'due_day' => $day]);

    expect($reminder->dueAfter(Carbon::parse($from))->toDateString())->toBe($expected);
})->with([
    'monthly on the 29th into February' => [1, 29, '2027-01-29', '2027-02-28'],
    'back to the 29th after February' => [1, 29, '2027-02-28', '2027-03-29'],
    'every three months on the 20th' => [3, 20, '2026-11-20', '2027-02-20'],
    'yearly on 20 October' => [12, 20, '2026-10-20', '2027-10-20'],
    'the 31st into a 30-day month' => [1, 31, '2026-10-31', '2026-11-30'],
    'across a leap day' => [12, 29, '2028-02-29', '2029-02-28'],
]);

test('the reminder window honours its own day count', function () {
    $outside = ($this->reminder)(['title' => 'Outside', 'next_due_date' => '2026-11-10']);
    $inside = ($this->reminder)(['title' => 'Inside', 'next_due_date' => '2026-11-09']);
    $custom = ($this->reminder)(['title' => 'Custom', 'next_due_date' => '2026-12-01', 'remind_days_before' => 30]);
    $overdue = ($this->reminder)(['title' => 'Overdue', 'next_due_date' => '2026-10-20']);
    ($this->reminder)(['title' => 'Off', 'next_due_date' => '2026-10-20', 'is_active' => false]);

    expect(PaymentReminder::needingAttention()->pluck('title')->all())->toBe(['Overdue', 'Inside', 'Custom'])
        ->and($outside->fresh()->stage())->toBe('upcoming')
        ->and($inside->fresh()->stage())->toBe('soon')
        ->and($overdue->fresh()->stage())->toBe('overdue')
        ->and($overdue->fresh()->daysUntilDue())->toBe(-13);
});

test('the dashboard shows due payments to admins only, until they are paid', function () {
    $reminder = ($this->reminder)();

    $this->actingAs($this->employee)->get(route('dashboard'))->assertOk()->assertDontSee('Server');
    $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('Server')->assertSee('payment-pay-modal', false);

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder))->assertRedirect();

    expect($reminder->fresh()->next_due_date->toDateString())->toBe('2026-12-05');
    $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertDontSee('Server');
});

test('paying records who, when and how much', function () {
    $reminder = ($this->reminder)();

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder) + ['amount' => 120, 'note' => 'raised'])
        ->assertSessionHas('status', 'payment-recorded');

    expect($reminder->payments()->first())
        ->amount->toEqual('120.00')
        ->note->toBe('raised')
        ->paid_by->toBe($this->admin->id)
        ->and($reminder->payments()->first()->due_date->toDateString())->toBe('2026-11-05');
});

test('a repeated submit cannot skip a cycle', function () {
    $reminder = ($this->reminder)();
    $payload = payPayload($reminder);

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), $payload);
    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), $payload)
        ->assertSessionHas('status', 'payment-already-recorded');

    expect($reminder->fresh()->next_due_date->toDateString())->toBe('2026-12-05')
        ->and($reminder->payments()->count())->toBe(1);
});

test('an overdue bill stays due when an older cycle is paid', function () {
    $reminder = ($this->reminder)(['next_due_date' => '2026-09-05']);

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder));

    expect($reminder->fresh()->stage())->toBe('overdue');
});

test('a payment in the future is refused', function () {
    $reminder = ($this->reminder)();

    $this->actingAs($this->admin)
        ->post(route('admin.payment-reminders.pay', $reminder), ['due_date' => '2026-11-05', 'paid_on' => '2026-11-03'])
        ->assertSessionHasErrors('paid_on');
});

test('only the latest payment can be undone', function () {
    $reminder = ($this->reminder)();

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder));
    $first = $reminder->payments()->first();
    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder->fresh()));

    $this->actingAs($this->admin)->delete(route('admin.payment-reminders.payments.undo', [$reminder, $first]))
        ->assertSessionHas('status', 'payment-undo-refused');
    expect($reminder->fresh()->next_due_date->toDateString())->toBe('2027-01-05');

    $this->actingAs($this->admin)->delete(route('admin.payment-reminders.payments.undo', [$reminder, $reminder->payments()->first()]))
        ->assertSessionHas('status', 'payment-undone');
    expect($reminder->fresh()->next_due_date->toDateString())->toBe('2026-12-05')
        ->and($reminder->payments()->count())->toBe(1);
});

test('a payment of another reminder cannot be undone through this one', function () {
    $reminder = ($this->reminder)();
    $other = ($this->reminder)(['title' => 'Other']);
    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder));

    $this->actingAs($this->admin)
        ->delete(route('admin.payment-reminders.payments.undo', [$other, $reminder->payments()->first()]))
        ->assertNotFound();
});

test('editing another field keeps the day of a bill sitting on a short month', function () {
    $reminder = ($this->reminder)(['next_due_date' => '2027-02-28', 'due_day' => 31]);

    $this->actingAs($this->admin)->put(route('admin.payment-reminders.update', $reminder), [
        'title' => 'Renamed',
        'interval_months' => 1,
        'next_due_date' => '2027-02-28',
        'remind_days_before' => 7,
    ])->assertRedirect();

    expect($reminder->fresh())->title->toBe('Renamed')->due_day->toBe(31);
});

test('the command notifies admins once per cycle', function () {
    $reminder = ($this->reminder)();
    ($this->reminder)(['title' => 'Far away', 'next_due_date' => '2027-03-01']);

    $this->artisan('payments:send-reminders')->assertSuccessful();
    $this->artisan('payments:send-reminders')->assertSuccessful();

    expect($this->admin->notifications()->count())->toBe(1)
        ->and($this->employee->notifications()->count())->toBe(0);

    $this->actingAs($this->admin)->post(route('admin.payment-reminders.pay', $reminder), payPayload($reminder));
    expect($this->admin->unreadNotifications()->count())->toBe(0);

    Carbon::setTestNow('2026-12-01 10:00:00');
    $this->artisan('payments:send-reminders')->assertSuccessful();

    expect($this->admin->notifications()->count())->toBe(2);
});

test('the bell shows payment and unknown notifications without breaking', function () {
    ($this->reminder)();
    $this->artisan('payments:send-reminders');

    DatabaseNotification::create([
        'id' => Str::uuid(),
        'type' => 'App\Notifications\SomethingRemoved',
        'notifiable_type' => 'user',
        'notifiable_id' => $this->admin->id,
        'data' => ['anything' => true],
    ]);

    $this->actingAs($this->admin)->get(route('admin.payment-reminders.index'))
        ->assertOk()
        ->assertSee(__('payments.notification_due', ['title' => 'Server', 'date' => '2026-11-05']));

    expect($this->admin->notifications()->where('type', PaymentReminderDueNotification::class)->count())->toBe(1);
});
