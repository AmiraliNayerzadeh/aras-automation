<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskExportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');
    Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/documents', [TaskController::class, 'storeDocument'])->name('tasks.documents.store');
    Route::delete('tasks/{task}/documents/{document}', [TaskController::class, 'destroyDocument'])->name('tasks.documents.destroy');
    Route::get('tasks-export/preview', [TaskExportController::class, 'preview'])->name('tasks.export.preview');
    Route::get('tasks-export/pdf', [TaskExportController::class, 'pdf'])->name('tasks.export.pdf');
    Route::get('tasks-export/excel', [TaskExportController::class, 'excel'])->name('tasks.export.excel');
});
