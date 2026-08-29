<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_point_id', 'reporter_id', 'update_type', 'message'])]
class CommunityUpdate extends Model
{
    use HasFactory, HasUuid;

    public const RESOURCE_ARRIVED = 'resource_arrived';

    public const RESOURCE_DEPLETED = 'resource_depleted';

    public const QUEUE_PAUSED = 'queue_paused';

    public const QUEUE_RESUMED = 'queue_resumed';

    public const OTHER = 'other';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $update) {
            $update->created_at ??= now();
        });
    }

    /** @return BelongsTo<DistributionPoint, $this> */
    public function distributionPoint(): BelongsTo
    {
        return $this->belongsTo(DistributionPoint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
