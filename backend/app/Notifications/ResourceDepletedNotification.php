<?php

namespace App\Notifications;

use App\Models\DistributionPoint;
use Illuminate\Notifications\Notification;

/**
 * Not in the doc's FR list — an admin-facing counterpart to FR-017 (which
 * only notifies residents who favorited the specific point). Gives admins
 * system-wide visibility into depletion the moment it's reported, without
 * needing every point favorited or the analytics dashboard open.
 */
class ResourceDepletedNotification extends Notification
{
    public function __construct(
        private readonly DistributionPoint $point,
        private readonly string $resourceType,
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'resource_depleted',
            'distribution_point_id' => $this->point->id,
            'distribution_point_name' => $this->point->name,
            'resource_type' => $this->resourceType,
            'message' => "{$this->resourceType} is now depleted at {$this->point->name}.",
        ];
    }
}
