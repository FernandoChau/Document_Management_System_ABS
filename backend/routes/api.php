<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\PasswordController as ControllersPasswordController;

use App\Http\Controllers\Api\User\Auth\LoginController;
use App\Http\Controllers\Api\User\Auth\LogoutController;
use App\Http\Controllers\Api\User\Auth\PasswordController;
use App\Http\Controllers\Api\User\Auth\ProfileController;
use App\Http\Controllers\Api\User\Auth\RegisterController;
use App\Http\Controllers\Api\User\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FolderController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\DocumentContentController;
use App\Http\Controllers\Api\ShareLinkController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\FolderPermissionController;
use App\Http\Controllers\Api\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('/')->group(function () {
    Route::post('/registar', [RegisterController::class, 'register']);      //checked
    Route::post('/entrar', [LoginController::class, 'login']);              //checked
    Route::post('/recuperar-senha', [PasswordController::class, 'forgot']); //checked
    Route::post('/redefinir-senha', [PasswordController::class, 'reset']);  //checked

    Route::get('/compartilhamentos/{token}', [ShareLinkController::class, 'show']);
    Route::get('/compartilhamentos/{token}/baixar', [ShareLinkController::class, 'download']);

    //Authetication Required
    Route::middleware(['auth:sanctum', 'check.auth.status'])->group(function () {
        Route::post('/sair', [LogoutController::class, 'logout']);                                  //checked
        Route::get('/minha-conta', [ProfileController::class, 'me']);                               //checked
        Route::post('/atualizar-senha', [ProfileController::class, 'updatePassword']);              //checked    

        // Two-Factor Authentication
        Route::prefix('autenticacao-dois-fatores')->group(function () {
            Route::post('/ativar', [TwoFactorAuthenticationController::class, 'enable']);           //Checked
            Route::post('/confirmar', [TwoFactorAuthenticationController::class, 'confirm']);
            Route::post('/desativar', [TwoFactorAuthenticationController::class, 'disable']);
            Route::get('/estado', [TwoFactorAuthenticationController::class, 'status']);
            Route::post('/regenerar-codigos', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes']);
            Route::post('/verificar', [TwoFactorAuthenticationController::class, 'verify']);
        });

        // ==================== DEPARTMENTS ====================
        Route::prefix('departamentos')->group(function () {
            Route::get('/', [DepartmentController::class, 'index']);
            Route::post('/', [DepartmentController::class, 'store']);
            Route::get('/{department}', [DepartmentController::class, 'show']);
            Route::put('/{department}', [DepartmentController::class, 'update']);
            Route::delete('/{department}', [DepartmentController::class, 'destroy']);
            Route::post('/{department}/restaurar', [DepartmentController::class, 'restore']);
            Route::delete('/{department}/permanente', [DepartmentController::class, 'forceDelete']);
            Route::get('/{department}/estatisticas', [DepartmentController::class, 'stats']);
        });

        // ==================== FOLDERS ====================
        Route::prefix('pastas')->group(function () {
            Route::get('/', [FolderController::class, 'index']); // List root or children
            Route::post('/', [FolderController::class, 'store']); // Create folder
            Route::get('/{folder}', [FolderController::class, 'show']);
            Route::put('/{folder}', [FolderController::class, 'update']);
            Route::delete('/{folder}', [FolderController::class, 'destroy']);
            Route::post('/{folder}/restaurar', [FolderController::class, 'restore']);
            Route::get('/{folder}/baixar', [FolderController::class, 'download']); // Download Zip | We need to implement the logic
            Route::get('/{folder}/estatisticas', [FolderController::class, 'stats']);

            // Document Upload inside Folder
            Route::post('/{folder}/upload', [DocumentController::class, 'store']);

            // Folder Permissions
            Route::prefix('{folder}/permissoes')->group(function () {
                Route::get('/', [FolderPermissionController::class, 'index']);
                Route::post('/', [FolderPermissionController::class, 'store']);
                Route::get('/{permission}', [FolderPermissionController::class, 'show']);
                Route::put('/{permission}', [FolderPermissionController::class, 'update']);
                Route::delete('/{permission}', [FolderPermissionController::class, 'destroy']);
                Route::get('/usuario/{userId}/check', [FolderPermissionController::class, 'check']);
            });
        });

        // ==================== DOCUMENTS ====================
        Route::prefix('documentos')->group(function () {
            Route::get('/', [DocumentController::class, 'index']);
            Route::get('/{document}', [DocumentController::class, 'show']); // View
            Route::put('/{document}', [DocumentController::class, 'update']);
            Route::get('/{document}/baixar', [DocumentController::class, 'download']); // Download File
            Route::delete('/{document}', [DocumentController::class, 'destroy']); // Soft Delete
            Route::post('/{document}/restaurar', [DocumentController::class, 'restore']);
            Route::get('/{document}/estatisticas', [DocumentController::class, 'stats']);
            Route::get('/{document}/logs', [AuditLogController::class, 'documentLogs']); // Document Audit Logs

            // Document Content (Extracted Text)
            Route::prefix('{document}/conteudo')->group(function () {
                Route::get('/', [DocumentContentController::class, 'show']);
                Route::put('/', [DocumentContentController::class, 'update']);
                Route::delete('/', [DocumentContentController::class, 'destroy']);
                Route::get('/status', [DocumentContentController::class, 'status']);
            });
        });

        // ==================== DOCUMENT CONTENT SEARCH ====================
        Route::post('/conteudo/buscar', [DocumentContentController::class, 'search']);

        // ==================== SHARE LINKS ====================
        Route::prefix('compartilhamentos')->group(function () {
            Route::get('/', [ShareLinkController::class, 'index']);
            Route::post('/', [ShareLinkController::class, 'store']);
            Route::get('/{shareLink}', [ShareLinkController::class, 'show']);
            Route::put('/{shareLink}', [ShareLinkController::class, 'update']);
            Route::delete('/{shareLink}', [ShareLinkController::class, 'destroy']);
        });

        // ==================== AUDIT LOGS ====================
        Route::prefix('auditoria')->group(function () {
            Route::get('/', [AuditLogController::class, 'index']);
            Route::get('/estatisticas', [AuditLogController::class, 'stats']);
            Route::get('/pasta/{folder}/logs', [AuditLogController::class, 'folderLogs']);
            Route::get('/usuario/{userId}/logs', [AuditLogController::class, 'userLogs']);
            Route::get('/{auditLog}', [AuditLogController::class, 'show']);
        });

        // ==================== FOLDER PERMISSIONS ====================
        Route::prefix('permissoes')->group(function () {
            Route::get('/usuario/{userId}/pastas', [FolderPermissionController::class, 'userFolders']);
        });

        // ==================== DASHBOARD & REPORTS ====================
        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('/departamento/{department}', [DashboardController::class, 'department']);
            Route::get('/pasta/{folder}', [DashboardController::class, 'folder']);
        });

        // Rotas de Utilizadores (Admin)
        Route::prefix('utilizadores')->group(function () {
            Route::get('/', [UserController::class, 'index']);                      //checked
            Route::get('/{id}', [UserController::class, 'show']);                   //checked
            Route::post('/', [UserController::class, 'store']);                     //checked
            Route::put('/{id}', [UserController::class, 'update']);                 //checked
            Route::put('/{id}/desativar', [UserController::class, 'deactivate']);   //checked
            Route::put('/{id}/ativar', [UserController::class, 'activate']);        //checked
        });
    });
});
