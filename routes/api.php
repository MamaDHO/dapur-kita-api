<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Api\ResepController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('resep')->group(function () {
    Route::get('/',          [ResepController::class, 'index']);
    Route::post('/',         [ResepController::class, 'store']);
    Route::get('/{resep}',   [ResepController::class, 'show']);
    Route::post('/{resep}/rating',   [ResepController::class, 'addRating']);
    Route::post('/{resep}/komentar', [ResepController::class, 'addKomentar']);
});