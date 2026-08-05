<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ErrorLogController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication routes
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Admin Dashboard Routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Businesses Management
    Route::get('/businesses', [BusinessController::class, 'index'])->name('businesses.index');
    Route::get('/businesses/export/all', [BusinessController::class, 'exportAll'])->name('businesses.export.all');
    Route::get('/businesses/export/filtered', [BusinessController::class, 'exportFiltered'])->name('businesses.export.filtered');
    Route::post('/businesses/export/selected', [BusinessController::class, 'exportSelected'])->name('businesses.export.selected');
    Route::post('/businesses/bulk-delete', [BusinessController::class, 'bulkDestroy'])->name('businesses.bulk-delete');
    Route::get('/businesses/{id}', [BusinessController::class, 'show'])->name('businesses.show');
    Route::delete('/businesses/{id}', [BusinessController::class, 'destroy'])->name('businesses.destroy');

    // Error Logs
    Route::get('/errors', [ErrorLogController::class, 'index'])->name('errors.index');
});
