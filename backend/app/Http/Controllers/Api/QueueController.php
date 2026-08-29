<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\JoinQueueRequest;
use App\Http\Resources\QueueEntryResource;
use App\Models\PriorityRegistration;
use App\Models\Queue;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;

/**
 * API 5: join a virtual queue remotely (FR-004, FR-011).
 */
class QueueController extends Controller
{
    public function __construct(private readonly QueueService $queues) {}

    public function join(JoinQueueRequest $request, Queue $queue): JsonResponse
    {
        $user = $request->user();

        // The client can request priority, but it only takes effect if the
        // user actually has a verified registration — never trust the flag
        // on its own (FR-011: "assigned a priority queue position").
        $wantsPriority = $request->boolean('priority');
        $hasVerifiedPriority = $wantsPriority && PriorityRegistration::query()
            ->where('user_id', $user->id)
            ->whereNotNull('verified_at')
            ->exists();

        $entry = $this->queues->join($queue, $user, $hasVerifiedPriority);
        $position = $this->queues->position($entry);

        return response()->json([
            'data' => new QueueEntryResource($entry),
            'position' => $position,
        ], 201);
    }
}
