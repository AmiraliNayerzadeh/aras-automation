<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AssetCategoryController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BusinessPartnerController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\FaceDeviceEventController;
use App\Http\Controllers\Admin\LookupTypeController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ProductBrandController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductExportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\WorkShiftController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('users/{user}/documents', [UserController::class, 'storeDocument'])->name('users.documents.store');
    Route::delete('users/{user}/documents/{document}', [UserController::class, 'destroyDocument'])->name('users.documents.destroy');
    Route::resource('business-partners', BusinessPartnerController::class)->except(['show']);
    Route::post('business-partners/{businessPartner}/contacts', [BusinessPartnerController::class, 'storeContact'])
        ->name('business-partners.contacts.store');
    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::resource('branches', BranchController::class)->except(['show']);
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::resource('units', UnitController::class)->except(['show']);
    Route::resource('positions', PositionController::class)->except(['show']);
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::resource('settings', SettingController::class)->only(['index', 'update']);
    Route::put('settings-color', [SettingController::class, 'updateColor'])->name('settings.color.update');

    Route::resource('lookup-types', LookupTypeController::class)->only(['index', 'edit']);
    Route::post('lookup-types/{lookupType}/values', [LookupTypeController::class, 'storeValue'])
        ->name('lookup-types.values.store');

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    Route::get('face-device-events', [FaceDeviceEventController::class, 'index'])->name('face-device-events.index');
    Route::get('attendance-report', [AttendanceReportController::class, 'index'])->name('attendance-report.index');

    Route::get('work-shifts', [WorkShiftController::class, 'index'])->name('work-shifts.index');
    Route::get('work-shifts/{user}/edit', [WorkShiftController::class, 'edit'])->name('work-shifts.edit');
    Route::put('work-shifts/{user}', [WorkShiftController::class, 'update'])->name('work-shifts.update');

    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::put('assets/{asset}/return', [AssetController::class, 'return'])->name('assets.return');
    Route::get('assets/{asset}/label', [AssetController::class, 'label'])->name('assets.label');
    Route::get('assets-labels', [AssetController::class, 'labels'])->name('assets.labels');
    Route::resource('asset-categories', AssetCategoryController::class)->except(['show']);

    Route::resource('products', ProductController::class)->except(['show']);
    Route::post('products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulk-update');
    Route::get('products/export/preview', [ProductExportController::class, 'preview'])->name('products.export.preview');
    Route::get('products/export/pdf', [ProductExportController::class, 'pdf'])->name('products.export.pdf');
    Route::get('products/export/excel', [ProductExportController::class, 'excel'])->name('products.export.excel');
    Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
    Route::resource('product-brands', ProductBrandController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class)->except(['show']);
    Route::resource('stock-movements', StockMovementController::class)->only(['index', 'create', 'store']);
    Route::get('stock-overview', [StockMovementController::class, 'overview'])->name('stock-movements.overview');
});
