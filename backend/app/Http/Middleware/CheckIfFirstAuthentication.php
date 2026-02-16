<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthenticationStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Verifica se a conta foi ativada pelo admin
            if (!$user->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A sua conta ainda não foi ativada. Contacte o administrador.',
                    'requires_activation' => true,
                ], 403);
            }

            // Verifica se já criou a senha (para utilizadores criados pelo admin)
            if (!$user->has_authenticated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Por favor, defina uma password antes de continuar.',
                    'requires_password_change' => true,
                ], 403);
            }
        }

        return $next($request);
    }
}
