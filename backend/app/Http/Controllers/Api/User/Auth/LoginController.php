<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            // avoid leaking which condition failed
            return response()->json([
                'message' => __('messages.login_invalid_credentials')
            ], 401);
        }

        $user = $request->user();

        // Verifica se o utilizador foi ativado pelo admin
        if (!$user->is_active) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => __('messages.account_not_activated'),
                'requires_activation' => true
            ], 403);
        }

        // Verifica se o utilizador já criou a sua senha (primeira autenticação)
        if (!$user->has_authenticated) {
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => __('messages.need_password_setup'),
                'requires_password_change' => true,
                'email' => $user->email
            ], 403);
        }

        // Cria token para SPA / API
        $token = $user->createToken('dms_api')->plainTextToken;

        // SEGURANÇA: Define o token em um cookie HttpOnly (não retorna no JSON)
        // - HttpOnly: JavaScript não consegue acessar (mitiga XSS)
        // - Secure: Cookie só é enviado em HTTPS
        // - SameSite: Protege contra CSRF
        $response = response()->json([
            'message' => 'Login efetuado com sucesso',
            'user' => $user
        ]);

        // Sintaxe: cookie(name, value, minutes, path, domain, secure, httpOnly, raw, sameSite)
        $response->cookie(
            'api_token',           // name
            $token,                // value
            60 * 24,               // minutes (24 horas)
            '/',                   // path
            null,                  // domain
            true,                  // secure (apenas HTTPS)
            true,                  // httpOnly (protege contra XSS)
            false,                 // raw
            'lax'                  // sameSite (protege contra CSRF)
        );

        return $response;
    }
}
