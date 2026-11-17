<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 🔹 Подключаем алиасы для маршрутов
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guide' => \App\Http\Middleware\GuideMiddleware::class,
            'client' => \App\Http\Middleware\ClientMiddleware::class,
        ]);

        // 🔹 Добавляем глобально для всех web-запросов
        $middleware->web(append: [
            \App\Http\Middleware\BlockedUserMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
