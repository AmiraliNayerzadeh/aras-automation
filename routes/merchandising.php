<?php

use App\Http\Controllers\Merchandising\ReportController;
use App\Http\Controllers\Merchandising\SetupController;
use App\Http\Controllers\Merchandising\StoreController;
use App\Http\Controllers\Merchandising\UploadController;
use App\Http\Controllers\Merchandising\VisitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('merchandising')->name('merchandising.')->group(function () {
    Route::get('visits', [VisitController::class, 'index'])->name('visits.index');
    Route::get('visits/create', [VisitController::class, 'create'])->name('visits.create');
    Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
    Route::get('visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::delete('visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');
    Route::get('store-search', [VisitController::class, 'stores'])->name('stores.search');

    Route::post('uploads', [UploadController::class, 'start'])->name('uploads.start');
    Route::get('uploads/{token}', [UploadController::class, 'status'])->name('uploads.status');
    Route::post('uploads/{token}/chunk', [UploadController::class, 'chunk'])->name('uploads.chunk');
    Route::delete('uploads/{token}', [UploadController::class, 'destroy'])->name('uploads.destroy');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('reports/excel', [ReportController::class, 'excel'])->name('reports.excel');

    Route::get('stores-template', [StoreController::class, 'template'])->name('stores.template');
    Route::post('stores-import', [StoreController::class, 'import'])->name('stores.import');
    Route::resource('stores', StoreController::class)->except(['show']);

    Route::get('setup', [SetupController::class, 'index'])->name('setup');
    Route::post('setup/merchandisers', [SetupController::class, 'addMerchandiser'])->name('setup.merchandisers.store');
    Route::put('setup/merchandisers/{user}', [SetupController::class, 'updateMerchandiser'])->name('setup.merchandisers.update');
    Route::delete('setup/merchandisers/{user}', [SetupController::class, 'removeMerchandiser'])->name('setup.merchandisers.destroy');
    Route::put('setup/categories', [SetupController::class, 'updateCategories'])->name('setup.categories');
    Route::post('setup/products-import', [SetupController::class, 'importProducts'])->name('setup.products.import');
});
