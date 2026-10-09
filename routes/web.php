<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SesiController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect(auth()->check()
    ? (auth()->user()->isAdmin() ? '/admin' : '/dashboard')
    : '/login'));
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->middleware('throttle:10,1');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/minta-reset', [AuthController::class, 'resetRequest']);
if (app()->environment('local')) {
    Route::get('/demo/reset', [AuthController::class, 'resetDemo']);
}

Route::middleware('auth')->group(function () {
    Route::get('/ganti-password', [AuthController::class, 'changePassword']);
    Route::post('/ganti-password', [AuthController::class, 'doChangePassword']);
});

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/kelas', [KelasController::class, 'index']);
    Route::get('/kelas/tambah', [KelasController::class, 'create']);
    Route::post('/kelas', [KelasController::class, 'store']);
    Route::get('/kelas/{kelas}/edit', [KelasController::class, 'edit'])->middleware('owns.kelas');
    Route::put('/kelas/{kelas}', [KelasController::class, 'update'])->middleware('owns.kelas');
    Route::post('/kelas/{kelas}/arsip', [KelasController::class, 'archive'])->middleware('owns.kelas');
    Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->middleware('owns.kelas');
    Route::get('/kelas/{kelas}', [KelasController::class, 'show'])->middleware('owns.kelas');

    Route::get('/kelas/{kelas}/siswa', [SiswaController::class, 'index'])->middleware('owns.kelas');
    Route::post('/kelas/{kelas}/siswa', [SiswaController::class, 'store'])->middleware('owns.kelas');
    Route::put('/siswa/{siswa}', [SiswaController::class, 'update']);
    Route::delete('/siswa/{siswa}', [SiswaController::class, 'destroy']);
    Route::get('/kelas/{kelas}/import', [SiswaController::class, 'importForm'])->middleware('owns.kelas');
    Route::post('/kelas/{kelas}/import/preview', [SiswaController::class, 'importPreview'])->middleware('owns.kelas');
    Route::post('/kelas/{kelas}/import/confirm', [SiswaController::class, 'importConfirm'])->middleware('owns.kelas');
    Route::get('/kelas/{kelas}/template', [SiswaController::class, 'template'])->middleware('owns.kelas');

    Route::get('/kelas/{kelas}/sesi/tambah', [SesiController::class, 'create'])->middleware('owns.kelas');
    Route::post('/kelas/{kelas}/sesi', [SesiController::class, 'store'])->middleware('owns.kelas');
    Route::get('/sesi/{sesi}', [SesiController::class, 'show']);
    Route::put('/sesi/{sesi}/draft', [SesiController::class, 'saveDraft']);
    Route::post('/sesi/{sesi}/finalisasi', [SesiController::class, 'finalize']);
    Route::post('/sesi/{sesi}/reopen', [SesiController::class, 'reopen']);
    Route::delete('/sesi/{sesi}', [SesiController::class, 'destroy']);
    Route::get('/sesi/{sesi}/audit', [RekapController::class, 'audit']);

    Route::get('/kelas/{kelas}/rekap', [RekapController::class, 'index'])->middleware('owns.kelas');
    Route::get('/kelas/{kelas}/rekap.xlsx', [RekapController::class, 'exportExcel'])->middleware('owns.kelas');
    Route::get('/kelas/{kelas}/rekap/cetak', [RekapController::class, 'cetak'])->middleware('owns.kelas');

    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/guru', [AdminController::class, 'store']);
        Route::post('/guru/{guru}/toggle', [AdminController::class, 'toggle']);
        Route::post('/guru/{guru}/reset', [AdminController::class, 'resetPassword']);
        Route::get('/rekap', [AdminController::class, 'rekap']);
        Route::get('/audit', [AdminController::class, 'audit']);
    });
});
