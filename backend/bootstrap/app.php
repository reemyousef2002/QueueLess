<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\EnsureIsStaff;
use App\Http\Middleware\EnsureIsVolunteer;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            // One dedicated middleware per role, rather than only the
            // generic parametrized 'role:x,y' gate above — each is its
            // own class so route intent reads clearly (->middleware('is_staff'))
            // and each role's exact allowance (e.g. admin overriding staff
            // checks) lives in one obvious place.
            'is_admin' => EnsureIsAdmin::class,
            'is_staff' => EnsureIsStaff::class,
            'is_volunteer' => EnsureIsVolunteer::class,
        ]);

        // This is a pure JSON API — there is no web login page to redirect
        // an unauthenticated request to, so always fall through to a JSON
        // 401 instead of Authenticate::redirectTo() trying (and failing)
        // to resolve a "login" route.
        $middleware->redirectGuestsTo(fn () => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
