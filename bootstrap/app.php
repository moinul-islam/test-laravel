<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ API middleware group
        $middleware->api(append: [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // ✅ Web middleware group এ আপনার visitor location middleware add করুন
        $middleware->web(append: [
            \App\Http\Middleware\SetVisitorLocationPath::class, // ✅ এটি add করুন
        ]);
        
        // ✅ Middleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'visitor.location' => \App\Http\Middleware\SetVisitorLocationPath::class, // ✅ এটিও add করতে পারেন
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();