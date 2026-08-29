<?php

namespace App\Notifications;

use App\Models\DistributionPoint;
use Illuminate\Notifications\Notification;

/**
 * FR-017: sent to users who favorited a distribution point when its
 * resource-availability status or crowd-density level changes.
 */
class FavoritePointChangedNotification extends Notification
{
    public function __construct(
        private readonly DistributionPoint $point,
        private readonly string $reason,
        private readonly string $summary,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'favorite_point_changed',
            'distribution_point_id' => $this->point->id,
            'distribution_point_name' => $this->point->name,
            'reason' => $this->reason, // resource_status | crowd_density
            'message' => $this->summary,
        ];
    }
}
