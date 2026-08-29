<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'phone', 'email', 'password_hash', 'role', 'preferred_language'])]
#[Hidden(['password_hash', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    public const ROLE_RESIDENT = 'resident';

    public const ROLE_VOLUNTEER = 'volunteer';

    public const ROLE_STAFF = 'staff';

    public const ROLE_ADMIN = 'admin';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    /**
     * Laravel's auth layer looks for a "password" attribute/accessor when
     * checking credentials. Our column is named password_hash to match the
     * database design doc, so we bridge the two here.
     */
    public function getAuthPassword(): ?string
    {
        return $this->password_hash;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isVolunteer(): bool
    {
        return $this->role === self::ROLE_VOLUNTEER;
    }

    public function isStaffOrAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_STAFF, self::ROLE_ADMIN], true);
    }

    public function isVolunteerOrStaff(): bool
    {
        return in_array($this->role, [self::ROLE_VOLUNTEER, self::ROLE_STAFF, self::ROLE_ADMIN], true);
    }

    /** @return HasMany<QueueEntry, $this> */
    public function queueEntries(): HasMany
    {
        return $this->hasMany(QueueEntry::class);
    }

    /** @return HasMany<CommunityUpdate, $this> */
    public function communityUpdates(): HasMany
    {
        return $this->hasMany(CommunityUpdate::class, 'reporter_id');
    }

    /** @return HasMany<PriorityRegistration, $this> */
    public function priorityRegistrations(): HasMany
    {
        return $this->hasMany(PriorityRegistration::class);
    }

    /** @return HasMany<CrowdDensityReport, $this> */
    public function crowdDensityReports(): HasMany
    {
        return $this->hasMany(CrowdDensityReport::class, 'reported_by');
    }

    /** @return HasMany<FavoritePoint, $this> */
    public function favoritePoints(): HasMany
    {
        return $this->hasMany(FavoritePoint::class);
    }

    /** @return HasMany<StaffAssignment, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }
}
