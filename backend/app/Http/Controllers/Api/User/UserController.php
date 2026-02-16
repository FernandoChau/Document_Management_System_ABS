<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    function index()
    {
        $user = User::all();

        return response()->json([
            $user,
            'status' => 'success',
        ], 200, ['Content-Type' => 'application/json']);
    }

    function show($id)
    {
        $user = User::find($id);

        return response()->json([
            $user,
            'status' => 'success',

        ], 200, ['Content-Type' => 'application/json']);
    }

    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|email:rfc,dns|ends_with:@abspro.co.mz|max:255|unique:users',
            'role' => 'nullable|string|in:admin,user,gestor',
            'phone' => 'nullable|digits:9',
            'profession' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser um texto válido.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.unique' => 'Este e-mail já está registado no sistema.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',

            'role.required' => 'O perfil é obrigatório.',
            'role.string' => 'O perfil deve ser um texto válido.',
            'role.in' => 'O perfil selecionado é inválido.',

            'phone.digits' => 'O número de telefone deve conter exatamente 9 dígitos.',

            'profession.string' => 'A profissão deve ser um texto válido.',
            'profession.max' => 'A profissão não pode ter mais de 100 caracteres.',

            'is_active.required' => 'O estado do utilizador é obrigatório.',
            'is_active.boolean' => 'O estado do utilizador deve ser verdadeiro ou falso.',
        ]);

        $user = new User();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->phone = $request->phone;
        $user->profession = $request->profession;
        $user->is_active = false;
        $user->has_authenticated = false;

        $user->save();

        // Envia link de definição de password para o novo utilizador
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $message = __('messages.user_created_with_reset_sent');
        } else {
            $message = __('messages.user_created_reset_failed');
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => $user,
            'reset_email_status' => $status === Password::RESET_LINK_SENT ? 'sent' : 'failed',
        ], 201, ['Content-Type' => 'application/json']);
    }

    function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|email:rfc,dns|ends_with:@abspro.co.mz|max:255',
            'role' => 'nullable|string|in:admin,user,gestor',
            'phone' => 'nullable|digits:9',
            'profession' => 'nullable|string|max:100',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser um texto válido.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.unique' => 'Este e-mail já está registado no sistema.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',

            'role.required' => 'O perfil é obrigatório.',
            'role.string' => 'O perfil deve ser um texto válido.',
            'role.in' => 'O perfil selecionado é inválido.',

            'phone.digits' => 'O número de telefone deve conter exatamente 9 dígitos.',

            'profession.string' => 'A profissão deve ser um texto válido.',
            'profession.max' => 'A profissão não pode ter mais de 100 caracteres.',
        ]);

        $user = User::find($id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->phone = $request->phone;
        $user->profession = $request->profession;

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_updated_named', ['name' => $user->name]),
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    function deactivate($id)
    {
        $user = User::find($id);

        $user->is_active = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_deactivated_named', ['name' => $user->name]),
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    function activate($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.user_not_found'),
            ], 404, ['Content-Type' => 'application/json']);
        }

        $user->is_active = true;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_activated_named', ['name' => $user->name]),
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }
}
