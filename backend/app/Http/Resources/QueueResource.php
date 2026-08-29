<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distributionPointId' => $this->distribution_point_id,
            'status' => $this->status,
            'currentNumber' => $this->current_number,
            'avgServiceMinutes' => $this->avg_service_minutes === null ? null : (float) $this->avg_service_minutes,
            'waitingCount' => $this->when(isset($this->waiting_count), fn () => (int) $this->waiting_count),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
