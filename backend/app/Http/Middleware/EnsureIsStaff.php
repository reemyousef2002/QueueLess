<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates Location Staff actions — call next, pause/resume/close a queue,
 * skip/recall a ticket (FR-008, FR-015). Admins pass too: they oversee
 * every point and NFR-05's assignment check already lets them through at
 * the service layer, so the route-level gate stays consistent with that.
 * Register as ->middleware('is_staff').
 */
class EnsureIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isStaffOrAdmin(), 403, 'Staff access required.');

        return $next($request);
    }
}
