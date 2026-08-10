<?php

use App\Enums\AdminRole;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;

return [
    'office' => [
        'representative_name' => 'Atty. Congresswoman Bel Zamora',
        'office_name' => 'District Office of Congresswoman Ysabel "Atty. Bel" Zamora',
        'district' => 'Lone District of San Juan City',
        'portal_name' => 'Digital District Office for San Juan City',
        'portal_subtitle' => 'Official Constituent-Services Portal',
        'public_service_statement' => 'The District Office provides verified public-service guidance, legislative updates, program information, and official contact pathways for San Juaneños.',
        'response_time_policy' => env('DISTRICT_OFFICE_RESPONSE_TIME_POLICY', 'Inquiries are reviewed by authorized staff. The target first-response window is within 2 working days, subject to category, verification needs, holidays, and office workload.'),
        'contact_channels' => [
            'phone' => env('DISTRICT_OFFICE_PHONE', '0976-062-3339'),
            'email' => env('DISTRICT_OFFICE_EMAIL'),
            'contact_form_path' => '/contact',
            'office_location' => env('DISTRICT_OFFICE_LOCATION', '3rd Floor, E-GovPH Serbisyo Hub, Makabagong San Juan National Government Center, Pinaglabanan St., San Juan City'),
            'office_hours' => env('DISTRICT_OFFICE_HOURS', 'Office hours and service-specific schedules must be confirmed with the District Office before publication.'),
        ],
    ],

    'inquiries' => [
        'staff_notification_email' => env('INQUIRY_STAFF_NOTIFICATION_EMAIL'),
        /*
         | Hours after submission before an unanswered inquiry is treated as SLA-overdue
         | in the admin list, export, and Operations dashboard.
         */
        'unanswered_sla_hours' => max(1, (int) env('INQUIRY_UNANSWERED_SLA_HOURS', 48)),
        'case_management' => [
            'default_priority' => InquiryPriority::Normal->value,
            'default_office_unit' => 'Constituent Services Desk',
            'overdue_reminder_grace_hours' => max(1, (int) env('INQUIRY_OVERDUE_REMINDER_GRACE_HOURS', 2)),
            'closure_reasons' => [
                'resolved_guidance_given' => 'Resolved - guidance given',
                'routed_to_responsible_office' => 'Routed to responsible office',
                'duplicate_or_follow_up' => 'Duplicate or follow-up logged',
                'insufficient_contact_information' => 'Insufficient contact information',
                'outside_district_office_scope' => 'Outside District Office scope',
            ],
        ],
        'category_routing' => [
            InquiryCategory::EducationalAssistance->value => [
                'office_unit' => 'Education and Scholarship Assistance Desk',
                'priority' => InquiryPriority::High->value,
                'target_first_response_hours' => 24,
                'acknowledgment_variant' => 'education_program',
                'routing_note' => 'Route to education assistance staff for program-status, requirements, and application-channel guidance.',
            ],
            InquiryCategory::MedicalSocialServices->value => [
                'office_unit' => 'Constituent Services and Agency Routing Desk',
                'priority' => InquiryPriority::High->value,
                'target_first_response_hours' => 24,
                'acknowledgment_variant' => 'service_routing',
                'routing_note' => 'Route for privacy-safe guidance and referral to the appropriate city, barangay, or national-agency service channel.',
            ],
            InquiryCategory::LegislativeFeedback->value => [
                'office_unit' => 'Legislative Affairs Desk',
                'priority' => InquiryPriority::Normal->value,
                'target_first_response_hours' => 48,
                'acknowledgment_variant' => 'legislative_feedback',
                'routing_note' => 'Route to legislative staff for bill, policy, committee, and public-consultation feedback review.',
            ],
            InquiryCategory::GeneralInquiry->value => [
                'office_unit' => 'Constituent Services Desk',
                'priority' => InquiryPriority::Normal->value,
                'target_first_response_hours' => 48,
                'acknowledgment_variant' => 'standard',
                'routing_note' => 'Route for general District Office information and next-step guidance.',
            ],
        ],
        'canned_replies' => [
            'ack_received' => 'Salamat sa inyong inquiry. Natanggap na ng District Office ang inyong mensahe at sinusuri ito ng assigned officer.',
            'need_more_info' => 'Salamat sa inyong inquiry. Para matulungan kayo nang tama, mangyaring magbigay ng karagdagang detalye tungkol sa inyong concern nang hindi nagpapadala ng ID, medical, o confidential documents.',
            'routed_city' => 'Salamat sa inyong inquiry. Batay sa inyong concern, ito ay karaniwang hinahawakan ng San Juan City Government o barangay office. Matutulungan kayo naming i-route sa tamang tanggapan.',
            'program_guidance' => 'Salamat sa inyong inquiry tungkol sa programa. Pakitingnan ang Programs page para sa kasalukuyang status, requirements, at opisyal na source. Ang District Office ay maaaring magbigay ng guidance, ngunit ang final eligibility ay sumusunod sa official agency rules.',
            'closed_resolved' => 'Salamat sa inyong inquiry. Naitala na ang aming tugon o routing guidance. Kung may bago kayong concern, maaari kayong magsumite ng bagong inquiry.',
        ],
        'acknowledgment_variants' => [
            'standard' => 'Your inquiry has been received and routed for District Office review.',
            'education_program' => 'Your education-program inquiry has been received. Staff will review the program category, published requirements, and official application guidance.',
            'service_routing' => 'Your service-routing inquiry has been received. Staff will review the concern and may direct you to the responsible city, barangay, or national-agency office.',
            'legislative_feedback' => 'Your legislative feedback has been received for staff review and routing to the appropriate legislative desk.',
        ],
        'public_workflow' => [
            'expected_response' => 'Target first response: within 2 working days for most inquiries, with faster routing targets for education and urgent service-routing categories when staffing allows.',
            'privacy_notice' => 'Do not submit IDs, certificates, medical records, student records, financial documents, or images of private documents through the public inquiry form.',
            'after_submission' => [
                'A reference code is generated immediately after successful submission.',
                'The inquiry is routed by category to the appropriate District Office desk.',
                'Authorized staff review the message inside the admin case workflow; citizen details remain encrypted at rest.',
                'If the concern belongs to another office, staff may provide routing guidance rather than final approval or case acceptance.',
            ],
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

    'staff' => [
        /*
         | When true, active AdminRole users must confirm Fortify 2FA before using /admin.
         | Enrollment remains available at /settings/security.
         */
        'require_2fa' => filter_var(env('STAFF_2FA_REQUIRED', false), FILTER_VALIDATE_BOOLEAN),
        /*
         | When true, staff notifications also write Laravel database notifications for the Filament bell.
         */
        'database_notifications' => filter_var(env('STAFF_DATABASE_NOTIFICATIONS', true), FILTER_VALIDATE_BOOLEAN),
    ],

    'admin_console' => [
        'enabled' => filter_var(env('ADMIN_CONSOLE_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'filament_fallback_enabled' => filter_var(env('FILAMENT_FALLBACK_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'modules' => [
            'inquiries' => filter_var(env('ADMIN_CONSOLE_INQUIRIES_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'content_approvals' => filter_var(env('ADMIN_CONSOLE_CONTENT_APPROVALS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        ],
    ],

    'governance' => [
        'content_approval' => [
            'workflow' => 'Draft or Needs revision -> For review -> Approved -> Scheduled or Published -> Archived.',
            'fast_track_requires_review_note' => true,
            'pre_publish_validation' => true,
            'change_summary_required' => true,
            'stale_warning_days_before_review_due' => 7,
            'policy_note' => 'Public content must be owned, sourced, verified, and reviewable before publication. Uncertain public statements must be tied to an official source or explicitly flagged for reviewer attention.',
            'archive_reasons' => [
                'superseded' => 'Superseded by newer information',
                'expired' => 'Program, announcement, or advisory expired',
                'source_recheck' => 'Temporarily unpublished for source recheck',
                'office_direction' => 'Archived by District Office direction',
                'duplicate' => 'Duplicate or merged content',
            ],
        ],

        'workflow_roles' => [
            'draft' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::CommunicationsEditor->value,
                AdminRole::ProgramOfficer->value,
                AdminRole::LegislativeEditor->value,
                AdminRole::ProjectEditor->value,
                AdminRole::MediaManager->value,
            ],
            'review' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
            ],
            'approve' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
            ],
            'schedule' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
            ],
            'publish' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
                AdminRole::ReviewerApprover->value,
            ],
            'archive' => [
                AdminRole::SuperAdministrator->value,
                AdminRole::ContentAdministrator->value,
            ],
        ],

        'modules' => [
            'programs' => [
                'label' => 'Programs',
                'model' => Program::class,
                'review_cadence_days' => 30,
                'required_publish_fields' => [
                    'program_category_id',
                    'title',
                    'slug',
                    'status',
                    'summary',
                    'responsible_office_label',
                    'source_label',
                    'source_url',
                    'verified_at',
                    'review_due_at',
                    'content_owner_id',
                ],
                'source_policy' => 'Program status, requirements, deadlines, and benefits must point to an official agency or District Office-approved source.',
                'requires_source' => true,
                'uncertain_statement_metadata_flag' => 'contains_uncertain_public_statement',
                'uncertain_statement_metadata_note' => 'uncertain_public_statement_note',
            ],
            'projects' => [
                'label' => 'Projects',
                'model' => Project::class,
                'review_cadence_days' => 45,
                'required_publish_fields' => [
                    'project_category_id',
                    'project_status_id',
                    'title',
                    'slug',
                    'barangay',
                    'location',
                    'responsible_office',
                    'summary',
                    'evidence_summary',
                    'source_label',
                    'source_url',
                    'verified_at',
                    'review_due_at',
                    'content_owner_id',
                ],
                'source_policy' => 'Project status, location, partner office, evidence notes, and impact statements must be source-linked or reviewed by the responsible content owner.',
                'requires_source' => true,
                'uncertain_statement_metadata_flag' => 'contains_uncertain_public_statement',
                'uncertain_statement_metadata_note' => 'uncertain_public_statement_note',
            ],
            'news' => [
                'label' => 'News and Advisories',
                'model' => NewsRecord::class,
                'review_cadence_days' => 30,
                'required_publish_fields' => [
                    'news_category_id',
                    'news_status_id',
                    'title',
                    'slug',
                    'published_at_label',
                    'summary',
                    'official_update',
                    'owner_label',
                    'source_label',
                    'source_url',
                    'source_verified_at',
                    'review_due_at',
                    'content_owner_id',
                ],
                'source_policy' => 'Announcements must clearly identify whether they are current, archived, or stale-review content, with source labels and review notes for reuse-sensitive advisories.',
                'requires_source' => true,
                'uncertain_statement_metadata_flag' => 'contains_uncertain_public_statement',
                'uncertain_statement_metadata_note' => 'uncertain_public_statement_note',
            ],
            'legislation' => [
                'label' => 'Legislation',
                'model' => LegislativeRecord::class,
                'review_cadence_days' => 30,
                'required_publish_fields' => [
                    'legislative_role_id',
                    'legislative_status_id',
                    'legislative_topic_id',
                    'bill_number',
                    'slug',
                    'official_title',
                    'public_title',
                    'plain_language_summary',
                    'official_source_label',
                    'official_source_url',
                    'verified_at',
                    'review_due_at',
                    'content_owner_id',
                ],
                'source_policy' => 'Legislative status, bill metadata, and plain-language summaries must link to official House or source-register records.',
                'requires_source' => true,
                'uncertain_statement_metadata_flag' => 'contains_uncertain_public_statement',
                'uncertain_statement_metadata_note' => 'uncertain_public_statement_note',
            ],
            'public_pages' => [
                'label' => 'Public Pages',
                'model' => PublicPage::class,
                'review_cadence_days' => 60,
                'required_publish_fields' => [
                    'slug',
                    'title',
                    'component',
                    'props',
                    'owner_label',
                    'verified_at',
                    'review_due_at',
                    'content_owner_id',
                ],
                'source_policy' => 'Static pages must use approved office wording. Pages containing service claims, deadlines, or external facts must include a source URL or an uncertainty review note.',
                'requires_source' => false,
                'uncertain_statement_metadata_flag' => 'contains_uncertain_public_statement',
                'uncertain_statement_metadata_note' => 'uncertain_public_statement_note',
            ],
        ],
    ],

    'reports' => [
        'cache_ttl_seconds' => max(5, (int) env('ADMIN_REPORTS_CACHE_TTL', 60)),
    ],
];
