<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DistributionPoint;
use App\Models\FavoritePoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 14: favorite/unfavorite a distribution point (FR-017).
 */
class FavoritePointController extends Controller
{
    public function store(Request $request, DistributionPoint $distributionPoint): JsonResponse
    {
        $favorite = FavoritePoint::firstOrCreate([
            'user_id' => $request->user()->id,
            'distribution_point_id' => $distributionPoint->id,
        ]);

        return response()->json(['data' => ['id' => $favorite->id, 'favorited' => true]], 201);
    }

    public function destroy(Request $request, DistributionPoint $distributionPoint): JsonResponse
    {
        FavoritePoint::where('user_id', $request->user()->id)
            ->where('distribution_point_id', $distributionPoint->id)
            ->delete();

        return response()->json(['data' => ['favorited' => false]]);
    }
}
