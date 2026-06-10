<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'projectOverview'])->name('dashboard');
    Route::get('/dashboard/projects', [DashboardController::class, 'projectOverview'])->name('projects.overview');
    Route::get('/dashboard/projects/{id}', [DashboardController::class, 'projectDetail'])->name('projects.detail');
});

// Authentication routes (uncomment if using Laravel Breeze or Jetstream)
// require __DIR__.'/auth.php';