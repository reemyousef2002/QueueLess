<?php

namespace App\Services;

use App\Models\CrowdDensityReport;
use App\Models\DistributionPoint;
use App\Models\User;
use App\Notifications\FavoritePointChangedNotification;

/**
 * FR-010: crowd-density reporting, submitted by volunteers or estimated
 * by the system.
 */
class CrowdDensityService
{
    public function report(DistributionPoint $point, string $densityLevel, ?User $reportedBy): CrowdDensityReport
    {
        $report = CrowdDensityReport::create([
            'distribution_point_id' => $point->id,
            'density_level' => $densityLevel,
            'reported_by' => $reportedBy?->id,
        ]);

        $summary = match ($densityLevel) {
            'red' => "{$point->name} is now dangerously crowded.",
            'yellow' => "{$point->name} is getting busy.",
            default => "{$point->name} has calmed down.",
        };

        $point->favoritedBy()->with('user')->get()->each(
            fn ($favorite) => $favorite->user->notify(
                new FavoritePointChangedNotification($point, 'crowd_density', $summary)
            )
        );

        return $report;
    }

    public function current(DistributionPoint $point): ?CrowdDensityReport
    {
        return $point->crowdDensityReports()->latest('created_at')->first();
    }
}
