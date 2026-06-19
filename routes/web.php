<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/img/{path}', [ImageController::class, 'show'])
    ->where('path', '.*')
    ->name('image.show');