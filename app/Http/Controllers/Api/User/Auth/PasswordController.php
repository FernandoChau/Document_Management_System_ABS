<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    // Envia email de reset
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email|ends_with:@abspro.co.mz|exists:users,email|max:255'], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.exists' => 'Este e-mail não está registado no sistema.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('messages.reset_email_sent')])
            : response()->json(['message' => __('messages.reset_email_failed')], 500);
    }

    // Reset da senha
    public function reset(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
                $user->has_authenticated = true;
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __('messages.password_reset_success')])
            : response()->json(['message' => __('messages.password_reset_failed')], 500);
    }
}
