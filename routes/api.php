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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Users Rout
Route::prefix('/')->group(function () {
    Route::post('/registar', [RegisterController::class, 'register']);      //checked
    Route::post('/entrar', [LoginController::class, 'login']);              //checked
    Route::post('/recuperar-senha', [PasswordController::class, 'forgot']); //checked
    Route::post('/redefinir-senha', [PasswordController::class, 'reset']);  //checked

    //Authetication Required
    Route::middleware(['auth:sanctum'])->group(function () {
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
