<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc,dns', 'ends_with:gmail.com', 'max:255', 'unique:users'],
            'phone' => ['required', 'digits:9'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ],[
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve conter apenas texto.',
            'name.min' => 'O nome deve ter pelo menos :min caracteres.',
            'name.max' => 'O nome não pode ultrapassar :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve ser do domínio @abspro.co.mz.',
            'email.unique' => 'Este e-mail já está registado.',

            'phone.digits' => 'O telefone deve ter exatamente 9 dígitos.',
            'phone.required' => 'O seu nr de telefone obrigatório.',

            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma sequência de caracteres.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não coincide.',

        ])->validate();

        $phone = $input['phone'];
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $phone,
            'password' => Hash::make($input['password']),
            'is_activated' => false,
        ]);
    }
}
