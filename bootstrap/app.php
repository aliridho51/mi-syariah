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
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware pembatas role: ->middleware('role:admin,kepala_madrasah')
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);

        // Izinkan callback otomatis dari Payment Gateway Midtrans tanpa token CSRF
        $middleware->validateCsrfTokens(except: [
            'webhook/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Jika sesi/token CSRF kedaluwarsa (419), kembalikan ke halaman sebelumnya dengan pesan ramah
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if ($response->getStatusCode() === 419) {
                return redirect()->route('login')
                    ->with('status', 'Sesi halaman sempat kedaluwarsa, silakan klik Masuk kembali.');
            }

            return $response;
        });
    })->create();
