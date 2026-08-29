<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['queue_id', 'user_id', 'ticket_number', 'status', 'priority_flag', 'counter_id', 'joined_at', 'called_at', 'served_at', 'left_at', 'notified_at'])]
class QueueEntry extends Model
{
    use HasFactory, HasUuid;

    public const STATUS_WAITING = 'waiting';

    public const STATUS_CALLED = 'called';

    public const STATUS_SERVED = 'served';

    public const STATUS_NO_SHOW = 'no_show';

    public const STATUS_SKIPPED = 'skipped';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * The table has no literal `created_at` column — `joined_at` plays
     * that role (it's the timestamp the doc's schema defines for when the
     * ticket was created), so Eloquent is told to use it instead.
     */
    public const CREATED_AT = 'joined_at';

    protected function casts(): array
    {
        return [
            'ticket_number' => 'integer',
            'priority_flag' => 'boolean',
            'joined_at' => 'datetime',
            'called_at' => 'datetime',
            'served_at' => 'datetime',
            'left_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Queue, $this> */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Counter, $this> */
    public function counter(): BelongsTo
    {
        return $this->belongsTo(Counter::class);
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_WAITING, self::STATUS_CALLED], true);
    }
}
