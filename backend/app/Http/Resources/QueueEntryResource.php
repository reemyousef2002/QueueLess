<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueEntryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'queueId' => $this->queue_id,
            'userId' => $this->user_id,
            'userName' => $this->whenLoaded('user', fn () => $this->user->name),
            'ticketNumber' => $this->ticket_number,
            'status' => $this->status,
            'priorityFlag' => $this->priority_flag,
            'counterId' => $this->counter_id,
            'joinedAt' => $this->joined_at?->toIso8601String(),
            'calledAt' => $this->called_at?->toIso8601String(),
            'servedAt' => $this->served_at?->toIso8601String(),
            'leftAt' => $this->left_at?->toIso8601String(),
        ];
    }
}
