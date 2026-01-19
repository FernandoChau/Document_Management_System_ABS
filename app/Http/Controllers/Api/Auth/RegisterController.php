<?php

namespace App\Http\Controllers\Api\Auth;


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
        $user = $this->creator->create($request->all());

        return response()->json([
            'message' => 'Registo efetuado com sucesso',
            'user' => $user
        ], 201);
    }
}
