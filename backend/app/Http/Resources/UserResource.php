<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role,
            'preferredLanguage' => $this->preferred_language,
            'assignedPointIds' => $this->when(
                $this->relationLoaded('staffAssignments'),
                fn () => $this->staffAssignments->pluck('distribution_point_id'),
            ),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
