<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\MissionRequestController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('leave-requests', [LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave-requests.create');
    Route::post('leave-requests', [LeaveRequestController::class, 'store'])->name('leave-requests.store');
    Route::get('leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::post('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

    Route::get('mission-requests', [MissionRequestController::class, 'index'])->name('mission-requests.index');
    Route::get('mission-requests/create', [MissionRequestController::class, 'create'])->name('mission-requests.create');
    Route::post('mission-requests', [MissionRequestController::class, 'store'])->name('mission-requests.store');
    Route::get('mission-requests/{missionRequest}', [MissionRequestController::class, 'show'])->name('mission-requests.show');
    Route::put('mission-requests/{missionRequest}/report', [MissionRequestController::class, 'report'])->name('mission-requests.report');
    Route::post('mission-requests/{missionRequest}/cancel', [MissionRequestController::class, 'cancel'])->name('mission-requests.cancel');

    Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
    Route::post('approvals/{approvalStep}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('approvals/{approvalStep}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
});
