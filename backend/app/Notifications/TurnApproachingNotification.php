<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Notifications\Notification;

/**
 * FR-006: sent when a user's turn is approaching.
 *
 * Delivered over the database channel so it works out of the box; add a
 * push channel (Firebase Cloud Messaging per the architecture doc, or any
 * other provider) by listing it in via() once credentials are configured —
 * the payload below does not need to change.
 */
class TurnApproachingNotification extends Notification
{
    public function __construct(private readonly QueueEntry $entry) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $this->entry->loadMissing('queue.distributionPoint');

        return [
            'type' => 'turn_approaching',
            'queue_entry_id' => $this->entry->id,
            'distribution_point_id' => $this->entry->queue->distribution_point_id,
            'distribution_point_name' => $this->entry->queue->distributionPoint->name,
            'ticket_number' => $this->entry->ticket_number,
            'message' => 'Your turn is approaching — please head to the counter soon.',
        ];
    }
}
