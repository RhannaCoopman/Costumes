<?php

use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [PostController::class, 'feed'])->name('feed.list');


Route::namespace('Api')
    ->group(function () {
        Route::get('/', [PostController::class, 'feed'])->name('feed.list');

        Route::post('/save-post', [PostController::class, 'store']);

        Route::get('/save-post', [PostController::class, 'get']);

        Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    });
