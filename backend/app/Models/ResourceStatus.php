<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_point_id', 'resource_type', 'availability', 'updated_by'])]
class ResourceStatus extends Model
{
    use HasFactory, HasUuid;

    public const AVAILABLE = 'available';

    public const LIMITED = 'limited';

    public const DEPLETED = 'depleted';

    public const UNKNOWN = 'unknown';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<DistributionPoint, $this> */
    public function distributionPoint(): BelongsTo
    {
        return $this->belongsTo(DistributionPoint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected static function booted(): void
    {
        static::saving(function (self $status) {
            $status->updated_at = now();
        });
    }
}
