<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueEntry;
use App\Models\User;
use App\Notifications\QueueJoinedNotification;
use App\Notifications\TurnApproachingNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Business logic for queue membership and staff queue control:
 * joining, position/wait-time calculation, leave/rejoin, call-next,
 * pause/resume/close, and skip/recall (FR-004 through FR-008, FR-015).
 */
class QueueService
{
    /**
     * Active-queue ordering: verified priority entries first, then FIFO by
     * join time within each tier (FR-011).
     */
    private function orderedActiveEntries(Queue $queue)
    {
        return $queue->entries()
            ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_CALLED])
            ->orderByDesc('priority_flag')
            ->orderBy('joined_at');
    }

    public function join(Queue $queue, User $user, bool $priorityFlag): QueueEntry
    {
        if (! $queue->isOpen()) {
            throw ValidationException::withMessages([
                'queue' => 'This queue is not currently accepting new tickets.',
            ]);
        }

        $entry = DB::transaction(function () use ($queue, $user, $priorityFlag) {
            $nextTicket = QueueEntry::where('queue_id', $queue->id)->max('ticket_number') + 1;

            return QueueEntry::create([
                'queue_id' => $queue->id,
                'user_id' => $user->id,
                'ticket_number' => $nextTicket,
                'status' => QueueEntry::STATUS_WAITING,
                'priority_flag' => $priorityFlag,
                'joined_at' => now(),
            ]);
        });

        $this->notifyStaffOfJoin($queue, $entry);

        return $entry;
    }

    /**
     * Staff-facing counterpart to FR-006 — not documented, but lets staff
     * assigned to this point know a new ticket landed without needing the
     * dashboard open and polling.
     */
    private function notifyStaffOfJoin(Queue $queue, QueueEntry $entry): void
    {
        $queue->loadMissing('distributionPoint');

        $staff = $queue->distributionPoint->staff()->get()
            ->filter(fn (User $u) => $u->role === User::ROLE_STAFF);

        $staff->each(fn (User $u) => $u->notify(new QueueJoinedNotification($entry)));
    }

    /**
     * @return array{position:int, people_ahead:int, estimated_wait_minutes:float}
     */
    public function position(QueueEntry $entry): array
    {
        $entry->loadMissing('queue');
        $queue = $entry->queue;

        if ($entry->status === QueueEntry::STATUS_CALLED) {
            return ['position' => 0, 'people_ahead' => 0, 'estimated_wait_minutes' => 0.0];
        }

        $peopleAhead = $this->orderedActiveEntries($queue)
            ->where(function ($q) use ($entry) {
                $q->where('priority_flag', '>', $entry->priority_flag)
                    ->orWhere(function ($q2) use ($entry) {
                        $q2->where('priority_flag', $entry->priority_flag)
                            ->where('joined_at', '<', $entry->joined_at);
                    });
            })
            ->count();

        $avgService = (float) ($queue->avg_service_minutes ?? config('queueless.default_service_minutes'));

        return [
            'position' => $peopleAhead + 1,
            'people_ahead' => $peopleAhead,
            'estimated_wait_minutes' => round($peopleAhead * $avgService, 1),
        ];
    }

    public function leave(QueueEntry $entry): QueueEntry
    {
        if (! $entry->isActive()) {
            throw ValidationException::withMessages([
                'entry' => 'This ticket is no longer active.',
            ]);
        }

        $entry->update(['left_at' => now()]);

        return $entry;
    }

    public function rejoin(QueueEntry $entry): QueueEntry
    {
        if (! $entry->isActive()) {
            throw ValidationException::withMessages([
                'entry' => 'This ticket is no longer active and cannot be rejoined.',
            ]);
        }

        if ($entry->left_at === null) {
            return $entry;
        }

        $graceMinutes = config('queueless.grace_period_minutes');
        if ($entry->left_at->diffInMinutes(now()) > $graceMinutes) {
            $entry->update(['status' => QueueEntry::STATUS_CANCELLED]);
            throw ValidationException::withMessages([
                'entry' => "The {$graceMinutes}-minute grace period has expired. Please join the queue again.",
            ]);
        }

        $entry->update(['left_at' => null]);

        return $entry;
    }

    /**
     * FR-014: a user permanently leaving a queue — distinct from leave()
     * above, which is FR-007's temporary "step away, hold my place." This
     * gives up the ticket entirely; there is no rejoining it.
     */
    public function cancel(QueueEntry $entry): QueueEntry
    {
        if (! $entry->isActive()) {
            throw ValidationException::withMessages([
                'entry' => 'This ticket is no longer active.',
            ]);
        }

        $entry->update([
            'status' => QueueEntry::STATUS_CANCELLED,
            'left_at' => null,
        ]);

        return $entry;
    }

    public function callNext(Queue $queue, ?Counter $counter = null): ?QueueEntry
    {
        return DB::transaction(function () use ($queue, $counter) {
            // Whoever is currently being served at this counter (or, if no
            // counter given, anyone still marked called) is now done.
            $inService = $queue->entries()
                ->where('status', QueueEntry::STATUS_CALLED)
                ->when($counter, fn ($q) => $q->where('counter_id', $counter->id))
                ->first();

            if ($inService) {
                $this->markServed($inService);
            }

            $next = $this->orderedActiveEntries($queue)
                ->where('status', QueueEntry::STATUS_WAITING)
                ->first();

            if (! $next) {
                return null;
            }

            $next->update([
                'status' => QueueEntry::STATUS_CALLED,
                'called_at' => now(),
                'counter_id' => $counter?->id,
            ]);

            $queue->update(['current_number' => $next->ticket_number]);

            $this->notifyUpcomingTurns($queue);

            return $next->fresh();
        });
    }

    private function markServed(QueueEntry $entry): void
    {
        $servedAt = now();
        $entry->update(['status' => QueueEntry::STATUS_SERVED, 'served_at' => $servedAt]);

        if ($entry->called_at) {
            $minutes = $entry->called_at->diffInSeconds($servedAt) / 60;
            $this->updateRollingAverage($entry->queue, $minutes);
        }
    }

    private function updateRollingAverage(Queue $queue, float $latestMinutes): void
    {
        $current = $queue->avg_service_minutes;
        // Simple exponential moving average so one slow/fast visit doesn't
        // swing the estimate too hard.
        $next = $current === null ? $latestMinutes : ($current * 0.7 + $latestMinutes * 0.3);
        $queue->update(['avg_service_minutes' => round($next, 2)]);
    }

    public function pause(Queue $queue): Queue
    {
        $queue->update(['status' => Queue::STATUS_PAUSED]);

        return $queue;
    }

    public function resume(Queue $queue): Queue
    {
        $queue->update(['status' => Queue::STATUS_OPEN]);

        return $queue;
    }

    public function close(Queue $queue): Queue
    {
        $queue->update(['status' => Queue::STATUS_CLOSED]);

        return $queue;
    }

    /**
     * FR-015: staff skips a called customer who does not respond.
     */
    public function skip(Queue $queue, ?QueueEntry $entry = null): QueueEntry
    {
        $entry ??= $queue->entries()->where('status', QueueEntry::STATUS_CALLED)->first();

        if (! $entry) {
            throw ValidationException::withMessages([
                'entry' => 'There is no called ticket to skip.',
            ]);
        }

        $entry->update([
            'status' => QueueEntry::STATUS_SKIPPED,
            'counter_id' => null,
        ]);

        return $entry;
    }

    /**
     * FR-015: recall a skipped/missed ticket back into the active queue,
     * preserving its original join time so it reclaims its natural
     * priority-ordered position.
     */
    public function recall(QueueEntry $entry): QueueEntry
    {
        if (! in_array($entry->status, [QueueEntry::STATUS_SKIPPED, QueueEntry::STATUS_NO_SHOW], true)) {
            throw ValidationException::withMessages([
                'entry' => 'Only a skipped or missed ticket can be recalled.',
            ]);
        }

        $entry->update([
            'status' => QueueEntry::STATUS_WAITING,
            'called_at' => null,
            'counter_id' => null,
        ]);

        return $entry;
    }

    /**
     * FR-006: notify waiting users once they cross the "turn approaching"
     * threshold, at most once per ticket.
     */
    private function notifyUpcomingTurns(Queue $queue): void
    {
        $threshold = config('queueless.notify_when_people_ahead');

        $candidates = $this->orderedActiveEntries($queue)
            ->where('status', QueueEntry::STATUS_WAITING)
            ->whereNull('notified_at')
            ->with('user')
            ->get();

        foreach ($candidates as $index => $entry) {
            if ($index < $threshold) {
                $entry->user->notify(new TurnApproachingNotification($entry));
                $entry->update(['notified_at' => now()]);
            }
        }
    }
}
