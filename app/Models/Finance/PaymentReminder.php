<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'title', 'amount', 'currency', 'interval_months', 'due_day', 'next_due_date',
    'remind_days_before', 'is_active', 'notes', 'notified_due_date', 'created_by',
])]
class PaymentReminder extends Model
{
    use LogsActivity, SoftDeletes;

    public const INTERVALS = [1, 2, 3, 4, 6, 12, 24];

    public const CURRENCIES = ['IRT', 'USD', 'EUR', 'AED'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'next_due_date' => 'date',
            'notified_due_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'amount', 'currency', 'interval_months', 'next_due_date', 'remind_days_before', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('payment_reminder');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentReminderPayment::class)->latest('id');
    }

    /**
     * Active reminders whose reminder window has opened (or that are overdue),
     * soonest first. Filtered in PHP because the window is per-row date maths
     * and this table only ever holds a handful of rows.
     *
     * @return Collection<int, self>
     */
    public static function needingAttention(): Collection
    {
        return static::where('is_active', true)
            ->orderBy('next_due_date')
            ->get()
            ->filter(fn (self $reminder) => $reminder->needsAttention())
            ->values();
    }

    public function needsAttention(): bool
    {
        return $this->is_active && $this->daysUntilDue() <= $this->remind_days_before;
    }

    /**
     * Negative once overdue.
     */
    public function daysUntilDue(): int
    {
        return (int) today()->diffInDays($this->next_due_date, false);
    }

    public function stage(): string
    {
        return match (true) {
            ! $this->is_active => 'inactive',
            $this->daysUntilDue() < 0 => 'overdue',
            $this->daysUntilDue() === 0 => 'today',
            $this->needsAttention() => 'soon',
            default => 'upcoming',
        };
    }

    public function dueAfter(Carbon $date): Carbon
    {
        $next = $date->copy()->startOfMonth()->addMonthsNoOverflow($this->interval_months);

        return $next->day(min($this->due_day, $next->daysInMonth));
    }

    /**
     * Records one cycle as paid and moves on to the next due date. Returns null
     * if $dueDate is no longer the open cycle (a double click, or someone else
     * got there first), so a repeated submit can't skip a cycle.
     */
    public function markPaid(string $dueDate, string $paidOn, ?string $amount, ?string $note, User $user): ?PaymentReminderPayment
    {
        return DB::transaction(function () use ($dueDate, $paidOn, $amount, $note, $user) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->next_due_date->toDateString() !== $dueDate) {
                return null;
            }

            $payment = $locked->payments()->create([
                'due_date' => $dueDate,
                'paid_on' => $paidOn,
                'amount' => $amount ?? $locked->amount,
                'currency' => $locked->currency,
                'note' => $note,
                'paid_by' => $user->id,
            ]);

            $locked->update(['next_due_date' => $locked->dueAfter($locked->next_due_date)]);

            return $payment;
        });
    }

    /**
     * Only the latest payment can be undone: it's the one that set the current
     * due date, so reverting it can't leave a hole in the chain of cycles.
     */
    public function undoPayment(PaymentReminderPayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $locked = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->payments()->value('id') !== $payment->id) {
                return false;
            }

            $dueDate = $payment->due_date->toDateString();
            $payment->delete();
            $locked->update(['next_due_date' => $dueDate, 'notified_due_date' => $dueDate]);

            return true;
        });
    }

    public function formattedAmount(): ?string
    {
        if ($this->amount === null) {
            return null;
        }

        $decimals = fmod((float) $this->amount, 1.0) === 0.0 ? 0 : 2;

        return number_format((float) $this->amount, $decimals).' '.__('payments.currency_'.$this->currency);
    }
}
