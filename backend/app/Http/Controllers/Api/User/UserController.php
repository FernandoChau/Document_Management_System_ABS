<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $users = User::query()->latest('created_at')->get();

        return response()->json([
            'status' => 'success',
            'users' => $users,
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function show(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.user_not_found'),
            ], 404, ['Content-Type' => 'application/json']);
        }

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|email:rfc,dns|ends_with:@abspro.co.mz|max:255|unique:users',
            'role' => 'nullable|string|in:admin,user',
            'phone' => 'nullable|digits:9',
            'profession' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'O nome e obrigatorio.',
            'name.string' => 'O nome deve ser um texto valido.',
            'name.max' => 'O nome nao pode ter mais de 255 caracteres.',

            'email.required' => 'O e-mail e obrigatorio.',
            'email.string' => 'O e-mail deve ser um texto valido.',
            'email.email' => 'Informe um endereco de e-mail valido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.unique' => 'Este e-mail ja esta registado no sistema.',
            'email.max' => 'O e-mail nao pode ter mais de 255 caracteres.',

            'role.string' => 'O perfil deve ser um texto valido.',
            'role.in' => 'O perfil selecionado e invalido.',

            'phone.digits' => 'O numero de telefone deve conter exatamente 9 digitos.',
            'profession.string' => 'A profissao deve ser um texto valido.',
            'profession.max' => 'A profissao nao pode ter mais de 100 caracteres.',
            'is_active.boolean' => 'O estado do utilizador deve ser verdadeiro ou falso.',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role ?? 'user';
        $user->phone = $request->phone;
        $user->profession = $request->profession;
        $user->is_active = false;
        $user->has_authenticated = false;
        $user->save();

        // Envia link de definicao de password para o novo utilizador
        $status = Password::sendResetLink(['email' => $user->email]);
        $message = $status === Password::RESET_LINK_SENT
            ? __('messages.user_created_with_reset_sent')
            : __('messages.user_created_reset_failed');

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'user' => $user,
            'reset_email_status' => $status === Password::RESET_LINK_SENT ? 'sent' : 'failed',
        ], 201, ['Content-Type' => 'application/json']);
    }

    public function update(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.user_not_found'),
            ], 404, ['Content-Type' => 'application/json']);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|email:rfc,dns|ends_with:@abspro.co.mz|max:255|unique:users,email,' . $user->id,
            'role' => 'nullable|string|in:admin,user',
            'phone' => 'nullable|digits:9',
            'profession' => 'nullable|string|max:100',
        ], [
            'name.required' => 'O nome e obrigatorio.',
            'name.string' => 'O nome deve ser um texto valido.',
            'name.max' => 'O nome nao pode ter mais de 255 caracteres.',

            'email.required' => 'O e-mail e obrigatorio.',
            'email.string' => 'O e-mail deve ser um texto valido.',
            'email.email' => 'Informe um endereco de e-mail valido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.unique' => 'Este e-mail ja esta registado no sistema.',
            'email.max' => 'O e-mail nao pode ter mais de 255 caracteres.',

            'role.string' => 'O perfil deve ser um texto valido.',
            'role.in' => 'O perfil selecionado e invalido.',

            'phone.digits' => 'O numero de telefone deve conter exatamente 9 digitos.',
            'profession.string' => 'A profissao deve ser um texto valido.',
            'profession.max' => 'A profissao nao pode ter mais de 100 caracteres.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role ?? $user->role;
        $user->phone = $request->phone;
        $user->profession = $request->profession;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_updated_named', ['name' => $user->name]),
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function deactivate(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.user_not_found'),
            ], 404, ['Content-Type' => 'application/json']);
        }

        $user->is_active = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.user_deactivated_named', ['name' => $user->name]),
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    public function activate(Request $request, string $id)
    {
        $this->ensureAdmin($request);

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

    public function redefinePassword(Request $request, string $id)
    {
        $this->ensureAdmin($request);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilizador não encontrado.',
            ], 404, ['Content-Type' => 'application/json']);
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required' => 'A password é obrigatória.',
            'password.min' => 'A password deve ter pelo menos 6 caracteres.',
            'password.confirmed' => 'As passwords não coincidem.',
        ]);

        $user->password = bcrypt($request->password);
        $user->has_authenticated = true;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password redefinida com sucesso para o utilizador ' . $user->name,
            'user' => $user,
        ], 200, ['Content-Type' => 'application/json']);
    }

    private function ensureAdmin(Request $request): void
    {
        $currentUser = $request->user();

        if (!$currentUser || !$currentUser->isAdmin()) {
            throw new HttpResponseException(response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403));
        }
    }
}
