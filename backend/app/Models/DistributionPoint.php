<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'address', 'contact_phone', 'image_path', 'is_active'])]
class DistributionPoint extends Model
{
    use HasFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<Queue, $this> */
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }

    /** @return HasMany<Counter, $this> */
    public function counters(): HasMany
    {
        return $this->hasMany(Counter::class);
    }

    /** @return HasMany<ResourceStatus, $this> */
    public function resourceStatuses(): HasMany
    {
        return $this->hasMany(ResourceStatus::class);
    }

    /** @return HasMany<CommunityUpdate, $this> */
    public function communityUpdates(): HasMany
    {
        return $this->hasMany(CommunityUpdate::class);
    }

    /** @return HasMany<CrowdDensityReport, $this> */
    public function crowdDensityReports(): HasMany
    {
        return $this->hasMany(CrowdDensityReport::class);
    }

    /** @return HasMany<FavoritePoint, $this> */
    public function favoritedBy(): HasMany
    {
        return $this->hasMany(FavoritePoint::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'staff_assignments');
    }

    public function latestCrowdDensity(): ?CrowdDensityReport
    {
        return $this->crowdDensityReports()->latest('created_at')->first();
    }
}
