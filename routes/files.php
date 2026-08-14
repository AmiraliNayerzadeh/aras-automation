<?php

use App\Http\Controllers\FileManager\FileCommentController;
use App\Http\Controllers\FileManager\FileController;
use App\Http\Controllers\FileManager\FileManagerController;
use App\Http\Controllers\FileManager\FileShareController;
use App\Http\Controllers\FileManager\FileVersionController;
use App\Http\Controllers\FileManager\FolderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('files', [FileManagerController::class, 'index'])->name('files.index');
    Route::get('files/trash', [FileManagerController::class, 'trash'])->name('files.trash');
    Route::get('files/link/{token}', [FileController::class, 'downloadByToken'])->name('files.link');

    Route::post('files/folders', [FolderController::class, 'store'])->name('files.folders.store');
    Route::put('files/folders/{folder}', [FolderController::class, 'update'])->name('files.folders.update');
    Route::delete('files/folders/{folder}', [FolderController::class, 'destroy'])->name('files.folders.destroy');
    Route::post('files/folders/{id}/restore', [FolderController::class, 'restore'])->name('files.folders.restore');
    Route::post('files/folders/{folder}/move', [FolderController::class, 'move'])->name('files.folders.move');
    Route::post('files/folders/{folder}/shares', [FileShareController::class, 'storeForFolder'])->name('files.folders.shares.store');
    Route::delete('files/folders/{folder}/shares/{share}', [FileShareController::class, 'destroyForFolder'])->name('files.folders.shares.destroy');

    Route::post('files/entries', [FileController::class, 'store'])->name('files.entries.store');
    Route::get('files/entries/{file}', [FileController::class, 'show'])->name('files.entries.show');
    Route::put('files/entries/{file}', [FileController::class, 'update'])->name('files.entries.update');
    Route::delete('files/entries/{file}', [FileController::class, 'destroy'])->name('files.entries.destroy');
    Route::post('files/entries/{id}/restore', [FileController::class, 'restore'])->name('files.entries.restore');
    Route::post('files/entries/{file}/move', [FileController::class, 'move'])->name('files.entries.move');
    Route::get('files/entries/{file}/download', [FileController::class, 'download'])->name('files.entries.download');
    Route::post('files/entries/{file}/version', [FileController::class, 'storeVersion'])->name('files.entries.version.store');
    Route::post('files/entries/{file}/share-link/enable', [FileController::class, 'enableShareLink'])->name('files.entries.share-link.enable');
    Route::post('files/entries/{file}/share-link/disable', [FileController::class, 'disableShareLink'])->name('files.entries.share-link.disable');
    Route::post('files/entries/{file}/versions/{version}/restore', [FileVersionController::class, 'restore'])->name('files.entries.version.restore');
    Route::post('files/entries/{file}/comments', [FileCommentController::class, 'store'])->name('files.entries.comments.store');
    Route::post('files/entries/{file}/shares', [FileShareController::class, 'storeForFile'])->name('files.entries.shares.store');
    Route::delete('files/entries/{file}/shares/{share}', [FileShareController::class, 'destroyForFile'])->name('files.entries.shares.destroy');
});
