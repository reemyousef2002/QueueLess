<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounterResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distributionPointId' => $this->distribution_point_id,
            'label' => $this->label,
            'isActive' => $this->is_active,
        ];
    }
}
