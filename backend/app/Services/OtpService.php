<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * Generates and verifies phone OTP codes (FR-001, FR-002, NFR-04).
 *
 * There is no real SMS gateway wired up yet, so codes are written to the
 * log instead of being sent — swap sendCode()'s dispatch for an SMS
 * provider (e.g. Twilio, a local gateway) when one is available. The
 * verify/rate-limit contract below will not need to change.
 */
class OtpService
{
    private const TTL_MINUTES = 5;

    private const MAX_ATTEMPTS = 5;

    public function sendCode(string $phone): void
    {
        $rateLimitKey = "otp-send:{$phone}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            throw ValidationException::withMessages([
                'phone' => 'Too many OTP requests. Please try again in a minute.',
            ]);
        }
        RateLimiter::hit($rateLimitKey, 60);

        $code = (string) random_int(100000, 999999);

        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        // Dev-mode delivery: log the code instead of sending a real SMS.
        Log::info("QueueLess OTP for {$phone}: {$code}");
    }

    public function verify(string $phone, string $code): bool
    {
        $rateLimitKey = "otp-verify:{$phone}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_ATTEMPTS)) {
            throw ValidationException::withMessages([
                'code' => 'Too many attempts. Please request a new code.',
            ]);
        }

        $otp = OtpCode::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (! $otp || $otp->isExpired() || $otp->code !== $code) {
            RateLimiter::hit($rateLimitKey, 300);
            $otp?->increment('attempts');

            return false;
        }

        $otp->update(['consumed_at' => now()]);
        RateLimiter::clear($rateLimitKey);

        return true;
    }
}
