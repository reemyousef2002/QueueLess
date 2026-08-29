<?php

namespace App\Notifications;

use App\Models\PriorityRegistration;
use Illuminate\Notifications\Notification;

/**
 * Not in the doc's FR list — an admin-facing counterpart so "verify
 * priority registrations" (an Administrator permission per the doc) isn't
 * something an admin only discovers by remembering to check the list.
 */
class PriorityRegistrationSubmittedNotification extends Notification
{
    public function __construct(private readonly PriorityRegistration $registration)
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
        $this->registration->loadMissing('user');

        return [
            'type' => 'priority_registration_submitted',
            'priority_registration_id' => $this->registration->id,
            'user_name' => $this->registration->user->name,
            'category' => $this->registration->category,
            'message' => "{$this->registration->user->name} requested priority status ({$this->registration->category}) — needs verification.",
        ];
    }
}
