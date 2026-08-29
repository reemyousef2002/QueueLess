<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'category', 'verified_by', 'verified_at'])]
class PriorityRegistration extends Model
{
    use HasFactory, HasUuid;

    public const ELDERLY = 'elderly';

    public const DISABILITY = 'disability';

    public const PREGNANT = 'pregnant';

    public const MOTHER_WITH_CHILDREN = 'mother_with_children';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $registration) {
            $registration->created_at ??= now();
        });
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }
}
