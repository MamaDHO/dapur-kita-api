<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UlasanController;
use Illuminate\Support\Facades\Route;

// ── Publik ────────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::prefix('resep')->group(function () {
    Route::get('/',        [ResepController::class, 'index']);
    Route::get('/{resep}', [ResepController::class, 'show']);
    // Baca ulasan bisa publik (tidak wajib login)
    Route::get('/{resep}/ulasan', [UlasanController::class, 'index']);
});

// ── Butuh login ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',       [AuthController::class, 'logout']);
    Route::get('/user',          fn(Request $r) => $r->user());
    Route::post('/user/avatar',  [UserController::class, 'uploadAvatar']);

    Route::get('/resep-saya',    [ResepController::class, 'myResep']);

    Route::prefix('resep')->group(function () {
        Route::post('/',             [ResepController::class, 'store']);
        Route::put('/{resep}',       [ResepController::class, 'update']);
        Route::delete('/{resep}',    [ResepController::class, 'destroy']);
        // Tulis ulasan wajib login
        Route::post('/{resep}/ulasan', [UlasanController::class, 'store']);
    });
});