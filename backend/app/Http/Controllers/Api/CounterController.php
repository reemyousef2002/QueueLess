<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\AuthorizesDistributionPointAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\CounterRequest;
use App\Http\Resources\CounterResource;
use App\Models\Counter;
use App\Models\DistributionPoint;
use Illuminate\Http\JsonResponse;

/**
 * API 12: service counters per distribution point (FR-016).
 */
class CounterController extends Controller
{
    use AuthorizesDistributionPointAccess;

    public function index(DistributionPoint $distributionPoint): JsonResponse
    {
        return response()->json([
            'data' => CounterResource::collection($distributionPoint->counters),
            'activeCount' => $distributionPoint->counters()->where('is_active', true)->count(),
        ]);
    }

    public function store(CounterRequest $request, DistributionPoint $distributionPoint): JsonResponse
    {
        $this->ensureAssignedToPoint($request->user(), $distributionPoint);

        $counter = $distributionPoint->counters()->create([
            'label' => $request->string('label')->toString(),
            'is_active' => $request->boolean('isActive', true),
        ]);

        return response()->json(['data' => new CounterResource($counter)], 201);
    }

    public function update(CounterRequest $request, Counter $counter): JsonResponse
    {
        $counter->loadMissing('distributionPoint');
        $this->ensureAssignedToPoint($request->user(), $counter->distributionPoint);

        $counter->update(array_filter([
            'label' => $request->input('label'),
            'is_active' => $request->has('isActive') ? $request->boolean('isActive') : null,
        ], fn ($value) => $value !== null));

        return response()->json(['data' => new CounterResource($counter)]);
    }
}
