<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        // Deleta o token de autenticação do banco de dados
        $request->user()->currentAccessToken()->delete();

        // Remove o cookie HttpOnly no navegador enviando um cookie expirado
        $response = response()->json([
            'message' => __('messages.logout_success')
        ]);

        // Sintaxe: cookie(name, value, minutes, path, domain, secure, httpOnly, raw, sameSite)
        $response->cookie(
            'api_token',           // name
            '',                    // value
            -1,                    // minutes (expira imediatamente)
            '/',                   // path
            null,                  // domain
            config('app.env') === 'production',  // secure: apenas HTTPS em produção
            true,                  // httpOnly
            false,                 // raw
            'lax'                  // sameSite
        );

        return $response;
    }
}
