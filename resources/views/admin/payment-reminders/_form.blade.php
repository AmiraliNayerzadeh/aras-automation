@php($reminder = $reminder ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <x-input-label for="title" :value="__('payments.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $reminder?->title)" :placeholder="__('payments.title_placeholder')" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="amount" :value="__('payments.field_amount')" />
        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 w-100" :value="old('amount', $reminder?->amount)" />
        <x-input-error :messages="$errors->get('amount')" class="mt-1" />
    </div>

    <div class="col-md-2">
        <x-input-label for="currency" :value="__('payments.field_currency')" />
        <select id="currency" name="currency" class="form-select mt-1">
            <option value="">—</option>
            @foreach (\App\Models\Finance\PaymentReminder::CURRENCIES as $currency)
                <option value="{{ $currency }}" @selected(old('currency', $reminder?->currency) === $currency)>{{ __('payments.currency_'.$currency) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('currency')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="interval_months" :value="__('payments.field_interval')" />
        <select id="interval_months" name="interval_months" class="form-select mt-1" required>
            @foreach (\App\Models\Finance\PaymentReminder::INTERVALS as $interval)
                <option value="{{ $interval }}" @selected((int) old('interval_months', $reminder?->interval_months ?? 1) === $interval)>{{ __('payments.interval_'.$interval) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('interval_months')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="next_due_date" :value="__('payments.field_next_due_date')" />
        <x-text-input id="next_due_date" name="next_due_date" type="date" class="mt-1 w-100" :value="old('next_due_date', $reminder?->next_due_date?->toDateString())" required />
        <x-input-error :messages="$errors->get('next_due_date')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="remind_days_before" :value="__('payments.field_remind_days_before')" />
        <x-text-input id="remind_days_before" name="remind_days_before" type="number" min="0" max="90" class="mt-1 w-100" :value="old('remind_days_before', $reminder?->remind_days_before ?? 7)" required />
        <x-input-error :messages="$errors->get('remind_days_before')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="notes" :value="__('payments.field_notes')" />
        <textarea id="notes" name="notes" rows="3" class="form-control mt-1">{{ old('notes', $reminder?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
    </div>

    <div class="col-12 form-check">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input" @checked(old('is_active', $reminder?->is_active ?? true))>
        <label for="is_active" class="form-check-label">{{ __('app.field_active') }}</label>
    </div>
</div>

<div class="d-flex gap-2 mt-24">
    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
    <a href="{{ route('admin.payment-reminders.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
