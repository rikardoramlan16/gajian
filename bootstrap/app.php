<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ForceRequestHostUrl;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ForceRequestHostUrl::class,
        ]);

        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // Pengguna yang sudah login diarahkan ke Dashboard Admin, bukan ke root
        // (root mengarah kembali ke login) agar tidak terjadi looping.
        $middleware->redirectUsersTo('/admin');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
