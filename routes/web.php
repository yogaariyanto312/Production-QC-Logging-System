<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\GambarKerjaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NoteController;
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

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Root redirect
Route::get('/', fn() => redirect()->route('dashboard'));

// File serve — bypass junction/symlink issue pada local dev
Route::get('/file/{path}', [GambarKerjaController::class, 'serveFile'])
    ->where('path', '.+')->middleware('auth')->name('storage.file');

// Protected routes (authenticated)
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // About
    Route::get('/about', fn() => view('about'))->name('about');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Riwayat Produksi — semua role bisa lihat
    Route::get('/production', [ProductionLogController::class, 'index'])->name('production.index');

    // Input / Edit Produksi — visitor tidak bisa (literal routes HARUS sebelum wildcard {productionLog})
    Route::middleware('role:developer,admin,supervisor,operator')->group(function () {
        Route::get('/production/create', [ProductionLogController::class, 'create'])->name('production.create');
        Route::post('/production', [ProductionLogController::class, 'store'])->name('production.store');
        Route::get('/production/{productionLog}/edit', [ProductionLogController::class, 'edit'])->name('production.edit');
        Route::put('/production/{productionLog}', [ProductionLogController::class, 'update'])->name('production.update');
        Route::delete('/production/{productionLog}', [ProductionLogController::class, 'destroy'])->name('production.destroy');
    });

    // Show detail — setelah literal routes supaya tidak di-capture duluan oleh wildcard
    Route::get('/production/{productionLog}', [ProductionLogController::class, 'show'])->name('production.show');

    // API untuk dropdown product (AJAX)
    Route::get('/api/products', [ProductController::class, 'apiList'])->name('api.products');

    // Ukuran Produk (semua user bisa lihat)
    Route::get('/products/ukuran', [ProductController::class, 'ukuranIndex'])->name('products.ukuran');

    // Gambar Kerja — literal routes HARUS sebelum wildcard
    Route::get('/gambar-kerja',           [GambarKerjaController::class, 'index'])->name('gambar-kerja.index');
    Route::get('/gambar-kerja/create',    [GambarKerjaController::class, 'create'])->name('gambar-kerja.create')->middleware('role:admin');
    Route::post('/gambar-kerja',          [GambarKerjaController::class, 'store'])->name('gambar-kerja.store')->middleware('role:admin');
    Route::get('/gambar-kerja/group',     [GambarKerjaController::class, 'byGroup'])->name('gambar-kerja.by-group');
    Route::delete('/gambar-kerja/group',  [GambarKerjaController::class, 'destroyByGroup'])->name('gambar-kerja.destroy-by-group')->middleware('role:admin');
    Route::post('/gambar-kerja/group/thumbnail',          [GambarKerjaController::class, 'uploadThumbnail'])->name('gambar-kerja.upload-thumbnail')->middleware('role:admin');
    Route::delete('/gambar-kerja/group/thumbnail',       [GambarKerjaController::class, 'deleteThumbnail'])->name('gambar-kerja.delete-thumbnail')->middleware('role:admin');
    Route::delete('/gambar-kerja/{gambarKerja}', [GambarKerjaController::class, 'destroy'])->name('gambar-kerja.destroy')->middleware('role:admin');

    // Notes
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::get('/notes/list', [NoteController::class, 'list'])->name('notes.list');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Chat / Pesan
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/messages/my', [MessageController::class, 'myMessages'])->name('messages.my');
    Route::get('/chatting', [MessageController::class, 'chatUnified'])->name('chatting');
    Route::get('/api/chat-contacts', [MessageController::class, 'contacts'])->name('chat.contacts');
    Route::patch('/api/ping', [MessageController::class, 'ping'])->name('api.ping');
    Route::patch('/api/typing', [MessageController::class, 'typing'])->name('api.typing');
    Route::get('/chat', [MessageController::class, 'chat'])->name('chat'); // legacy

    // Admin + Supervisor routes (developer juga bisa akses)
    Route::middleware('role:developer,admin,supervisor')->group(function () {

        // Kategori — hanya bisa lihat (index), create/edit/delete hanya untuk developer
        Route::get('/categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories.index');

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

    // Admin + Supervisor: Chat inbox
    Route::middleware('role:developer,admin,supervisor')->group(function () {
        Route::get('/messages', [MessageController::class, 'adminMessages'])->name('messages.index');
        Route::post('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
        Route::patch('/messages/{message}/read', [MessageController::class, 'markRead'])->name('messages.read');
        Route::delete('/messages/conversation/{senderId}', [MessageController::class, 'destroyConversation'])->name('messages.destroy-conversation');
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    });

    // Halaman Manajemen (developer + admin bisa lihat)
    Route::middleware('role:developer,admin')->group(function () {
        Route::get('/management', [ManagementController::class, 'index'])->name('management.index');

        // Manajemen Operator (admin boleh kelola operator)
        Route::resource('operators', OperatorController::class)->except(['show']);
        Route::patch('/operators/{operator}/toggle-active', [OperatorController::class, 'toggleActive'])->name('operators.toggle-active');

        // Manajemen Visitor (developer + admin bisa kelola)
        Route::resource('visitors', \App\Http\Controllers\VisitorController::class)->except(['show', 'index']);
    });

    // Developer only routes (permission tertinggi — hanya developer yg bisa kelola admin, supervisor, developer, departemen, kategori)
    Route::middleware('role:developer')->group(function () {
        Route::resource('developers', \App\Http\Controllers\DeveloperController::class)->except(['show']);
        Route::resource('admins', AdminController::class)->except(['show']);
        Route::resource('supervisors', SupervisorController::class)->except(['show']);
        Route::resource('departments', DepartmentController::class)->except(['show']);
        Route::post('/departments/quick-store', [DepartmentController::class, 'quickStore'])->name('departments.quick-store');
        Route::resource('categories', CategoryController::class)->except(['show']);
    });
});
