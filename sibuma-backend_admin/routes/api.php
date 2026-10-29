<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UsahaController;
use App\Http\Controllers\API\PendapatanController;
use App\Http\Controllers\API\PasswordController; // ✅ Tambahan baru

// =========================================================================
// 🔓 ZONA PUBLIK (Bisa diakses tanpa login/token)
// =========================================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ✅ Lupa sandi - kirim email reset (tidak perlu login)
Route::post('/password/forgot', [PasswordController::class, 'forgot']);


// =========================================================================
// 🔒 ZONA PRIVAT (Wajib menyertakan Token Login dari Flutter)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- AUTHENTICATION ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ✅ Ubah kata sandi (perlu login)
    Route::post('/password/change', [PasswordController::class, 'change']);

    // --- PROFIL USAHA ---
    Route::get('/usaha', [UsahaController::class, 'index']);
    Route::put('/usaha/{id}', [UsahaController::class, 'update']);

    // --- PELAPORAN DATA BULANAN ---
    Route::post('/pendapatan', [PendapatanController::class, 'store']);
    Route::put('/pendapatan/{id}', [PendapatanController::class, 'update']);
    Route::delete('/pendapatan/{id}', [PendapatanController::class, 'destroy']);

    // --- RIWAYAT & STATISTIK ---
    Route::get('/pendapatan', [PendapatanController::class, 'index']);
    Route::get('/pendapatan/statistics', [PendapatanController::class, 'statistics']);

    // --- ROUTE DEFAULT BAWAAN LARAVEL ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});