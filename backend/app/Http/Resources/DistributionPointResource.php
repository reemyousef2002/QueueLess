<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributionPointResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'contactPhone' => $this->contact_phone,
            // asset() just concatenates APP_URL + '/storage/' + the path,
            // matching config/filesystems.php's 'public' disk 'url' setting
            // exactly — avoids Storage::disk()->url(), whose contract
            // doesn't declare url() (only the concrete adapter does).
            'image' => $this->image_path ? asset('storage/'.$this->image_path) : null,
            'isActive' => $this->is_active,
            'isFavorited' => (bool) $this->is_favorited,
            'resourceStatuses' => ResourceStatusResource::collection($this->whenLoaded('resourceStatuses')),
            'crowdDensity' => $this->when(
                $this->relationLoaded('crowdDensityReports'),
                fn () => $this->crowdDensityReports->first()?->density_level,
            ),
            'counters' => CounterResource::collection($this->whenLoaded('counters')),
            'queues' => QueueResource::collection($this->whenLoaded('queues')),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
