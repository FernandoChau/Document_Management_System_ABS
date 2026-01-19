<?php

namespace App\Http\Controllers\Api\Auth;

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
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = $request->user();

        // Cria token para SPA / API
        $token = $user->createToken('dms_api')->plainTextToken;

        return response()->json([
            'message' => 'Login efetuado com sucesso',
            'token' => $token,
            'user' => $user
        ]);
    }
}
