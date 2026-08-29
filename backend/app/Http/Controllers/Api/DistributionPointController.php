<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DistributionPointImageRequest;
use App\Http\Requests\DistributionPointRequest;
use App\Http\Resources\DistributionPointResource;
use App\Models\DistributionPoint;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * API 3: public directory of distribution points, plus FR-013 admin CRUD.
 */
class DistributionPointController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // lat/lng/radius are accepted per the API doc but this reference
        // implementation does not do geo-distance filtering yet — swap the
        // query below for a spatial lookup once locations get real coordinates.
        // This route has no auth:sanctum middleware (it's public per the API
        // doc), so the default 'web' guard never resolves a user here even
        // with a Bearer token present — the 'sanctum' guard has to be asked
        // for explicitly.
        $requester = $request->user('sanctum');

        $points = DistributionPoint::query()
            // Residents only ever see active points; an admin managing the
            // list (FR-013) needs to see deactivated ones too, so they can
            // find and reactivate them.
            ->when(! $requester?->isAdmin(), fn ($q) => $q->where('is_active', true))
            ->with([
                // UUID primary keys have no natural row order, so without
                // this the "first" resourceStatus (what cards show as the
                // point's headline status) was effectively random.
                'resourceStatuses' => fn ($q) => $q->orderBy('updated_at', 'desc'),
                'crowdDensityReports' => fn ($q) => $q->latest('created_at')->limit(1),
                'counters',
                'queues' => fn ($q) => $q->withCount([
                    'entries as waiting_count' => fn ($q2) => $q2->where('status', 'waiting'),
                ]),
            ])
            ->orderBy('name')
            ->get();

        $this->markFavorites($points, $requester);

        return response()->json([
            'data' => DistributionPointResource::collection($points),
        ]);
    }

    public function show(Request $request, DistributionPoint $distributionPoint): JsonResponse
    {
        $distributionPoint->load([
            'resourceStatuses',
            'crowdDensityReports' => fn ($q) => $q->latest('created_at')->limit(1),
            'counters',
            'queues' => fn ($q) => $q->withCount([
                'entries as waiting_count' => fn ($q2) => $q2->where('status', 'waiting'),
            ]),
        ]);

        $this->markFavorites(collect([$distributionPoint]), $request->user('sanctum'));

        return response()->json(['data' => new DistributionPointResource($distributionPoint)]);
    }

    /**
     * FR-017 needs the client to know which points the current user has
     * already favorited (so it can show a filled vs. outline heart) —
     * stamps an is_favorited attribute onto each model rather than adding
     * a dedicated "is this favorited" endpoint.
     *
     * @param  Collection<int, DistributionPoint>  $points
     */
    private function markFavorites(Collection $points, ?User $requester): void
    {
        $favoritedIds = $requester
            ? $requester->favoritePoints()->pluck('distribution_point_id')->all()
            : [];

        // NOT `fn ($p) => $p->is_favorited = ...` — an assignment expression
        // evaluates to its assigned value, so that arrow function returns
        // `false` for every non-favorited point, and Collection::each()
        // treats a `=== false` callback return as "stop iterating". That
        // silently truncated is_favorited to null (unset) for every point
        // after the first non-favorited one. A block body with no return
        // value sidesteps it entirely.
        $points->each(function (DistributionPoint $p) use ($favoritedIds) {
            $p->is_favorited = in_array($p->id, $favoritedIds, true);
        });
    }

    public function store(DistributionPointRequest $request): JsonResponse
    {
        $point = DistributionPoint::create([
            'name' => $request->string('name')->toString(),
            'type' => $request->string('type')->toString(),
            'address' => $request->input('address'),
            'contact_phone' => $request->input('contactPhone'),
            'is_active' => $request->boolean('isActive', true),
        ]);

        // Not covered by any documented endpoint, but a point is useless
        // without one: FR-004 (join a queue) has nothing to join otherwise.
        // Every new point gets a single open queue by default.
        Queue::create([
            'distribution_point_id' => $point->id,
            'status' => Queue::STATUS_OPEN,
        ]);

        return response()->json(['data' => new DistributionPointResource($point)], 201);
    }

    /**
     * Not one of the documented 16 endpoints — a per-point photo isn't in
     * the doc's schema at all. Kept as its own multipart endpoint (rather
     * than folded into update()) so update() can stay plain JSON.
     */
    public function uploadImage(DistributionPointImageRequest $request, DistributionPoint $distributionPoint): JsonResponse
    {
        if ($distributionPoint->image_path) {
            Storage::disk('public')->delete($distributionPoint->image_path);
        }

        $path = $request->file('image')->store('distribution-points', 'public');
        $distributionPoint->update(['image_path' => $path]);

        return response()->json(['data' => new DistributionPointResource($distributionPoint)]);
    }

    public function update(DistributionPointRequest $request, DistributionPoint $distributionPoint): JsonResponse
    {
        $distributionPoint->update(array_filter([
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'address' => $request->input('address'),
            'contact_phone' => $request->input('contactPhone'),
            'is_active' => $request->has('isActive') ? $request->boolean('isActive') : null,
        ], fn ($value) => $value !== null));

        return response()->json(['data' => new DistributionPointResource($distributionPoint)]);
    }

    public function destroy(DistributionPointRequest $request, DistributionPoint $distributionPoint): JsonResponse
    {
        // "Deactivate" per FR-013 rather than a hard delete, so historical
        // queue/analytics data stays intact.
        $distributionPoint->update(['is_active' => false]);

        return response()->json(['data' => new DistributionPointResource($distributionPoint)]);
    }
}
