<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates actions a verified volunteer can take at an unstaffed point —
 * community updates, resource-status updates, crowd-density reports
 * (FR-009, FR-010). Staff and admins pass too, since they're a superset
 * of what a volunteer can do.
 * Register as ->middleware('is_volunteer').
 */
class EnsureIsVolunteer
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isVolunteerOrStaff(), 403, 'Volunteer access required.');

        return $next($request);
    }
}
