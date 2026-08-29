<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommunityUpdateResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distributionPointId' => $this->distribution_point_id,
            'reporterId' => $this->reporter_id,
            'updateType' => $this->update_type,
            'message' => $this->message,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
