<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        $User = User::all()->where('is_activated',true)->sortBy("name");
        return view("abs_dms/accounts/index",compact("User"));
    }
    
    public function pending(){
        $User = User::all()->where('created_by',null)->sortBy("created_at");
        return view("abs_dms/accounts/pending",compact("User"));
    }

    public function deactivated(){
        $User = User::all()->where('deactivated_at',!null)->sortBy("deactivated_at");
        return view("abs_dms/accounts/deactivated",compact("User"));
    }

    public function create(Request $request){

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|ends_with:fernando.co.mz|unique:users,email',
            'phone' => 'nullable|digits:9',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user,gestor', 
            'profession' => 'nullable|string|max:100',
            'is_activated' => 'required|boolean',
        ], [
            // Mensagens personalizadas
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve conter apenas texto.',
            'name.min' => 'O nome deve ter pelo menos :min caracteres.',
            'name.max' => 'O nome não pode ultrapassar :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve ser do domínio @fernando.co.mz.',
            'email.unique' => 'Este e-mail já está registado.',

            'phone.digits' => 'O telefone deve ter exatamente 9 dígitos.',

            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma sequência de caracteres.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não coincide.',

            'role.required' => 'O papel do utilizador é obrigatório.',
            'role.in' => 'O papel selecionado é inválido.',

            'profession.string' => 'A profissão deve conter apenas texto.',
            'profession.max' => 'A profissão não pode ultrapassar :max caracteres.',

            'is_activated.required' => 'É necessário indicar se o utilizador está ativo.',
            'is_activated.boolean' => 'O campo de ativação deve ser verdadeiro ou falso.',
        ]);

        $user = User::create(['name' => $request->name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'password'=> $request->password,
            'role'=> $request->role,
            'profission' => $request->profission,
            'created_by'=> auth()->user() ? auth()->user()->id : null,
            'is_activated' => auth()->user() ? true : false,
        ]);

        if(!$user)
            return redirect()->back()->with('error','Falha ao criar o utilizador');

        if(!auth()->user())
            return redirect()->back()->with('Alert','Contacte o administrador para lhe dar acesso a App');
        
        return redirect()->back()->with('success','Utilizador Criado Com Sucesso.');
    }

    public function update(Request $request, $id){
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|ends_with:fernando.co.mz|unique:users,email',
            'phone' => 'nullable|digits:9',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user', 
            'profession' => 'nullable|string|max:100',
            'is_activated' => 'required|boolean',
        ], [
            // Mensagens personalizadas
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve conter apenas texto.',
            'name.min' => 'O nome deve ter pelo menos :min caracteres.',
            'name.max' => 'O nome não pode ultrapassar :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve ser do domínio @fernando.co.mz.',
            'email.unique' => 'Este e-mail já está registado.',

            'phone.digits' => 'O telefone deve ter exatamente 9 dígitos.',

            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser uma sequência de caracteres.',
            'password.min' => 'A senha deve ter pelo menos :min caracteres.',
            'password.confirmed' => 'A confirmação da senha não coincide.',

            'role.required' => 'O papel do utilizador é obrigatório.',
            'role.in' => 'O papel selecionado é inválido.',

            'profession.string' => 'A profissão deve conter apenas texto.',
            'profession.max' => 'A profissão não pode ultrapassar :max caracteres.',

            'is_activated.required' => 'É necessário indicar se o utilizador está ativo.',
            'is_activated.boolean' => 'O campo de ativação deve ser verdadeiro ou falso.',
        ]);
        
        $user = User::update([
            'name' => $request->name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'role'=> $request->role,
            'profission' => $request->profission,
        ]);

        if(!$user)
            return redirect()->back()->with('error','Falha ao atualizar o utilizador');

        
        return redirect()->back()->with('success','Utilizador atualizado com sucesso Com Sucesso.');

    }
    
    public function deativate($id){
        $user = User::find($id);
        
        $user->deactivated_by = auth()->user()->id;
        $user->is_activated = false;
        $user->deactivated_at = now();
        
        if($user->save())
            return redirect()->back()->with('error','Erro ao desativado utilizador');

        return redirect()->back()->with('success','Utilizador desativado com sucesso');
    }

    public function ativate($id){
        $user = User::find($id);
        
        $user->activated_by = auth()->user()->id;
        $user->is_activated = true;
        $user->activated_at = now();
        
        if($user->save())
            return redirect()->back()->with('error','Erro ao ativar utilizador');

        return redirect()->back()->with('success','Utilizador ativado com sucesso');
    }

    public function confirm (Request $request, $id){  
        $user = User::find($id);
        
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:rfc,dns|ends_with:gmail.com',
            // 'email' => 'required|email:rfc,dns|ends_with:abspro.co.mz',
            'phone' => 'nullable|digits:9',
            'role' => 'required|string|in:admin,user,gestor', 
            'profession' => 'nullable|string|max:100',
        ], [
            // Mensagens personalizadas
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve conter apenas texto.',
            'name.min' => 'O nome deve ter pelo menos :min caracteres.',
            'name.max' => 'O nome não pode ultrapassar :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.ends_with' => 'O e-mail deve ser do domínio @abspro.co.mz.',
            'email.unique' => 'Este e-mail já está registado.',

            'phone.digits' => 'O telefone deve ter exatamente 9 dígitos.',

            'role.required' => 'O papel do utilizador é obrigatório.',
            'role.in' => 'O papel selecionado é inválido.',

            'profession.string' => 'A profissão deve conter apenas texto.',
            'profession.max' => 'A profissão não pode ultrapassar :max caracteres.',
        ]);

        
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->profession = $request->profession;
        

        $user->created_by = auth()->user()->id;
        $user->is_activated = true;

        $user->save();
        
        if(!$user)
            return redirect()->back()->with('error','Erro ao ativar utilizador');

        return redirect()->back()->with('success','Utilizador ativado com sucesso');
    }
    
}
