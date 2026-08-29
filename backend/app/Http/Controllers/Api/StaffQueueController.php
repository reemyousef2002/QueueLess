<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesDistributionPointAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\CallNextRequest;
use App\Http\Resources\QueueEntryResource;
use App\Http\Resources\QueueResource;
use App\Models\Counter;
use App\Models\Queue;
use App\Models\QueueEntry;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 8 & API 13: staff queue control — call next, pause/resume/close,
 * skip a no-show, recall a skipped ticket (FR-008, FR-015).
 */
class StaffQueueController extends Controller
{
    use AuthorizesDistributionPointAccess;

    public function __construct(private readonly QueueService $queues) {}

    /**
     * Not one of the documented 16 endpoints — the dashboard needs to
     * actually show who's in the queue (with real entry IDs) to call,
     * skip, or recall anyone; nothing in the doc's API list lists a
     * queue's tickets.
     */
    public function entries(Request $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        $entries = $queue->entries()
            ->with('user')
            ->orderByDesc('priority_flag')
            ->orderBy('joined_at')
            ->get();

        return response()->json(['data' => QueueEntryResource::collection($entries)]);
    }

    public function callNext(CallNextRequest $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        $counter = null;
        if ($counterId = $request->input('counterId')) {
            $counter = Counter::where('id', $counterId)
                ->where('distribution_point_id', $queue->distribution_point_id)
                ->firstOrFail();
        }

        $entry = $this->queues->callNext($queue, $counter);

        return response()->json([
            'data' => $entry ? new QueueEntryResource($entry) : null,
            'queue' => new QueueResource($queue->fresh()),
        ]);
    }

    public function pause(Request $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        return response()->json(['data' => new QueueResource($this->queues->pause($queue))]);
    }

    public function resume(Request $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        return response()->json(['data' => new QueueResource($this->queues->resume($queue))]);
    }

    public function close(Request $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        return response()->json(['data' => new QueueResource($this->queues->close($queue))]);
    }

    public function skip(Request $request, Queue $queue): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);

        $entry = $this->queues->skip($queue);

        return response()->json(['data' => new QueueEntryResource($entry)]);
    }

    public function recall(Request $request, Queue $queue, QueueEntry $entryId): JsonResponse
    {
        $queue->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $queue->distributionPoint);
        abort_unless($entryId->queue_id === $queue->id, 404);

        $entry = $this->queues->recall($entryId);

        return response()->json(['data' => new QueueEntryResource($entry)]);
    }
}
