<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distribution_point_id', 'density_level', 'reported_by'])]
class CrowdDensityReport extends Model
{
    use HasFactory, HasUuid;

    public const GREEN = 'green';

    public const YELLOW = 'yellow';

    public const RED = 'red';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $report) {
            $report->created_at ??= now();
        });
    }

    /** @return BelongsTo<DistributionPoint, $this> */
    public function distributionPoint(): BelongsTo
    {
        return $this->belongsTo(DistributionPoint::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
