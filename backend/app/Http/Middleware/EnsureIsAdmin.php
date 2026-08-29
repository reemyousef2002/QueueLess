<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the admin console — distribution point CRUD (FR-013), staff/
 * volunteer account management, priority-registration verification.
 * Register as ->middleware('is_admin').
 */
class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Admin access required.');

        return $next($request);
    }
}
