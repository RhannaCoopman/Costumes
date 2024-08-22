<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::get('/', [PostController::class, 'feed'])->name('feed.list');
    Route::get('/post/{post:uuid}', [PostController::class, 'post'])->name('post.detail');
    Route::get('new', [PostController::class, 'create'])->name('post.create');
    Route::get('/save-post', [PostController::class, 'get']);

    Route::get('/community', [CommunityController::class, 'feed'])->name('community.feed');

    Route::get('/chats', [ChatController::class, 'list'])->name('chats.list');

    Route::get('/chat/{chat:uuid?}', [ChatController::class, 'openChat'])->name('chats.detail');

    Route::post('/posts/{post}/toggle-like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{post}/toggle-save', [PostController::class, 'toggleSave']);

    Route::view('/test', 'test');
});

require __DIR__ . '/auth.php';
