<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrowdDensityReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'distributionPointId' => $this->distribution_point_id,
            'densityLevel' => $this->density_level,
            'reportedBy' => $this->reported_by,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
