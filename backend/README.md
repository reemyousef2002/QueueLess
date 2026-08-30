# QueueLess — Backend (Laravel API)

Implements the System Analysis Document's 19 functional requirements as a
Laravel 13 REST API: Sanctum token auth, the 10-entity database design, all
16 documented endpoints, and the queue/notification business logic behind
them.

## Stack

PHP 8.5 · Laravel 13 · MySQL · Laravel Sanctum · Laravel Notifications
(database channel, ready to swap in FCM).

## Setup

```sh
composer install
cp .env.example .env        # then set DB_* to your MySQL credentials
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8002
```

The app currently runs at **http://localhost:8002** (ports 8000 and 8001
were already taken locally by another project on this machine — change
`APP_URL` and `--port` freely if that's not the case for you).

## Seeded accounts (all passwords: `password`)

The frontend's login form asks for email (see below), so every seeded
account now has one, alongside its phone number — either works against
`POST /api/auth/login`, which accepts `identifier` as phone or email.

| Role | Email | Phone | Notes |
|---|---|---|---|
| admin | `admin@queueless.test` | `+970590000001` | full access |
| staff | `staff-al-amal-clinic@queueless.test` | `+970591000100` | Al-Amal Clinic |
| staff | `staff-student-affairs-office@queueless.test` | `+970591000150` | Student Affairs Office |
| staff | `staff-civil-registry-office@queueless.test` | `+970591000153` | Civil Registry Office (closed queue, for testing) |
| volunteer | `volunteer-barakah-community-bakery@queueless.test` | `+970592000106` | Barakah Community Bakery |
| volunteer | `volunteer-al-nour-water-point@queueless.test` | `+970592000120` | Al-Nour Water Point |
| volunteer | `volunteer-rahma-community-kitchen@queueless.test` | `+970592000129` | Rahma Community Kitchen |
| resident | `resident1@queueless.test` … `resident20@queueless.test` | `+97059000101` … `+97059000120` | 20 generic residents; #1 has a verified priority registration |

Exact accounts are also always recoverable with:
`php artisan tinker --execute="App\Models\User::where('role','!=','resident')->get(['name','email','phone','role'])->each(fn(\$u)=>print(\$u->role.' | '.\$u->email.' | '.\$u->phone.' | '.\$u->name.PHP_EOL));"`

Six distribution points are seeded with the same names/counts as the
frontend's mock data (`src/features/queueless/data.ts`) so the two line up
visually once the client is pointed at this API.

## Where this deviates from the doc

The System Analysis Document's schema and 16-endpoint list were followed as
closely as possible; a few things had to be added because the doc describes
*what* the system does but not every implementation detail an API needs to
actually run:

- **`staff_assignments` table** — NFR-05 ("staff/volunteers manage only
  the points assigned to them") requires a many-to-many link between users
  and distribution points that isn't in the ERD. Added as a plain junction
  table, mirroring `favorite_points`.
- **`otp_codes` table** — infrastructure for FR-001/FR-002 phone OTP login.
  Not a business entity, so not in the ERD either. There's no SMS provider
  wired up: codes are written to `storage/logs/laravel.log` instead of
  texted. Swap the delivery call in `OtpService::sendCode()` for a real
  gateway later.
- **`notifications` table** — Laravel's standard polymorphic notifications
  table, needed for FR-006 (turn-approaching) and FR-017 (favorite point
  changed). Notifications are delivered on the `database` channel for now;
  add a push channel (Firebase per the architecture doc, or anything else)
  in `via()` on the two Notification classes once credentials exist.
- **`queue_entries` gained `served_at`, `left_at`, `notified_at`** beyond
  the doc's column list — needed respectively for: computing service
  duration for analytics/the rolling average wait estimate, tracking the
  FR-007 leave/rejoin grace period, and not re-sending the FR-006
  turn-approaching notification on every call-next.
- **`config/queueless.php`** — the grace period (FR-007) and notification
  threshold (FR-006) are described as "configurable" but the doc doesn't
  say where; they're `.env`-backed config values here
  (`QUEUE_GRACE_PERIOD_MINUTES`, `QUEUE_NOTIFY_THRESHOLD`).
- **Login form asks for email, not phone** — FR-001 lists phone as the
  primary identifier and that's still what the database/registration form
  enforces, but the login screen was switched to ask for email at the
  frontend's request. `AuthService::loginWithPassword()` was always
  phone-or-email underneath, so no backend change was needed — this is a
  UI-only deviation from the doc's stated primary identifier.
- **A few extra routes** beyond the documented 16, needed to make
  documented *permissions* actually reachable: `GET/POST /api/admin/users`
  and assignment endpoints (FR-013's "manage staff and volunteer
  accounts"), and `GET /api/priority-registrations` +
  `PUT .../{id}/verify` (the Administrator's "verify priority
  registrations" permission, and FR-011 itself needs *some* verify step
  before a registration can grant priority).

## Layering (NFR-09)

- `app/Http/Controllers/Api` — thin, HTTP-only.
- `app/Http/Requests` — validation + per-endpoint authorization.
- `app/Services` — business logic (queue position/wait-time math, call-next
  state machine, resource status, analytics, OTP, auth).
- `app/Models` — Eloquent + relationships from the ERD. All primary keys
  are UUIDs via `App\Models\Concerns\HasUuid`.

## Testing

`php artisan test` runs the default Laravel test scaffold (PHPUnit/Pest per
the doc's testing strategy). No feature tests have been written yet for the
QueueLess-specific endpoints — that's a natural next step.
