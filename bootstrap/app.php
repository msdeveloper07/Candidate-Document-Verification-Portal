<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureCandidateInviteIsLive;
use App\Http\Middleware\RedirectIfAuthenticatedAs;
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
        $middleware->alias([
            'admin.active'     => EnsureAdminIsActive::class,
            'admin.role'       => EnsureAdminRole::class,
            'candidate.invite' => EnsureCandidateInviteIsLive::class,
            'guest.as'         => RedirectIfAuthenticatedAs::class,
        ]);

        // Unauthenticated visitors go to the right sign-in page for the area.
        $middleware->redirectGuestsTo(function ($request) {
            return $request->is('portal*') || $request->is('verify*')
                ? route('candidate.link-expired')
                : route('admin.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
