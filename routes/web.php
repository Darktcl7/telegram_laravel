<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StokController; // Pastikan ini ada
use App\Http\Controllers\TelegramController; // Tambahkan jika perlu
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\PromosiMedsosController;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// --- RUTE UNTUK LAPORAN PENJUALAN ---
// Menampilkan form (method GET)
Route::get('/laporan', [LaporanController::class, 'showForm'])->name('laporan.form');
// Menyimpan data dari form (method POST)
Route::post('/laporan', [LaporanController::class, 'submit'])->name('laporan.store');


// --- RUTE UNTUK LAPORAN STOK ---
// Menampilkan halaman stok (method GET)
Route::get('/stok', [StokController::class, 'showForm'])->name('stok.form');
// Menyimpan data dari form (method POST)
Route::post('/stok', [StokController::class, 'store'])->name('stok.store');

// --- RUTE UNTUK LAPORAN FOLLOWER ---
Route::get('/follower', [FollowerController::class, 'form'])->name('follower.form');
Route::post('/follower', [FollowerController::class, 'store'])->name('follower.store');

Route::get('/promosi', [PromosiMedsosController::class, 'form'])->name('promosi.form');
Route::post('/promosi', [PromosiMedsosController::class, 'store'])->name('promosi.store');

// --- RUTE UNTUK AUTENTIKASI ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// Grup rute yang hanya bisa diakses setelah login
Route::middleware(['auth'])->group(function () {
    // Letakkan rute yang butuh login di dalam sini
});
