<?php

return [
    'inquiries' => [
        'staff_notification_email' => env('INQUIRY_STAFF_NOTIFICATION_EMAIL'),
        /*
         | Hours after submission before an unanswered inquiry is treated as SLA-overdue
         | in the admin list, export, and Operations dashboard.
         */
        'unanswered_sla_hours' => max(1, (int) env('INQUIRY_UNANSWERED_SLA_HOURS', 48)),
        'canned_replies' => [
            'ack_received' => 'Salamat sa inyong inquiry. Natanggap na ng District Office ang inyong mensahe at sinusuri ito ng assigned officer.',
            'need_more_info' => 'Salamat sa inyong inquiry. Para matulungan kayo nang tama, mangyaring magbigay ng karagdagang detalye tungkol sa inyong concern nang hindi nagpapadala ng ID, medical, o confidential documents.',
            'routed_city' => 'Salamat sa inyong inquiry. Batay sa inyong concern, ito ay karaniwang hinahawakan ng San Juan City Government o barangay office. Matutulungan kayo naming i-route sa tamang tanggapan.',
            'program_guidance' => 'Salamat sa inyong inquiry tungkol sa programa. Pakitingnan ang Programs page para sa kasalukuyang status, requirements, at opisyal na source. Ang District Office ay maaaring magbigay ng guidance, ngunit ang final eligibility ay sumusunod sa official agency rules.',
            'closed_resolved' => 'Salamat sa inyong inquiry. Naitala na ang aming tugon o routing guidance. Kung may bago kayong concern, maaari kayong magsumite ng bagong inquiry.',
        ],
    ],

    'privacy' => [
        'inquiry_retention_days' => max(1, (int) env('INQUIRY_RETENTION_DAYS', 180)),
    ],

    'media' => [
        /*
         | Filament FileUpload maxSize() expects kilobytes.
         | 5120 KB = 5 MB.
         */
        'max_upload_kilobytes' => max(1, (int) env('APPROVED_MEDIA_MAX_KB', 5120)),
        'allowed_mimetypes' => ['image/jpeg', 'image/png', 'image/webp'],
        'news_directory' => 'approved-media/news',
        'project_evidence_directory' => 'approved-media/project-evidence',
    ],

    'cache' => [
        /*
         | Public Inertia prop payloads. Invalidate on publish/edit via PublicCacheInvalidator.
         */
        'public_ttl_seconds' => max(5, (int) env('PUBLIC_CONTENT_CACHE_TTL', 60)),
    ],

    'jobs' => [
        'default_tries' => max(1, (int) env('QUEUE_JOB_TRIES', 3)),
        'default_backoff_seconds' => max(1, (int) env('QUEUE_JOB_BACKOFF', 60)),
        'default_timeout_seconds' => max(30, (int) env('QUEUE_JOB_TIMEOUT', 120)),
    ],
];
