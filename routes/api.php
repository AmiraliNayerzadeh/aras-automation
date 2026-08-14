<?php

use App\Http\Controllers\Webhooks\FaceDeviceWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/face-device/{token}', [FaceDeviceWebhookController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('webhooks.face-device.store');
