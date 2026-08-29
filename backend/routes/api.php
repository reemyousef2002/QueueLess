<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommunityUpdateController;
use App\Http\Controllers\Api\CounterController;
use App\Http\Controllers\Api\CrowdDensityController;
use App\Http\Controllers\Api\DistributionPointController;
use App\Http\Controllers\Api\FavoritePointController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PriorityRegistrationController;
use App\Http\Controllers\Api\PublicDisplayController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\QueueEntryController;
use App\Http\Controllers\Api\ResourceStatusController;
use App\Http\Controllers\Api\StaffQueueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes — numbered comments match "Fifth: API Documentation Template"
| in the QueueLess system analysis document.
|--------------------------------------------------------------------------
*/

// API 1 — Register / API 2 — Login, OTP send+verify
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/otp/send', [AuthController::class, 'sendOtp']);
Route::post('/auth/otp/verify', [AuthController::class, 'verifyOtp']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Not in the documented 16 — see AuthController::updateProfile()'s docblock.
Route::put('/auth/profile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::put('/auth/password', [AuthController::class, 'updatePassword'])->middleware('auth:sanctum');

// API 3 — Distribution Points (public directory)
Route::get('/distribution-points', [DistributionPointController::class, 'index']);
Route::get('/distribution-points/{distributionPoint}', [DistributionPointController::class, 'show']);

// FR-013 — Admin CRUD for distribution points
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::post('/distribution-points', [DistributionPointController::class, 'store']);
    Route::put('/distribution-points/{distributionPoint}', [DistributionPointController::class, 'update']);
    Route::delete('/distribution-points/{distributionPoint}', [DistributionPointController::class, 'destroy']);
    Route::post('/distribution-points/{distributionPoint}/image', [DistributionPointController::class, 'uploadImage']);
});

// API 4 — Resource Status
Route::get('/distribution-points/{distributionPoint}/resource-status', [ResourceStatusController::class, 'index']);
Route::post('/distribution-points/{distributionPoint}/resource-status', [ResourceStatusController::class, 'store'])
    ->middleware(['auth:sanctum', 'is_volunteer']);

// API 11 — Crowd Density
Route::get('/distribution-points/{distributionPoint}/crowd-density', [CrowdDensityController::class, 'show']);
Route::post('/crowd-density-reports', [CrowdDensityController::class, 'store'])
    ->middleware(['auth:sanctum', 'is_volunteer']);

// API 12 — Counters
Route::get('/distribution-points/{distributionPoint}/counters', [CounterController::class, 'index']);
Route::post('/distribution-points/{distributionPoint}/counters', [CounterController::class, 'store'])
    ->middleware(['auth:sanctum', 'is_staff']);
Route::put('/counters/{counter}', [CounterController::class, 'update'])->middleware(['auth:sanctum', 'is_staff']);

// API 15 — Public Queue Display (no auth — kiosk/device-scoped)
Route::get('/distribution-points/{distributionPoint}/public-display', [PublicDisplayController::class, 'show']);

// Everything below requires authentication.
Route::middleware('auth:sanctum')->group(function () {
    // API 5 — Join Queue
    Route::post('/queues/{queue}/join', [QueueController::class, 'join']);

    // API 6 — My Queue Position / API 7 — Leave / Rejoin
    Route::get('/queue-entries/{queueEntry}/position', [QueueEntryController::class, 'position']);
    Route::post('/queue-entries/{queueEntry}/leave', [QueueEntryController::class, 'leave']);
    Route::post('/queue-entries/{queueEntry}/rejoin', [QueueEntryController::class, 'rejoin']);
    Route::post('/queue-entries/{queueEntry}/cancel', [QueueEntryController::class, 'cancel']);

    // API 8, 13, and the entries listing — every /staff/queues/* action is
    // Location Staff territory (FR-008, FR-015); admins pass too via
    // EnsureIsStaff.
    Route::middleware('is_staff')->group(function () {
        // Not one of the documented 16 — see StaffQueueController::entries()'s docblock.
        Route::get('/staff/queues/{queue}/entries', [StaffQueueController::class, 'entries']);

        // API 8 — Staff Queue Management
        Route::put('/staff/queues/{queue}/call-next', [StaffQueueController::class, 'callNext']);
        Route::put('/staff/queues/{queue}/pause', [StaffQueueController::class, 'pause']);
        Route::put('/staff/queues/{queue}/resume', [StaffQueueController::class, 'resume']);
        Route::put('/staff/queues/{queue}/close', [StaffQueueController::class, 'close']);

        // API 13 — Skip / Recall Customer
        Route::put('/staff/queues/{queue}/skip', [StaffQueueController::class, 'skip']);
        Route::put('/staff/queues/{queue}/recall/{entryId}', [StaffQueueController::class, 'recall']);
    });

    // API 9 — Community Updates (FR-009: verified volunteers and staff)
    Route::post('/community-updates', [CommunityUpdateController::class, 'store'])->middleware('is_volunteer');
    Route::get('/distribution-points/{distributionPoint}/community-updates', [CommunityUpdateController::class, 'index']);

    // API 10 — Priority Registration: any resident can request (FR-011);
    // reviewing/verifying the queue is staff/admin territory.
    Route::post('/priority-registrations', [PriorityRegistrationController::class, 'store']);
    Route::get('/priority-registrations/mine', [PriorityRegistrationController::class, 'mine']);
    Route::get('/priority-registrations', [PriorityRegistrationController::class, 'index'])->middleware('is_staff');
    Route::put('/priority-registrations/{priorityRegistration}/verify', [PriorityRegistrationController::class, 'verify'])
        ->middleware('is_staff');

    // API 14 — Favorites
    Route::post('/distribution-points/{distributionPoint}/favorite', [FavoritePointController::class, 'store']);
    Route::delete('/distribution-points/{distributionPoint}/favorite', [FavoritePointController::class, 'destroy']);

    // API 16 — Analytics (doc: "Authentication Required: Yes (staff/admin role)")
    Route::get('/analytics', [AnalyticsController::class, 'index'])->middleware('is_staff');

    // Not one of the documented 16 — see NotificationController's docblock.
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // FR-013 — admin: manage staff & volunteer accounts and their point assignments
    Route::prefix('admin')->middleware('is_admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::post('/users/{user}/assignments', [AdminUserController::class, 'assign']);
        Route::delete('/users/{user}/assignments/{distributionPointId}', [AdminUserController::class, 'unassign']);
    });
});
