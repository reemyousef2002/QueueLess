<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['distribution_point_id', 'status', 'current_number', 'avg_service_minutes'])]
class Queue extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_OPEN = 'open';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CLOSED = 'closed';

    protected function casts(): array
    {
        return [
            'current_number' => 'integer',
            'avg_service_minutes' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<DistributionPoint, $this> */
    public function distributionPoint(): BelongsTo
    {
        return $this->belongsTo(DistributionPoint::class);
    }

    /** @return HasMany<QueueEntry, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    /** @return HasMany<QueueEntry, $this> */
    public function waitingEntries(): HasMany
    {
        return $this->entries()->where('status', QueueEntry::STATUS_WAITING);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}
