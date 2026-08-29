<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DistributionPoint;
use App\Models\User;

/**
 * NFR-05: "Location staff and volunteers shall only be able to manage or
 * update the distribution points assigned to them." Admins bypass the
 * assignment check.
 */
trait AuthorizesDistributionPointAccess
{
    protected function ensureAssignedToPoint(User $user, DistributionPoint $point): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $assigned = $user->staffAssignments()
            ->where('distribution_point_id', $point->id)
            ->exists();

        abort_unless($assigned, 403, 'You are not assigned to this distribution point.');
    }
}
