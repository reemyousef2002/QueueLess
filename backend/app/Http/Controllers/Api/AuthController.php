<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpSendRequest;
use App\Http\Requests\Auth\OtpVerifyRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API 1 & API 2: registration and login (phone/OTP or email/password).
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly OtpService $otp,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $this->auth->register(
            $request->string('name')->toString(),
            $request->string('phone')->toString(),
            $request->input('email'),
            $request->input('password'),
        );

        return $this->tokenResponse($data, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->auth->loginWithPassword(
            $request->string('identifier')->toString(),
            $request->string('password')->toString(),
        );

        return $this->tokenResponse($data);
    }

    public function sendOtp(OtpSendRequest $request): JsonResponse
    {
        $this->otp->sendCode($request->string('phone')->toString());

        return response()->json(['message' => 'A verification code has been sent.']);
    }

    public function verifyOtp(OtpVerifyRequest $request): JsonResponse
    {
        $phone = $request->string('phone')->toString();
        $ok = $this->otp->verify($phone, $request->string('code')->toString());

        if (! $ok) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired verification code.',
            ]);
        }

        $data = $this->auth->loginWithOtpVerifiedPhone($phone);

        return $this->tokenResponse($data);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /**
     * Was previously just `fn () => request()->user()` inline in the route
     * file — that returned the raw Eloquent model (snake_case, plus
     * leaking email_verified_at/updated_at) instead of going through
     * UserResource like every other endpoint, so preferredLanguage/
     * createdAt were silently undefined on the client. Also now
     * eager-loads staffAssignments so staff/volunteer accounts get
     * assignedPointIds here (the staff dashboard needs it).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()->load('staffAssignments')));
    }

    /**
     * Not in the doc's 16 endpoints — there's no FR for editing your own
     * account, but every user needs some way to fix a typo'd name or
     * change a phone number.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update([
            'name' => $request->string('name')->toString(),
            'phone' => $request->string('phone')->toString(),
            'email' => $request->input('email'),
            'preferred_language' => $request->input('preferredLanguage', $user->preferred_language),
        ]);

        return response()->json(new UserResource($user->fresh('staffAssignments')));
    }

    /**
     * Not in the doc's 16 endpoints either — a natural companion to
     * updateProfile(). Requires the current password so a hijacked
     * session token alone can't lock the real owner out.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password_hash' => Hash::make($request->string('newPassword')->toString()),
        ]);

        return response()->json(['message' => 'Password updated.']);
    }

    private function tokenResponse(array $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($data['user']),
            'token' => $data['token'],
        ], $status);
    }
}
