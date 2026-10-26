<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para lidar com CORS e permitir credenciais (cookies HttpOnly)
 * 
 * Quando o frontend envia `withCredentials: true`, o servidor DEVE:
 * 1. Especificar um domínio exato (não pode ser wildcard *)
 * 2. Incluir Access-Control-Allow-Credentials: true
 * 3. Permitir os métodos e headers necessários
 * 4. Responder corretamente a requisições OPTIONS (pré-verificações)
 */
class EnableCorsWithCredentials
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        $allowedOrigins = config('app.frontend_origins', []);
        $isAllowedOrigin = $origin && in_array($origin, $allowedOrigins, true);
        $isPreflight = $request->isMethod('OPTIONS');
        $isUnsafeMethod = !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true);

        if ($origin && !$isAllowedOrigin && ($isPreflight || $isUnsafeMethod)) {
            return response()->json(['message' => 'Origin not allowed'], 403);
        }

        $corsHeaders = [];

        if ($isAllowedOrigin) {
            $corsHeaders = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
                'Access-Control-Allow-Headers' => 'Accept, Authorization, Content-Type, X-CSRF-Token, X-Requested-With, X-Request-ID',
                'Access-Control-Expose-Headers' => 'Authorization, X-Total-Count, X-Page-Number, Content-Length, Content-Disposition',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '3600',
            ];
        }

        if ($isPreflight) {
            return response('', 204, $corsHeaders);
        }

        $response = $next($request);

        foreach ($corsHeaders as $header => $value) {
            $response->headers->set($header, $value);
        }

        if ($origin) {
            $response->headers->set('Vary', 'Origin', false);
        }

        return $response;
    }
}
