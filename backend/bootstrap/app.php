<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ❌ Remover o middleware HandleCors padrão do Laravel (usa Access-Control-Allow-Origin: *)
        // Ele usa wildcard * que não funciona com credentials (withCredentials: true)
        $middleware->remove(
            \Illuminate\Http\Middleware\HandleCors::class
        );

        // ✅ Aplicar middleware que transforma cookie de API token em header Authorization
        // Isto permite que Sanctum reconheça o token enviado em um cookie HttpOnly
        $middleware->prepend(
            \App\Http\Middleware\CookieToHeaderToken::class
        );

        // ✅ Aplicar nosso middleware de CORS customizado na PILHA GLOBAL
        // Isto garante que pré-verificações OPTIONS também são tratadas correctamente
        // Necessário para suportar cookies HttpOnly com credenciais
        $middleware->prepend(
            \App\Http\Middleware\EnableCorsWithCredentials::class
        );

        // ✅ Registar alias para o middleware de validação de ficheiros
        $middleware->alias([
            'validate.file.access' => \App\Http\Middleware\ValidateFileAccessMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
