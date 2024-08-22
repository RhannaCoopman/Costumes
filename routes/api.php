<?php

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\WebshopScraperController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', [PostController::class, 'feed'])->name('feed.list');


Route::namespace('Api')
    ->middleware('web')
    ->group(function () {
        Route::get('/', [PostController::class, 'feed'])->name('feed.list');

        Route::post('/save-post', [PostController::class, 'store']);
        Route::get('/save-post', [PostController::class, 'get']);

        Route::get('/scrape-webshop', [WebshopScraperController::class, 'scrape']);

        Route::get('/community/users', [CommunityController::class, 'getUsers']);
        Route::get('/community/interests', [CommunityController::class, 'interestsAndUserInterests']);

        Route::post('/chat/create', [ChatController::class, 'createChat']);
        Route::post('/chat/{chat:uuid}/newMessage', [ChatController::class, 'store']);
        Route::get('/chat/{chat:uuid}', [ChatController::class, 'fetchChat'])->where('chatUuid', '[a-zA-Z0-9\-]+');

        Route::get('/posts', [PostController::class, 'fetchPosts']);

        Route::get('/tags/search', [TagController::class, 'fetchSearchTags']);
        Route::get('/tags/random', [TagController::class, 'fetchRandomTags']);
    });
