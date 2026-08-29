<?php

namespace App\Services;

use App\Models\QueueEntry;
use Carbon\Carbon;

/**
 * FR-019 / API 16: aggregated stats for the analytics dashboard.
 */
class AnalyticsService
{
    /**
     * @return array{
     *   range: string,
     *   from: string,
     *   to: string,
     *   customers_served: int,
     *   abandoned_queues: int,
     *   avg_wait_minutes: float,
     *   avg_service_minutes: float,
     *   peak_hours: array<int, array{hour:int, count:int}>,
     * }
     */
    public function summary(string $range, ?string $distributionPointId = null): array
    {
        $from = match ($range) {
            '30d' => Carbon::now()->subDays(30),
            '3m' => Carbon::now()->subMonths(3),
            default => Carbon::now()->subDays(7),
        };

        $base = QueueEntry::query()
            ->join('queues', 'queues.id', '=', 'queue_entries.queue_id')
            ->when($distributionPointId, fn ($q) => $q->where('queues.distribution_point_id', $distributionPointId))
            ->where('queue_entries.joined_at', '>=', $from);

        $customersServed = (clone $base)->where('queue_entries.status', QueueEntry::STATUS_SERVED)->count();

        $abandoned = (clone $base)
            ->whereIn('queue_entries.status', [QueueEntry::STATUS_NO_SHOW, QueueEntry::STATUS_CANCELLED])
            ->count();

        $avgWaitMinutes = (clone $base)
            ->whereNotNull('queue_entries.called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queue_entries.joined_at, queue_entries.called_at)) / 60 as avg_minutes')
            ->value('avg_minutes');

        $avgServiceMinutes = (clone $base)
            ->whereNotNull('queue_entries.served_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, queue_entries.called_at, queue_entries.served_at)) / 60 as avg_minutes')
            ->value('avg_minutes');

        $peakHours = (clone $base)
            ->selectRaw('HOUR(queue_entries.joined_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['hour' => (int) $row->hour, 'count' => (int) $row->count])
            ->all();

        return [
            'range' => $range,
            'from' => $from->toIso8601String(),
            'to' => Carbon::now()->toIso8601String(),
            'customers_served' => $customersServed,
            'abandoned_queues' => $abandoned,
            'avg_wait_minutes' => round((float) ($avgWaitMinutes ?? 0), 1),
            'avg_service_minutes' => round((float) ($avgServiceMinutes ?? 0), 1),
            'peak_hours' => $peakHours,
        ];
    }
}
