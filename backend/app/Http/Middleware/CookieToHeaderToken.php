<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que lê o token de API do cookie e o coloca no header Authorization
 * 
 * Isto permite que o Sanctum reconheça o token de API enviado em um cookie HttpOnly
 */
class CookieToHeaderToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não houver um header Authorization, procurar no cookie
        if (!$request->hasHeader('Authorization')) {
            $token = $request->cookie('api_token');

            if ($token) {
                // O cookie pode vir URL-encoded, decodificar se necessário
                $decodedToken = urldecode($token);

                // Formatar como Bearer token
                $request->headers->set('Authorization', 'Bearer ' . $decodedToken);
            }
        }

        return $next($request);
    }
}
