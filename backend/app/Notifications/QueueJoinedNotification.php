<?php

namespace App\Notifications;

use App\Models\QueueEntry;
use Illuminate\Notifications\Notification;

/**
 * Not in the doc's FR list — a reasonable staff-facing counterpart to
 * FR-006 (which only notifies the resident side). Sent to every staff
 * member assigned to a point when a new ticket joins their queue, so
 * they're not just watching the dashboard to notice.
 */
class QueueJoinedNotification extends Notification
{
    public function __construct(private readonly QueueEntry $entry)
    {
    }

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
            'type' => 'queue_joined',
            'queue_entry_id' => $this->entry->id,
            'distribution_point_id' => $this->entry->queue->distribution_point_id,
            'distribution_point_name' => $this->entry->queue->distributionPoint->name,
            'ticket_number' => $this->entry->ticket_number,
            'message' => "New ticket #{$this->entry->ticket_number} joined the queue at {$this->entry->queue->distributionPoint->name}.",
        ];
    }
}
