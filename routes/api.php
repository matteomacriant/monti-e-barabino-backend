<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PracticeController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/practices/{id}/favorite', [PracticeController::class, 'toggleFavorite']);
    Route::apiResource('practices', PracticeController::class);
    Route::apiResource('attachments', AttachmentController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('saved-searches', SavedSearchController::class)->only(['index', 'store', 'destroy']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('users', \App\Http\Controllers\UserController::class);
});
