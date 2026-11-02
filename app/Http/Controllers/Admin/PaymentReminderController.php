<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Finance\PaymentReminder;
use App\Models\Finance\PaymentReminderPayment;
use App\Notifications\PaymentReminderDueNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentReminderController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:payment-reminders.manage'),
        ];
    }

    public function index(): View
    {
        return view('admin.payment-reminders.index', [
            'reminders' => PaymentReminder::orderByDesc('is_active')->orderBy('next_due_date')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-reminders.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        PaymentReminder::create($data + [
            'due_day' => Carbon::parse($data['next_due_date'])->day,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.payment-reminders.index')->with('status', 'reminder-created');
    }

    public function edit(PaymentReminder $paymentReminder): View
    {
        return view('admin.payment-reminders.edit', [
            'reminder' => $paymentReminder,
            'payments' => $paymentReminder->payments()->with('paidBy')->get(),
        ]);
    }

    public function update(Request $request, PaymentReminder $paymentReminder): RedirectResponse
    {
        $data = $this->validateData($request);

        // A moved date is a new schedule; untouched, due_day must survive so a
        // 31st bill sitting on Feb 28 isn't pinned to the 28th forever.
        if ($paymentReminder->next_due_date->toDateString() !== $data['next_due_date']) {
            $data['due_day'] = Carbon::parse($data['next_due_date'])->day;
            $data['notified_due_date'] = null;
        }

        $paymentReminder->update($data);

        return redirect()->route('admin.payment-reminders.index')->with('status', 'reminder-updated');
    }

    public function destroy(PaymentReminder $paymentReminder): RedirectResponse
    {
        $paymentReminder->delete();
        $this->clearNotifications($paymentReminder);

        return redirect()->route('admin.payment-reminders.index')->with('status', 'reminder-deleted');
    }

    public function pay(Request $request, PaymentReminder $paymentReminder): RedirectResponse
    {
        $data = $request->validate([
            'due_date' => ['required', 'date_format:Y-m-d'],
            'paid_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $payment = $paymentReminder->markPaid(
            $data['due_date'],
            $data['paid_on'],
            $data['amount'] ?? null,
            $data['note'] ?? null,
            $request->user(),
        );

        if (! $payment) {
            return back()->with('status', 'payment-already-recorded');
        }

        $this->clearNotifications($paymentReminder);

        return back()->with('status', 'payment-recorded');
    }

    public function undoPayment(PaymentReminder $paymentReminder, PaymentReminderPayment $payment): RedirectResponse
    {
        return back()->with('status', $paymentReminder->undoPayment($payment) ? 'payment-undone' : 'payment-undo-refused');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999999'],
            'currency' => ['nullable', 'required_with:amount', Rule::in(PaymentReminder::CURRENCIES)],
            'interval_months' => ['required', 'integer', Rule::in(PaymentReminder::INTERVALS)],
            'next_due_date' => ['required', 'date_format:Y-m-d'],
            'remind_days_before' => ['required', 'integer', 'min:0', 'max:90'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * A settled bill shouldn't keep nagging from the bell menu.
     */
    protected function clearNotifications(PaymentReminder $reminder): void
    {
        DatabaseNotification::where('type', PaymentReminderDueNotification::class)
            ->where('data->reminder_id', $reminder->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
