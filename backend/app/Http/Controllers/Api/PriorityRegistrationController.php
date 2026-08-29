<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PriorityRegistrationRequest;
use App\Http\Resources\PriorityRegistrationResource;
use App\Models\PriorityRegistration;
use App\Models\User;
use App\Notifications\PriorityRegistrationSubmittedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API 10: priority registration requests (FR-011), plus the
 * staff/admin verification step referenced by the Administrator's
 * "verify priority registrations" permission.
 */
class PriorityRegistrationController extends Controller
{
    public function store(PriorityRegistrationRequest $request): JsonResponse
    {
        $registration = PriorityRegistration::create([
            'user_id' => $request->user()->id,
            'category' => $request->string('category')->toString(),
        ]);

        $this->notifyAdmins($registration);

        return response()->json(['data' => new PriorityRegistrationResource($registration)], 201);
    }

    /**
     * Not in the doc's FR list — an admin-facing counterpart so "verify
     * priority registrations" isn't something an admin only discovers by
     * remembering to check the pending list.
     */
    private function notifyAdmins(PriorityRegistration $registration): void
    {
        User::where('role', User::ROLE_ADMIN)->get()->each(
            fn (User $admin) => $admin->notify(new PriorityRegistrationSubmittedNotification($registration))
        );
    }

    public function mine(Request $request): JsonResponse
    {
        return response()->json([
            'data' => PriorityRegistrationResource::collection(
                $request->user()->priorityRegistrations()->latest('created_at')->get()
            ),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->isStaffOrAdmin(), 403);

        $pending = PriorityRegistration::whereNull('verified_at')->latest('created_at')->get();

        return response()->json(['data' => PriorityRegistrationResource::collection($pending)]);
    }

    public function verify(Request $request, PriorityRegistration $priorityRegistration): JsonResponse
    {
        abort_unless($request->user()->isStaffOrAdmin(), 403);

        $priorityRegistration->update([
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return response()->json(['data' => new PriorityRegistrationResource($priorityRegistration)]);
    }
}
