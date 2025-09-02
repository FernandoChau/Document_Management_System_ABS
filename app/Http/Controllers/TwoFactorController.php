<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Fortify\TwoFactorAuthenticatable;

class TwoFactorController extends Controller
{
    public function confirmPasswordAjax(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Senha incorreta'
            ], 400);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function qrCode()
    {
        $user = Auth::user();

        // Gera a secret temporária se ainda não existir
        if (!$user->two_factor_secret) {
            $user->enableTwoFactorAuthentication();
        }

        $qrCode = $user->twoFactorQrCodeSvg();

        return response()->json(['svg' => $qrCode]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        $valid = $user->validateTwoFactorCode($request->code); // método do Jetstream/Fortify

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Código inválido'
            ], 400);
        }

        // Ativa 2FA definitivamente
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return response()->json(['success' => true]);
    }
}
