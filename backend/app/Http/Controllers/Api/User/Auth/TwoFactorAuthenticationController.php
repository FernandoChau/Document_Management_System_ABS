<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Enable two-factor authentication for the user.
     * Gera um codigo QR e recovery codes para o utilizador.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        // Se ja tem 2FA ativado, retorna erro
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_already_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);

        // Gera novo secret e recovery codes
        $secret = $provider->generateSecretKey();
        $recoveryCodes = $provider->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => $this->encodeRecoveryCodes($recoveryCodes),
            'two_factor_confirmed_at' => null,
        ])->save();

        $qrCode = $provider->qrCodeUrl(
            $user->name,
            $user->email,
            $secret
        );

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_qr_generated'),
            'data' => [
                'qr_code' => $qrCode,
                'recovery_codes' => $recoveryCodes,
            ],
        ], 200);
    }

    /**
     * Confirmar e guardar o estado final do two-factor.
     * O utilizador fornece um codigo 6-digitos para confirmar.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
        ], [
            'code.required' => 'O codigo de autenticacao e obrigatorio.',
            'code.digits' => 'O codigo deve ter exatamente 6 digitos.',
        ]);

        $user = $request->user();

        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_already_enabled'),
            ], 400);
        }

        if (!$user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $secret = decrypt($user->two_factor_secret);

        if (!$provider->verify($secret, $request->code)) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_code_invalid'),
            ], 400);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_confirmed'),
            'recovery_codes' => $this->decodeRecoveryCodes($user->two_factor_recovery_codes),
        ], 200);
    }

    /**
     * Desativar two-factor authentication.
     */
    public function disable(Request $request)
    {
        $user = $request->user();

        if (!$user->two_factor_secret) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

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
            'two_factor_enabled' => (bool) ($user->two_factor_secret && $user->two_factor_confirmed_at),
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

        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $recoveryCodes = $provider->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => $this->encodeRecoveryCodes($recoveryCodes),
        ])->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.two_factor_codes_regenerated'),
            'recovery_codes' => $recoveryCodes,
        ], 200);
    }

    /**
     * Verificar codigo de dois fatores durante login/acoes sensiveis.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'O codigo de autenticacao e obrigatorio.',
        ]);

        $user = $request->user();

        if (!$user->two_factor_secret || !$user->two_factor_confirmed_at) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.two_factor_not_enabled'),
            ], 400);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $code = $request->code;

        // Tenta validar com o codigo de 6 digitos
        if (strlen($code) === 6 && ctype_digit($code)) {
            if ($provider->verify(decrypt($user->two_factor_secret), $code)) {
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

    private function encodeRecoveryCodes(array $codes): string
    {
        return json_encode(
            collect($codes)->map(fn($code) => ['code' => $code, 'used_at' => null])->all()
        );
    }

    private function decodeRecoveryCodes(?string $codes): array
    {
        if (!$codes) {
            return [];
        }

        return collect(json_decode($codes, true))
            ->pluck('code')
            ->filter()
            ->values()
            ->all();
    }
}
