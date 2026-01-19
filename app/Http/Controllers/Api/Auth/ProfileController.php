<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Atualiza senha do usuário autenticado
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = $request->user();

        if (!\hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Senha atual incorreta'], 403);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => 'Senha atualizada com sucesso']);
    }
}
