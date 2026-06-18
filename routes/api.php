<?php

use App\Http\Controllers\Api\DesktopApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [DesktopApiController::class, 'login']);

Route::middleware('desktop.token')->group(function (): void {
    Route::get('/user', [DesktopApiController::class, 'user']);
    Route::get('/dashboard/stats', [DesktopApiController::class, 'dashboardStats']);
    Route::get('/projects/recent', [DesktopApiController::class, 'recentProjects']);
    Route::get('/dashboard/monthly', [DesktopApiController::class, 'monthly']);
});
