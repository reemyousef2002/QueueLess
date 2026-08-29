<?php

namespace Database\Seeders;

use App\Models\Counter;
use App\Models\CrowdDensityReport;
use App\Models\DistributionPoint;
use App\Models\PriorityRegistration;
use App\Models\Queue;
use App\Models\QueueEntry;
use App\Models\ResourceStatus;
use App\Models\StaffAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds data that mirrors the frontend's mock ORGS array
 * (src/features/queueless/data.ts) so the two stay visually consistent
 * once the client is wired up to the real API.
 */
class QueueLessSeeder extends Seeder
{
    /** Resource type shown per distribution point type. */
    private const RESOURCE_TYPE = [
        'clinic' => 'medical_supplies',
        'bakery' => 'bread',
        'water_point' => 'water',
        'community_kitchen' => 'food',
        'university_office' => 'service_slots',
        'government_office' => 'service_slots',
    ];

    public function run(): void
    {
        $admin = User::create([
            'name' => 'System Admin',
            'phone' => '+970590000001',
            'email' => 'admin@queueless.test',
            'password_hash' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
        ]);

        $residents = collect(range(1, 20))->map(fn (int $i) => User::create([
            'name' => "Resident {$i}",
            'phone' => sprintf('+97059%06d', 100 + $i),
            'email' => "resident{$i}@queueless.test",
            'password_hash' => Hash::make('password'),
            'role' => User::ROLE_RESIDENT,
        ]));

        // One verified, priority-eligible resident for demoing FR-011.
        $priorityResident = $residents->first();
        PriorityRegistration::create([
            'user_id' => $priorityResident->id,
            'category' => PriorityRegistration::ELDERLY,
            'verified_by' => $admin->id,
            'verified_at' => now(),
        ]);

        $orgs = [
            ['name' => 'Al-Amal Clinic', 'type' => 'clinic', 'status' => 'available', 'density' => 'green', 'queueSize' => 6, 'avgService' => 4, 'open' => true, 'counters' => 2, 'staffed' => true],
            ['name' => 'Barakah Community Bakery', 'type' => 'bakery', 'status' => 'limited', 'density' => 'yellow', 'queueSize' => 14, 'avgService' => 2, 'open' => true, 'counters' => 1, 'staffed' => false],
            ['name' => 'Al-Nour Water Point', 'type' => 'water_point', 'status' => 'available', 'density' => 'yellow', 'queueSize' => 9, 'avgService' => 3, 'open' => true, 'counters' => 3, 'staffed' => false],
            ['name' => 'Rahma Community Kitchen', 'type' => 'community_kitchen', 'status' => 'depleted', 'density' => 'red', 'queueSize' => 21, 'avgService' => 4, 'open' => true, 'counters' => 2, 'staffed' => false],
            ['name' => 'Student Affairs Office', 'type' => 'university_office', 'status' => 'available', 'density' => 'green', 'queueSize' => 3, 'avgService' => 5, 'open' => true, 'counters' => 1, 'staffed' => true],
            ['name' => 'Civil Registry Office', 'type' => 'government_office', 'status' => 'limited', 'density' => 'yellow', 'queueSize' => 11, 'avgService' => 3, 'open' => false, 'counters' => 2, 'staffed' => true],
        ];

        $residentCursor = 0;

        foreach ($orgs as $org) {
            $point = DistributionPoint::create([
                'name' => $org['name'],
                'type' => $org['type'],
                'address' => $org['name'].', Gaza',
                'is_active' => true,
            ]);

            ResourceStatus::create([
                'distribution_point_id' => $point->id,
                'resource_type' => self::RESOURCE_TYPE[$org['type']],
                'availability' => $org['status'],
                'updated_by' => $admin->id,
            ]);

            CrowdDensityReport::create([
                'distribution_point_id' => $point->id,
                'density_level' => $org['density'],
                'reported_by' => null, // system-estimated seed value
            ]);

            for ($c = 1; $c <= $org['counters']; $c++) {
                Counter::create([
                    'distribution_point_id' => $point->id,
                    'label' => "Counter {$c}",
                    'is_active' => true,
                ]);
            }

            $queue = Queue::create([
                'distribution_point_id' => $point->id,
                'status' => $org['open'] ? Queue::STATUS_OPEN : Queue::STATUS_CLOSED,
                'avg_service_minutes' => $org['avgService'],
            ]);

            $slug = Str::slug($org['name']);

            if ($org['staffed']) {
                $staffUser = User::create([
                    'name' => 'Staff — '.$org['name'],
                    'phone' => '+970591'.str_pad((string) (100 + $residentCursor), 6, '0', STR_PAD_LEFT),
                    'email' => "staff-{$slug}@queueless.test",
                    'password_hash' => Hash::make('password'),
                    'role' => User::ROLE_STAFF,
                ]);
                StaffAssignment::create(['user_id' => $staffUser->id, 'distribution_point_id' => $point->id]);
            } else {
                $volunteerUser = User::create([
                    'name' => 'Volunteer — '.$org['name'],
                    'phone' => '+970592'.str_pad((string) (100 + $residentCursor), 6, '0', STR_PAD_LEFT),
                    'email' => "volunteer-{$slug}@queueless.test",
                    'password_hash' => Hash::make('password'),
                    'role' => User::ROLE_VOLUNTEER,
                ]);
                StaffAssignment::create(['user_id' => $volunteerUser->id, 'distribution_point_id' => $point->id]);
            }

            for ($t = 1; $t <= $org['queueSize']; $t++) {
                $resident = $residents[$residentCursor % $residents->count()];
                $residentCursor++;

                QueueEntry::create([
                    'queue_id' => $queue->id,
                    'user_id' => $resident->id,
                    'ticket_number' => $t,
                    'status' => QueueEntry::STATUS_WAITING,
                    'priority_flag' => false,
                    'joined_at' => now()->subMinutes($org['queueSize'] - $t),
                ]);
            }
        }

        $this->command?->info('Seeded 6 distribution points, staff/volunteer accounts, and demo queues.');
        $this->command?->info('Login as admin: admin@queueless.test / password "password".');
    }
}
