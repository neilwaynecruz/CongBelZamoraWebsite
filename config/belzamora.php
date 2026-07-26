<?php

return [
    'inquiries' => [
        'staff_notification_email' => env('INQUIRY_STAFF_NOTIFICATION_EMAIL'),
    ],

    'privacy' => [
        'inquiry_retention_days' => (int) env('INQUIRY_RETENTION_DAYS', 180),
    ],
];
