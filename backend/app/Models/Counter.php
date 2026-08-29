<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['distribution_point_id', 'label', 'is_active'])]
class Counter extends Model
{
    use HasFactory, HasUuid;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<DistributionPoint, $this> */
    public function distributionPoint(): BelongsTo
    {
        return $this->belongsTo(DistributionPoint::class);
    }

    /** @return HasMany<QueueEntry, $this> */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }
}
