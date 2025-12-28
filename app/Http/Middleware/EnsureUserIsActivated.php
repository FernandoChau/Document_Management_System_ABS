<?php

namespace App\Http\Middleware;

use Closure;
// use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && !$user->is_activated) {
             // Faz logout corretamente
            Auth::guard('web')->logout();

            // Invalida a sessão e redireciona para login com mensagem
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('login')->withErrors([
                'email' => 'Sua conta está desativada. Contacte o administrador.',
            ]);
        }
        return $next($request);
    }
}
