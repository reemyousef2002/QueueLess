<?php

return [
    /*
     * FR-007: how long a user may leave the physical location and still
     * rejoin their queue without losing their position.
     */
    'grace_period_minutes' => (int) env('QUEUE_GRACE_PERIOD_MINUTES', 15),

    /*
     * FR-006: how many people ahead of a user triggers the
     * "your turn is approaching" push notification.
     */
    'notify_when_people_ahead' => (int) env('QUEUE_NOTIFY_THRESHOLD', 1),

    /*
     * Fallback average service time (minutes) used to estimate wait time
     * for a queue that has not yet built up a rolling average.
     */
    'default_service_minutes' => (float) env('QUEUE_DEFAULT_SERVICE_MINUTES', 5),
];
