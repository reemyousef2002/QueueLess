<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * FR-001, FR-002: registration and authentication (phone/OTP or
 * email/password), issuing Sanctum tokens.
 */
class AuthService
{
    public function register(string $name, string $phone, ?string $email, ?string $password): array
    {
        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password_hash' => $password ? Hash::make($password) : null,
            'role' => User::ROLE_RESIDENT,
            'preferred_language' => 'en',
        ]);

        return $this->issueToken($user);
    }

    public function loginWithPassword(string $identifier, string $password): array
    {
        $user = User::query()
            ->where('phone', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $user || ! $user->password_hash || ! Hash::check($password, $user->password_hash)) {
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        return $this->issueToken($user);
    }

    public function loginWithOtpVerifiedPhone(string $phone): array
    {
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'No account found for this phone number. Please register first.',
            ]);
        }

        return $this->issueToken($user);
    }

    private function issueToken(User $user): array
    {
        $token = $user->createToken('queueless')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }
}
