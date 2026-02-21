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
        // Domínios permitidos
        $allowedOrigins = [
            'http://localhost:5173',  // Desenvolvimento Vite
            'http://localhost:3000',  // Alternativa
            'http://localhost:8080',  // Alternativa
            'https://localhost:5173', // HTTPS local
            // Em produção: adicionar seus domínios reais
            // 'https://seu-dominio.com',
            // 'https://www.seu-dominio.com',
        ];

        $origin = $request->header('Origin');
        $isAllowedOrigin = $origin && in_array($origin, $allowedOrigins);

        // Headers CORS que serão retornados
        $corsHeaders = [];

        if ($isAllowedOrigin) {
            $corsHeaders = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD',
                'Access-Control-Allow-Headers' => 'Accept, Authorization, Content-Type, X-CSRF-Token, X-Requested-With, X-Request-ID',
                'Access-Control-Expose-Headers' => 'Authorization, X-Total-Count, X-Page-Number, Content-Length',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age' => '3600',
            ];
        }

        // Se a requisição é uma pré-verificação OPTIONS, responder imediatamente
        if ($request->isMethod('OPTIONS')) {
            return response('', 200, $corsHeaders);
        }

        // Processar a requisição normal
        $response = $next($request);

        // Adicionar headers CORS à resposta
        foreach ($corsHeaders as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}
