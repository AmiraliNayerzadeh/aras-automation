<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\LookupTypeController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('settings', SettingController::class)->only(['index', 'update']);

    Route::resource('lookup-types', LookupTypeController::class)->only(['index', 'edit']);
    Route::post('lookup-types/{lookupType}/values', [LookupTypeController::class, 'storeValue'])
        ->name('lookup-types.values.store');

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});
