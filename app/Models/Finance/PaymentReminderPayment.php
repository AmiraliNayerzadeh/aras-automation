<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_reminder_id', 'due_date', 'paid_on', 'amount', 'currency', 'note', 'paid_by'])]
class PaymentReminderPayment extends Model
{
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
