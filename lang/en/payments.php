<?php

return [
    'title_index' => 'Payment Reminders',
    'dashboard_title' => 'Upcoming Payments',
    'nav' => 'Payment Reminders',

    'field_title' => 'Title',
    'title_placeholder' => 'e.g. Server hosting',
    'field_amount' => 'Amount',
    'field_currency' => 'Currency',
    'field_interval' => 'Billing cycle',
    'field_next_due_date' => 'Next due date',
    'field_remind_days_before' => 'Remind days before',
    'field_notes' => 'Notes',
    'field_due_date' => 'Due date',
    'field_paid_on' => 'Paid on',
    'field_paid_by' => 'Recorded by',
    'field_note' => 'Note',

    'interval_1' => 'Monthly',
    'interval_2' => 'Every 2 months',
    'interval_3' => 'Every 3 months',
    'interval_4' => 'Every 4 months',
    'interval_6' => 'Every 6 months',
    'interval_12' => 'Yearly',
    'interval_24' => 'Every 2 years',

    'currency_IRT' => 'Toman',
    'currency_USD' => 'USD',
    'currency_EUR' => 'EUR',
    'currency_AED' => 'AED',

    'stage_overdue' => ':days days overdue',
    'stage_today' => 'Due today',
    'stage_soon' => ':days days left',
    'stage_upcoming' => ':days days left',

    'action_mark_paid' => 'Mark as paid',
    'action_undo' => 'Undo this payment',
    'confirm_undo' => 'Undo this payment and move the due date back?',
    'pay_hint' => 'The due date moves to the next cycle, and this stays hidden until its next reminder window opens.',

    'history_title' => 'Payment history',
    'history_empty' => 'No payments recorded yet.',

    'notification_due' => 'Payment for ":title" is due on :date.',

    'flash_reminder_created' => 'Reminder created.',
    'flash_reminder_updated' => 'Reminder updated.',
    'flash_reminder_deleted' => 'Reminder deleted.',
    'flash_payment_recorded' => 'Payment recorded; the due date moved to the next cycle.',
    'flash_payment_already_recorded' => 'This cycle was already paid, so nothing changed.',
    'flash_payment_undone' => 'Payment undone; the due date moved back.',
    'flash_payment_undo_refused' => 'Only the latest payment can be undone.',
];
