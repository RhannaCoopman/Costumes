<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/', [PostController::class, 'feed'])->name('feed.list');
    Route::get('/post/{post:uuid}', [PostController::class, 'post'])->name('post.detail');
    Route::get('new', [PostController::class, 'create'])->name('post.create');

    Route::post('/upload-images', [ImageController::class, 'store'])->name('upload.images.store');
    Route::delete('/delete-image/{id}', [ImageController::class, 'destroy'])->name('delete.image');

    Route::get('/save-post', [PostController::class, 'get']);

    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{post}/toggle-save', [PostController::class, 'toggleSave']);

    Route::view('/test', 'test');
});

require __DIR__ . '/auth.php';
