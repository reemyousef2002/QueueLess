<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesDistributionPointAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\CrowdDensityReportRequest;
use App\Http\Resources\CrowdDensityReportResource;
use App\Models\DistributionPoint;
use App\Services\CrowdDensityService;
use Illuminate\Http\JsonResponse;

/**
 * API 11: crowd-density indicator per distribution point (FR-010).
 */
class CrowdDensityController extends Controller
{
    use AuthorizesDistributionPointAccess;

    public function __construct(private readonly CrowdDensityService $service) {}

    public function show(DistributionPoint $distributionPoint): JsonResponse
    {
        return response()->json([
            'data' => $this->service->current($distributionPoint)
                ? new CrowdDensityReportResource($this->service->current($distributionPoint))
                : null,
        ]);
    }

    public function store(CrowdDensityReportRequest $request): JsonResponse
    {
        $point = DistributionPoint::findOrFail($request->input('distributionPointId'));
        $this->ensureAssignedToPoint($request->user(), $point);

        $report = $this->service->report(
            $point,
            $request->string('densityLevel')->toString(),
            $request->user(),
        );

        return response()->json(['data' => new CrowdDensityReportResource($report)], 201);
    }
}
