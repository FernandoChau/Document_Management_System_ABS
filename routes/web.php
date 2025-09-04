<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwoFactorController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use App\Models\Folder;
use App\Models\File;

Route::get('/', function () {
    // return view('abs_dms/accounts/two-factor-challenge-verification');
    // return view('abs_dms/accounts/two-factor-challenge-recovery');
    return view('abs_dms/accounts/signin');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $folders = Folder::get()->sortBy('name');
        $files = File::get()->sortBy('name');
        $parentId = null;
        return view('abs_dms/documents/index', compact('folders', 'files', 'parentId'));
    })->name('dashboard');
});

Route::get('/dashboard/{id}', function ($id) {
    //order folders and files by name
    if (Folder::findOrFail($id)->is_accessible == false)
        return redirect()->back()->with('error', 'A pasta não é acessível.');
    
    $folders = Folder::where('parent_id', $id)->get()->sortBy('name');
    
    $files = File::where('folder_id', $id)->get()->sortBy('name');
    $parentId = $id;

    // Breadcrumbs logic (máximo 5 níveis)
    $breadcrumbs = [];
    $current = Folder::find($id);
    $levels = 0;
    while ($current && $levels < 5) {
        $breadcrumbs[] = $current;
        if (!$current->parent_id) break;
        $current = Folder::find($current->parent_id);
        $levels++;
    }
    $breadcrumbs = array_reverse($breadcrumbs);

    return view('abs_dms/documents/index', compact('folders', 'files', 'parentId', 'breadcrumbs'));
})->name('dashboard.show');


Route::middleware('auth')->group(function () {

    Route::prefix('folders')->group(function () {
        Route::get('/', [FolderController::class, 'index'])->name('folders.index');
        Route::post('/update', [FolderController::class, 'update_aux'])->name('folders.update_aux');
        Route::get('/create', [FolderController::class, 'create'])->name('folders.create');
        Route::post('/', [FolderController::class, 'store'])->name('folders.store');
        Route::get('/{id}', [FolderController::class, 'show'])->name('folders.show');
        Route::get('/{id}/edit', [FolderController::class, 'edit'])->name('folders.edit');
        Route::put('/{id}', [FolderController::class, 'update'])->name('folders.update');
        Route::delete('/{id}', [FolderController::class, 'destroy'])->name('folders.destroy');
        Route::put('disable/{id}', [FolderController::class, 'disable'])->name('folders.disable');
    });

    Route::prefix('files')->group(function () {
        Route::get('/', [FileController::class, 'index'])->name('files.index');
        Route::get('/create', [FileController::class, 'create'])->name('files.create');
        Route::post('/', [FileController::class, 'store'])->name('files.store');
        Route::get('/{id}', [FileController::class, 'show'])->name('files.show');
        Route::get('/{id}/edit', [FileController::class, 'edit'])->name('files.edit');
        Route::put('/{id}', [FileController::class, 'update'])->name('files.update');
        Route::delete('/{id}', [FileController::class, 'destroy'])->name('files.destroy');
        Route::get('/{id}/download', [FileController::class, 'download'])->name('files.download');
        Route::get('/{id}/preview', [FileController::class, 'preview'])->name('files.preview');
    });

    Route::post('/share', [ShareController::class, 'store'])->name('share.store');
    Route::post('/share/{token}/revoke', [ShareController::class, 'revoke'])->name('share.revoke'); // opcional
});

Route::get('/s/{token}', [ShareController::class, 'show'])->name('share.show');

Route::middleware('auth')->group(function () {
    Route::post('/two-factor/confirm-password', [TwoFactorController::class, 'confirmPasswordAjax'])
        ->name('two-factor.confirm-password');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/user/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/account', function () {
        return view('abs_dms/accounts/index');
    })->name('account.index');
});