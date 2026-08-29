<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateStaffRequest;
use App\Http\Resources\UserResource;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * FR-013 (Administrator permission): manage staff and volunteer accounts,
 * including which distribution points each one is assigned to (NFR-05).
 */
class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'role' => ['sometimes', Rule::in(['resident', 'volunteer', 'staff', 'admin'])],
        ]);

        $users = User::query()
            ->with('staffAssignments')
            ->when($request->input('role'), fn ($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(25);

        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => ['page' => $users->currentPage(), 'total' => $users->total()],
        ]);
    }

    public function store(CreateStaffRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->input('email'),
            'password_hash' => Hash::make($request->string('password')->toString()),
            'role' => $request->string('role')->toString(),
        ]);

        foreach ($request->input('distributionPointIds', []) as $pointId) {
            StaffAssignment::create(['user_id' => $user->id, 'distribution_point_id' => $pointId]);
        }

        return response()->json(['data' => new UserResource($user->load('staffAssignments'))], 201);
    }

    public function assign(Request $request, User $user): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($user->isVolunteerOrStaff(), 422, 'Only staff or volunteer accounts can be assigned to a point.');

        $request->validate([
            'distributionPointId' => ['required', 'uuid', Rule::exists('distribution_points', 'id')],
        ]);

        StaffAssignment::firstOrCreate([
            'user_id' => $user->id,
            'distribution_point_id' => $request->input('distributionPointId'),
        ]);

        return response()->json(['data' => new UserResource($user->load('staffAssignments'))], 201);
    }

    public function unassign(Request $request, User $user, string $distributionPointId): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        StaffAssignment::where('user_id', $user->id)
            ->where('distribution_point_id', $distributionPointId)
            ->delete();

        return response()->json(['data' => new UserResource($user->load('staffAssignments'))]);
    }
}
