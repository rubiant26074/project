<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterFlowController;
use App\Http\Controllers\MyTaskController;
use App\Http\Controllers\ProjectDashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectProcessCommentController;
use App\Http\Controllers\ProjectProcessChecklistController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', [ProjectDashboardController::class, 'index'])->middleware('permission:dashboard_view')->name('dashboard');
    Route::get('/dashboard-tv1', [ProjectDashboardController::class, 'tv1'])->middleware('permission:dashboard_view')->name('dashboard.tv1');
    Route::get('/tugas-saya', [MyTaskController::class, 'index'])->middleware('permission:process_view')->name('my-tasks.index');

    Route::prefix('projects')->name('projects.')->group(function (): void {
        Route::get('/create', [ProjectController::class, 'create'])->middleware('permission:project_create')->name('create');
        Route::post('/', [ProjectController::class, 'store'])->middleware('permission:project_create')->name('store');
        Route::get('/{project}/edit', [ProjectController::class, 'edit'])->middleware('permission:project_update')->name('edit');
        Route::put('/{project}', [ProjectController::class, 'update'])->middleware('permission:project_update')->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->middleware('permission:project_delete')->name('destroy');

        Route::get('/{project}/tv-dashboard', [ProjectDashboardController::class, 'tvProject'])->middleware('permission:project_view')->name('tv');
        Route::get('/{project}', [ProjectDashboardController::class, 'show'])->middleware('permission:project_view')->name('show');
        Route::get('/{project}/processes/{process}', [ProjectDashboardController::class, 'showProcess'])->middleware('permission:process_view')->name('processes.show');
        Route::put('/{project}/processes/{process}/target', [ProjectDashboardController::class, 'updateProcessTarget'])->name('processes.target.update');
        Route::post('/{project}/processes/{process}/checklists', [ProjectProcessChecklistController::class, 'store'])->name('processes.checklists.store');
        Route::put('/{project}/processes/{process}/checklists', [ProjectProcessChecklistController::class, 'bulkUpdate'])->name('processes.checklists.bulk-update');
        Route::put('/{project}/processes/{process}/checklists/{checklist}', [ProjectProcessChecklistController::class, 'update'])->name('processes.checklists.update');
        Route::delete('/{project}/processes/{process}/checklists/{checklist}', [ProjectProcessChecklistController::class, 'destroy'])->name('processes.checklists.destroy');
        Route::post('/{project}/processes/{process}/comments', [ProjectProcessCommentController::class, 'store'])->name('processes.comments.store');
        Route::delete('/{project}/processes/{process}/comments/{comment}', [ProjectProcessCommentController::class, 'destroy'])->name('processes.comments.destroy');
    });

    Route::prefix('master-flows')->name('master-flows.')->middleware('permission:master_flow_manage')->group(function (): void {
        Route::get('/', [MasterFlowController::class, 'index'])->name('index');
        Route::post('/', [MasterFlowController::class, 'store'])->name('store');
        Route::get('/{masterFlow}/edit', [MasterFlowController::class, 'edit'])->name('edit');
        Route::put('/{masterFlow}', [MasterFlowController::class, 'update'])->name('update');
        Route::delete('/{masterFlow}', [MasterFlowController::class, 'destroy'])->name('destroy');
        Route::put('/{masterFlow}/layout', [MasterFlowController::class, 'updateLayout'])->name('layout.update');
        Route::post('/{masterFlow}/steps', [MasterFlowController::class, 'storeStep'])->name('steps.store');
        Route::put('/{masterFlow}/steps/{step}', [MasterFlowController::class, 'updateStep'])->name('steps.update');
        Route::delete('/{masterFlow}/steps/{step}', [MasterFlowController::class, 'destroyStep'])->name('steps.destroy');
        Route::post('/{masterFlow}/connections', [MasterFlowController::class, 'storeConnection'])->name('connections.store');
        Route::delete('/{masterFlow}/connections/{connection}', [MasterFlowController::class, 'destroyConnection'])->name('connections.destroy');
        Route::post('/{masterFlow}/steps/{step}/checklists', [MasterFlowController::class, 'storeChecklist'])->name('steps.checklists.store');
        Route::put('/{masterFlow}/steps/{step}/checklists/{checklist}', [MasterFlowController::class, 'updateChecklist'])->name('steps.checklists.update');
        Route::delete('/{masterFlow}/steps/{step}/checklists/{checklist}', [MasterFlowController::class, 'destroyChecklist'])->name('steps.checklists.destroy');
    });

    Route::prefix('roles')->name('roles.')->middleware('permission:role_manage')->group(function (): void {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::put('/permissions', [RoleController::class, 'updatePermissions'])->name('permissions.update');
        Route::put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('users')->name('users.')->middleware('permission:user_manage')->group(function (): void {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::post('/{user}/approve', [UserManagementController::class, 'approve'])->name('approve');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    });
});
