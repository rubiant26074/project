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
use App\Support\GoogleDriveUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::match(['get', 'post'], '/notifications/read', function (Illuminate\Http\Request $request): Illuminate\Http\RedirectResponse {
        $request->user()?->markNotificationsAsRead();

        $redirectTo = $request->query('redirect', $request->input('redirect'));

        if (filled($redirectTo)) {
            return redirect($redirectTo);
        }

        return back()->with('status', 'Notifikasi berhasil ditandai sudah dibaca.');
    })->name('notifications.read');
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
        Route::post('/{project}/processes/{process}/checklists/{checklist}/document', [ProjectProcessChecklistController::class, 'uploadDocument'])->name('processes.checklists.document.upload');
        Route::delete('/{project}/processes/{process}/checklists/{checklist}', [ProjectProcessChecklistController::class, 'destroy'])->name('processes.checklists.destroy');
        Route::post('/{project}/processes/{process}/comments', [ProjectProcessCommentController::class, 'store'])->name('processes.comments.store');
        Route::delete('/{project}/processes/{process}/comments/{comment}', [ProjectProcessCommentController::class, 'destroy'])->name('processes.comments.destroy');
    });

    Route::get('/google-drive/connect', function (GoogleDriveUploadService $drive): Illuminate\Http\RedirectResponse {
        try {
            return redirect()->away($drive->authorizationUrl());
        } catch (\Throwable $exception) {
            return redirect()
                ->back()
                ->withErrors(['google_drive' => $exception->getMessage()]);
        }
    })->middleware('permission:project_update')->name('google-drive.connect');

    Route::get('/google-drive/setup', function (): string {
        $html = <<<'HTML'
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Google Drive</title>
    <style>
        body{font-family:Segoe UI,Arial,sans-serif;background:#eef1f4;color:#18212b;margin:0;padding:32px}
        main{max-width:720px;margin:auto;background:#fff;border:1px solid #d8dee8;border-radius:12px;padding:24px;box-shadow:0 16px 36px #161e2614}
        h1{margin:0 0 8px;font-size:24px}p{color:#586575;line-height:1.5}label{display:grid;gap:8px;margin:18px 0;font-weight:700}
        textarea,input{font:inherit;border:1px solid #aeb8c2;border-radius:8px;padding:10px;width:100%;box-sizing:border-box}
        textarea{min-height:180px}button,a{display:inline-flex;align-items:center;min-height:38px;padding:0 14px;border-radius:8px;border:0;background:#009530;color:#fff;font-weight:800;text-decoration:none;cursor:pointer}
        .actions{display:flex;gap:10px;flex-wrap:wrap}.hint{font-size:13px}.message{border-radius:8px;padding:10px 12px;margin:14px 0;font-weight:700}.error{background:#fee2e2;color:#991b1b}.status{background:#dcfce7;color:#166534}
    </style>
</head>
<body>
<main>
    <h1>Setup Google Drive</h1>
    <p>Upload file JSON OAuth dari Google Cloud, atau paste seluruh isi JSON ke kotak di bawah. File akan disimpan di storage aplikasi, jadi tidak perlu terminal.</p>
    __MESSAGE__
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="__CSRF__">
        <label>Upload client_secret.json
            <input type="file" name="client_secret_file" accept=".json,application/json">
        </label>
        <label>Atau paste isi JSON
            <textarea name="client_secret_json" placeholder='{"web":{"client_id":"...","client_secret":"...","redirect_uris":["https://pm.berkahcipta.co.id/oauth2callback.php"]}}'></textarea>
        </label>
        <p class="hint">Setelah berhasil, klik Hubungkan Google Drive.</p>
        <div class="actions">
            <button type="submit">Simpan Konfigurasi</button>
            <a href="/google-drive/connect">Hubungkan Google Drive</a>
        </div>
    </form>
</main>
</body>
</html>
HTML;

        $message = '';
        if ($errors = session('errors')) {
            $message = '<div class="message error">' . e($errors->first()) . '</div>';
        } elseif (session('status')) {
            $message = '<div class="message status">' . e(session('status')) . '</div>';
        }

        return str_replace(['__CSRF__', '__MESSAGE__'], [csrf_token(), $message], $html);
    })->middleware('permission:project_update')->name('google-drive.setup');

    Route::post('/google-drive/setup', function (Request $request, GoogleDriveUploadService $drive): Illuminate\Http\RedirectResponse {
        $json = '';

        if ($request->hasFile('client_secret_file')) {
            $json = file_get_contents($request->file('client_secret_file')->getRealPath()) ?: '';
        }

        if (blank($json)) {
            $json = (string) $request->input('client_secret_json', '');
        }

        try {
            $drive->storeClientSecret($json);
        } catch (\Throwable $exception) {
            return redirect()
                ->route('google-drive.setup')
                ->withErrors(['google_drive' => $exception->getMessage()]);
        }

        return redirect()
            ->route('google-drive.connect')
            ->with('status', 'Konfigurasi Google Drive berhasil disimpan.');
    })->middleware('permission:project_update')->name('google-drive.setup.store');

    Route::get('/oauth2callback.php', function (Request $request, GoogleDriveUploadService $drive): Illuminate\Http\RedirectResponse {
        abort_unless($request->filled('code'), 422, 'Kode otorisasi Google tidak ditemukan.');

        try {
            $drive->exchangeCode((string) $request->query('code'));
        } catch (\Throwable $exception) {
            return redirect()
                ->route('dashboard')
                ->withErrors(['google_drive' => $exception->getMessage()]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Google Drive berhasil terhubung.');
    })->middleware('permission:project_update')->name('google-drive.callback');

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
