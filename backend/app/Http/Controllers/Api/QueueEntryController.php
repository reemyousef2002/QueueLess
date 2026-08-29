<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QueueEntryResource;
use App\Models\QueueEntry;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 6 & API 7: live position, leave/rejoin (FR-005, FR-007), and
 * permanently canceling a ticket (FR-014).
 */
class QueueEntryController extends Controller
{
    public function __construct(private readonly QueueService $queues) {}

    public function position(Request $request, QueueEntry $queueEntry): JsonResponse
    {
        $this->ensureOwner($request, $queueEntry);

        return response()->json([
            'data' => new QueueEntryResource($queueEntry),
            'position' => $this->queues->position($queueEntry),
        ]);
    }

    public function leave(Request $request, QueueEntry $queueEntry): JsonResponse
    {
        $this->ensureOwner($request, $queueEntry);

        $entry = $this->queues->leave($queueEntry);

        return response()->json(['data' => new QueueEntryResource($entry)]);
    }

    public function rejoin(Request $request, QueueEntry $queueEntry): JsonResponse
    {
        $this->ensureOwner($request, $queueEntry);

        $entry = $this->queues->rejoin($queueEntry);

        return response()->json([
            'data' => new QueueEntryResource($entry),
            'position' => $this->queues->position($entry),
        ]);
    }

    public function cancel(Request $request, QueueEntry $queueEntry): JsonResponse
    {
        $this->ensureOwner($request, $queueEntry);

        $entry = $this->queues->cancel($queueEntry);

        return response()->json(['data' => new QueueEntryResource($entry)]);
    }

    private function ensureOwner(Request $request, QueueEntry $entry): void
    {
        $user = $request->user();
        abort_unless($user->id === $entry->user_id || $user->isStaffOrAdmin(), 403);
    }
}
