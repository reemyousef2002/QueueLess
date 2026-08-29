<?php

namespace App\Services;

use App\Models\DistributionPoint;
use App\Models\ResourceStatus;
use App\Models\User;
use App\Notifications\FavoritePointChangedNotification;
use App\Notifications\ResourceDepletedNotification;

/**
 * FR-003, FR-009: resource-availability tracking per distribution point,
 * updatable by staff or verified volunteers.
 */
class ResourceStatusService
{
    public function update(DistributionPoint $point, string $resourceType, string $availability, User $updatedBy): ResourceStatus
    {
        $status = ResourceStatus::updateOrCreate(
            ['distribution_point_id' => $point->id, 'resource_type' => $resourceType],
            ['availability' => $availability, 'updated_by' => $updatedBy->id],
        );

        $this->notifyFavorites($point, $resourceType, $availability);

        if ($availability === 'depleted') {
            $this->notifyAdminsOfDepletion($point, $resourceType);
        }

        return $status;
    }

    private function notifyAdminsOfDepletion(DistributionPoint $point, string $resourceType): void
    {
        User::where('role', User::ROLE_ADMIN)->get()->each(
            fn (User $admin) => $admin->notify(new ResourceDepletedNotification($point, $resourceType))
        );
    }

    private function notifyFavorites(DistributionPoint $point, string $resourceType, string $availability): void
    {
        $summary = "{$resourceType} is now {$availability} at {$point->name}.";

        $point->favoritedBy()->with('user')->get()->each(
            fn ($favorite) => $favorite->user->notify(
                new FavoritePointChangedNotification($point, 'resource_status', $summary)
            )
        );
    }
}
