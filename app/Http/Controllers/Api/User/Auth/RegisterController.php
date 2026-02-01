<?php

namespace App\Http\Controllers\Api\User\Auth;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisterController extends Controller
{
     protected $creator;

    public function __construct(CreatesNewUsers $creator)
    {
        $this->creator = $creator;
    }

    public function register(Request $request)
    {
        // Validação e criação via Fortify
        
        $request->validate([
                'email' => 'required|email|ends_with:@abspro.co.mz|max:255'
            ],
            [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve terminar com @abspro.co.mz.',
            'email.exists' => 'Este e-mail não está registado no sistema.',
            'email.max' => 'O e-mail não pode ter mais de 255 caracteres.',
        ]);

        $user = $this->creator->create($request->all());

        return response()->json([
            'message' => 'Registo efetuado com sucesso',
            'user' => $user
        ], 201);
    }
}
