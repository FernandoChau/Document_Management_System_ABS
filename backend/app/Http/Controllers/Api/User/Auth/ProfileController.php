<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => __('messages.current_password_incorrect')], 403);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json(['message' => __('messages.password_updated_success')]);
    }

    // Define password na primeira autenticação (apenas para utilizadores criados pelo admin)
    public function setInitialPassword(Request $request)
    {
        $request->validate([
            // 'password' => 'required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            'password' => 'required|confirmed|min:8',
        ], [
            'password.required' => 'A password é obrigatória.',
            'password.confirmed' => 'As passwords não correspondem.',
            'password.min' => 'A password deve ter no mínimo 8 caracteres.',
            'password.regex' => 'A password deve conter pelo menos uma letra maiúscula, uma letra minúscula, um número e um caractere especial (@$!%*?&).',
        ]);

        $user = $request->user();

        // Verifica se a conta foi ativada pelo admin
        if (!$user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.account_not_activated'),
            ], 403);
        }

        // Verifica se já autenticou (já tem password)
        if ($user->has_authenticated) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.password_already_set'),
            ], 403);
        }

        $user->password = bcrypt($request->password);
        $user->has_authenticated = true;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.password_set_success'),
            'user' => $user,
        ], 200);
    }
}
