<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FoundStatusController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/posts', [PostController::class, 'userPosts']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
    Route::delete('/posts/{post}', [PostController::class, 'destroy']);
    Route::post('/posts/{post}/complaints', [ComplaintController::class, 'store']);
    Route::post('/posts/{post}/toggle-status', [PostController::class, 'toggleStatus']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/districts', [DistrictController::class, 'index']);
Route::get('/found-statuses', [FoundStatusController::class, 'index']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/search', [PostController::class, 'search']);
Route::get('/posts/{id}', [PostController::class, 'show']);

