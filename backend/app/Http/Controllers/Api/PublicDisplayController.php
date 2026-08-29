<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionPoint;
use App\Models\QueueEntry;
use Illuminate\Http\JsonResponse;

/**
 * API 15: lightweight, frequently-polled feed for the public
 * "Now Serving" kiosk display (FR-018, NFR-11). No auth required.
 */
class PublicDisplayController extends Controller
{
    public function show(DistributionPoint $distributionPoint): JsonResponse
    {
        $queues = $distributionPoint->queues()->where('status', '!=', 'closed')->get();

        $nowServing = QueueEntry::query()
            ->whereIn('queue_id', $queues->pluck('id'))
            ->where('status', QueueEntry::STATUS_CALLED)
            ->with('counter')
            ->get()
            ->map(fn (QueueEntry $entry) => [
                'ticketNumber' => $entry->ticket_number,
                'counter' => $entry->counter?->label,
            ]);

        $nextUp = QueueEntry::query()
            ->whereIn('queue_id', $queues->pluck('id'))
            ->where('status', QueueEntry::STATUS_WAITING)
            ->orderByDesc('priority_flag')
            ->orderBy('joined_at')
            ->limit(5)
            ->pluck('ticket_number');

        return response()->json([
            'data' => [
                'distributionPointId' => $distributionPoint->id,
                'distributionPointName' => $distributionPoint->name,
                'nowServing' => $nowServing,
                'nextUp' => $nextUp,
                'refreshedAt' => now()->toIso8601String(),
            ],
        ]);
    }
}
