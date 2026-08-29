<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesDistributionPointAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityUpdateRequest;
use App\Http\Resources\CommunityUpdateResource;
use App\Models\CommunityUpdate;
use App\Models\DistributionPoint;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;

/**
 * API 9: volunteer/staff status updates for a distribution point (FR-009).
 */
class CommunityUpdateController extends Controller
{
    use AuthorizesDistributionPointAccess;

    public function __construct(private readonly QueueService $queues) {}

    public function store(CommunityUpdateRequest $request): JsonResponse
    {
        $point = DistributionPoint::findOrFail($request->input('distributionPointId'));
        $this->ensureAssignedToPoint($request->user(), $point);

        $update = CommunityUpdate::create([
            'distribution_point_id' => $point->id,
            'reporter_id' => $request->user()->id,
            'update_type' => $request->string('updateType')->toString(),
            'message' => $request->input('message'),
        ]);

        // A community update about the queue itself pauses/resumes every
        // open queue at the point, mirroring what a staff dashboard action
        // would do (FR-009 explicitly lists "queue paused"/"queue resumed"
        // as update types a volunteer can post).
        if (in_array($update->update_type, [CommunityUpdate::QUEUE_PAUSED, CommunityUpdate::QUEUE_RESUMED], true)) {
            $point->queues()->where('status', '!=', 'closed')->get()->each(
                fn ($queue) => $update->update_type === CommunityUpdate::QUEUE_PAUSED
                    ? $this->queues->pause($queue)
                    : $this->queues->resume($queue)
            );
        }

        return response()->json(['data' => new CommunityUpdateResource($update)], 201);
    }

    public function index(DistributionPoint $distributionPoint): JsonResponse
    {
        return response()->json([
            'data' => CommunityUpdateResource::collection(
                $distributionPoint->communityUpdates()->latest('created_at')->limit(50)->get()
            ),
        ]);
    }
}
