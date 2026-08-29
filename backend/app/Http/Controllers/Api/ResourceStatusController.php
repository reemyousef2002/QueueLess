<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesDistributionPointAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceStatusUpdateRequest;
use App\Http\Resources\ResourceStatusResource;
use App\Models\DistributionPoint;
use App\Services\ResourceStatusService;
use Illuminate\Http\JsonResponse;

/**
 * API 4: resource availability per distribution point (FR-003, FR-009).
 */
class ResourceStatusController extends Controller
{
    use AuthorizesDistributionPointAccess;

    public function __construct(private readonly ResourceStatusService $service) {}

    public function index(DistributionPoint $distributionPoint): JsonResponse
    {
        return response()->json([
            'data' => ResourceStatusResource::collection($distributionPoint->resourceStatuses),
        ]);
    }

    public function store(ResourceStatusUpdateRequest $request, DistributionPoint $distributionPoint): JsonResponse
    {
        $this->ensureAssignedToPoint($request->user(), $distributionPoint);

        $status = $this->service->update(
            $distributionPoint,
            $request->string('resourceType')->toString(),
            $request->string('availability')->toString(),
            $request->user(),
        );

        return response()->json(['data' => new ResourceStatusResource($status)], 201);
    }
}
