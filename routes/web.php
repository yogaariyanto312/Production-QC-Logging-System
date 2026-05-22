<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GambarKerjaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionLogController;
use App\Http\Controllers\ProfileController;
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

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Input Produksi (semua user bisa input & view)
    // parameters() menyamakan {productionLog} di URL dengan nama parameter di controller
    Route::resource('production', ProductionLogController::class)
        ->except(['show'])
        ->parameters(['production' => 'productionLog']);
    Route::get('/production/{productionLog}', [ProductionLogController::class, 'show'])->name('production.show');

    // API untuk dropdown product (AJAX)
    Route::get('/api/products', [ProductController::class, 'apiList'])->name('api.products');

    // Ukuran Produk (semua user bisa lihat)
    Route::get('/products/ukuran', [ProductController::class, 'ukuranIndex'])->name('products.ukuran');

    // Gambar Kerja — literal routes HARUS sebelum wildcard
    Route::get('/gambar-kerja',                        [GambarKerjaController::class, 'index'])->name('gambar-kerja.index');
    Route::get('/gambar-kerja/create',                 [GambarKerjaController::class, 'create'])->name('gambar-kerja.create')->middleware('role:admin');
    Route::post('/gambar-kerja',                       [GambarKerjaController::class, 'store'])->name('gambar-kerja.store')->middleware('role:admin');
    Route::get('/gambar-kerja/produk/{product}',        [GambarKerjaController::class, 'byProduct'])->name('gambar-kerja.by-product');
    Route::delete('/gambar-kerja/produk/{product}/all', [GambarKerjaController::class, 'destroyByProduct'])->name('gambar-kerja.destroy-by-product')->middleware('role:admin');
    Route::delete('/gambar-kerja/{gambarKerja}',        [GambarKerjaController::class, 'destroy'])->name('gambar-kerja.destroy')->middleware('role:admin');

    // Chat / Pesan
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/my', [MessageController::class, 'myMessages'])->name('messages.my');
    Route::get('/chat', [MessageController::class, 'chat'])->name('chat');

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Chat admin
        Route::get('/messages', [MessageController::class, 'adminMessages'])->name('messages.index');
        Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
        Route::patch('/messages/{message}/read', [MessageController::class, 'markRead'])->name('messages.read');
        Route::delete('/messages/conversation/{senderId}', [MessageController::class, 'destroyConversation'])->name('messages.destroy-conversation');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        // Manajemen Admin
        Route::resource('admins', AdminController::class)->except(['show']);

        // Manajemen Operator
        Route::resource('operators', OperatorController::class)->except(['show']);
        Route::patch('/operators/{operator}/toggle-active', [OperatorController::class, 'toggleActive'])->name('operators.toggle-active');

        // Departemen
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::post('/departments/quick-store', [DepartmentController::class, 'quickStore'])->name('departments.quick-store');

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
