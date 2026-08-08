<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance');
    Route::post('orders/{order}/post-stock', [OrderController::class, 'postStock'])->name('orders.post-stock');
    Route::post('orders/{order}/shipment', [OrderController::class, 'saveShipment'])->name('orders.shipment.save');
});
