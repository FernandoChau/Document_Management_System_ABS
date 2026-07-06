<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        // Verifica se o utilizador tem 2FA ativo e confirmado
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            // Gera um token temporário de pré-autenticação (válido por 10 minutos)
            $twoFactorToken = Str::random(64);

            // Guarda o ID do utilizador na cache ligado ao token temporário
            Cache::put("2fa_pending:{$twoFactorToken}", $user->id, now()->addMinutes(10));

            // Faz logout da sessão web (não emite token Sanctum ainda)
            Auth::logout();

            return response()->json([
                'status' => 'two_factor_required',
                'message' => 'Autenticação de dois fatores necessária.',
                'two_factor_token' => $twoFactorToken,
                'requires_two_factor' => true,
            ], 200);
        }

        // Sem 2FA: cria token para SPA / API normalmente
        return $this->issueSessionToken($user);
    }

    /**
     * Verificar o código 2FA durante o processo de login.
     * Recebe o token temporário de pré-autenticação e o código TOTP/recovery.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'two_factor_token' => 'required|string',
            'code'             => 'required|string',
        ]);

        $cacheKey = "2fa_pending:{$request->two_factor_token}";
        $userId   = Cache::get($cacheKey);

        if (!$userId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Sessão de verificação expirada. Por favor, inicie sessão novamente.',
            ], 401);
        }

        $user = \App\Models\User::find($userId);

        if (!$user || !$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            Cache::forget($cacheKey);
            return response()->json([
                'status'  => 'error',
                'message' => 'Utilizador inválido ou 2FA não configurado.',
            ], 400);
        }

        $provider = app(\Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class);
        $code     = $request->code;

        // Tenta validar com código TOTP de 6 dígitos
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($provider->verify(decrypt($user->two_factor_secret), $code)) {
                Cache::forget($cacheKey);
                return $this->issueSessionToken($user);
            }
        }

        // Tenta validar com recovery code
        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        foreach ($recoveryCodes as $key => $recoveryCode) {
            if ($recoveryCode['code'] === $code && !$recoveryCode['used_at']) {
                // Marca o recovery code como usado
                $recoveryCodes[$key]['used_at'] = now();
                $user->forceFill([
                    'two_factor_recovery_codes' => json_encode($recoveryCodes),
                ])->save();

                Cache::forget($cacheKey);
                return $this->issueSessionToken($user);
            }
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Código de verificação inválido ou expirado.',
        ], 400);
    }

    /**
     * Emite o token de sessão Sanctum e define o cookie HttpOnly para o utilizador.
     */
    private function issueSessionToken($user)
    {
        $token = $user->createToken('dms_api')->plainTextToken;

        $response = response()->json([
            'message' => 'Login efetuado com sucesso',
            'user'    => $user
        ]);

        // Sintaxe: cookie(name, value, minutes, path, domain, secure, httpOnly, raw, sameSite)
        $response->cookie(
            'api_token',           // name
            $token,                // value
            60 * 24,               // minutes (24 horas)
            '/',                   // path
            null,                  // domain
            config('app.env') === 'production',  // secure: apenas HTTPS em produção
            true,                  // httpOnly (protege contra XSS)
            false,                 // raw
            'lax'                  // sameSite (protege contra CSRF)
        );

        return $response;
    }
}
