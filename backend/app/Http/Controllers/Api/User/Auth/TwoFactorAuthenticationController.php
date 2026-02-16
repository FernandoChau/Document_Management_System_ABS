<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     * Gera um código QR e recovery codes para o utilizador.
     */
    public function enable(Request $request)
    {
        
        $user = $request;
        // $user = $request->user();

        // dd();

        // Se já tem 2FA ativado, retorna erro
        if ($user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_already_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);

        // Gera novo secret
        $secret = $provider->generateSecretKey();
        $qrCode = $provider->qrCodeUrl(
            $user->name,
            $user->email,
            $secret
        );

        // Gera recovery codes
        $recoveryCodes = $provider->generateSecretKey();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_qr_generated'),
            'data' => [
                'qr_code' => $qrCode,
                'secret' => $secret,
                'recovery_codes' => $recoveryCodes,
            ],
        ], 200);
    }

    /**
     * Confirmar e guardar o secret do two-factor.
     * O utilizador fornece um código 6-dígitos para confirmar.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'O código de autenticação é obrigatório.',
            'code.digits' => 'O código deve ter exatamente 6 dígitos.',
        ]);

        $user = $request->user();

        // Se já tem 2FA ativado, retorna erro
        if ($user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_already_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);

        // Valida se o código está correto
        if (!$provider->verify(
            decrypt($user->two_factor_secret ?? ''),
            $request->code
        )) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_code_invalid'),
            ], 400);
        }

        // Guarda recovery codes
        $recoveryCodes = $provider->generateRecoveryCodes();
        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(
                collect($recoveryCodes)->map(fn($code) => ['code' => $code, 'used_at' => null])->all()
            ),
        ])->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_confirmed'),
            'recovery_codes' => $recoveryCodes,
        ], 200);
    }

    /**
     * Desativar two-factor authentication.
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $user = $request->user();

        // Se não tem 2FA ativado, retorna erro
        if (!$user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $disable($user);

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_disabled'),
        ], 200);
    }

    /**
     * Obter o estado atual de 2FA do utilizador.
     */
    public function status(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'two_factor_enabled' => (bool) $user->two_factor_secret,
            'recovery_codes_count' => $user->two_factor_recovery_codes
                ? count(json_decode($user->two_factor_recovery_codes, true))
                : 0,
        ], 200);
    }

    /**
     * Regenerar recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        // Se não tem 2FA ativado, retorna erro
        if (!$user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $recoveryCodes = $provider->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode(
                collect($recoveryCodes)->map(fn($code) => ['code' => $code, 'used_at' => null])->all()
            ),
        ])->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_codes_regenerated'),
            'recovery_codes' => $recoveryCodes,
        ], 200);
    }

    /**
     * Verificar código de dois fatores durante login.
     * Este método é chamado quando o utilizador tem 2FA e precisa confirmar.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'O código de autenticação é obrigatório.',
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $code = $request->code;

        // Tenta validar com o código de 6 dígitos
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($provider->verify(
                decrypt($user->two_factor_secret),
                $code
            )) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.two_factor_verified'),
                ], 200);
            }
        }

        // Tenta validar com recovery code
        $recoveryCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        foreach ($recoveryCodes as $key => $recoveryCode) {
            if ($recoveryCode['code'] === $code && !$recoveryCode['used_at']) {
                $recoveryCodes[$key]['used_at'] = now();
                $user->forceFill([
                    'two_factor_recovery_codes' => json_encode($recoveryCodes),
                ])->save();

                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.two_factor_verified_with_recovery'),
                ], 200);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => __('messages.two_factor_code_invalid'),
        ], 400);
    }
}
