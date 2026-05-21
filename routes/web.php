<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionLogController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Auth routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Root redirect
Route::get('/', fn() => redirect()->route('dashboard'));

// Protected routes (authenticated)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Input Produksi (semua user bisa input & view)
    Route::resource('production', ProductionLogController::class)->except(['show']);
    Route::get('/production/{productionLog}', [ProductionLogController::class, 'show'])->name('production.show');

    // API untuk dropdown product (AJAX)
    Route::get('/api/products', [ProductController::class, 'apiList'])->name('api.products');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {

        // Kategori
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Produk
        Route::resource('products', ProductController::class);

        // Laporan
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/',             [ReportController::class, 'index'])->name('index');
            Route::get('/daily',        [ReportController::class, 'daily'])->name('daily');
            Route::get('/export-pdf',   [ReportController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('/daily-pdf',    [ReportController::class, 'exportDailyPdf'])->name('daily-pdf');
        });
    });
});
