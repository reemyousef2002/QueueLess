<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * API 16: analytics dashboard (FR-019).
 */
class AnalyticsController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isStaffOrAdmin(), 403);

        $request->validate([
            'range' => ['sometimes', Rule::in(['7d', '30d', '3m'])],
            'distributionPointId' => ['sometimes', 'uuid'],
        ]);

        $summary = $this->analytics->summary(
            $request->input('range', '7d'),
            $request->input('distributionPointId'),
        );

        return response()->json(['data' => $summary]);
    }
}
