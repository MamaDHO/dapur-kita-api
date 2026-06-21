<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('resep')->group(function () {
    Route::get('/',          [ResepController::class, 'index']);
    Route::get('/{resep}',   [ResepController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profil
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);

    // Resep milik user yang login
    Route::get('/resep-saya', [ResepController::class, 'myResep']);

    Route::prefix('resep')->group(function () {
        Route::post('/',                 [ResepController::class, 'store']);
        Route::put('/{resep}',           [ResepController::class, 'update']);
        Route::delete('/{resep}',        [ResepController::class, 'destroy']);
        Route::post('/{resep}/rating',   [ResepController::class, 'addRating']);
        Route::post('/{resep}/komentar', [ResepController::class, 'addKomentar']);
    });
});