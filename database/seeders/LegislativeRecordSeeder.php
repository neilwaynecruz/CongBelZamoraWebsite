<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\LegislativeRoleValue;
use App\Enums\LegislativeStatusTone;
use App\Enums\SourceType;
use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LegislativeRecordSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'legislation@example.com'],
            [
                'name' => 'Legislative Content Owner',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::LegislativeEditor,
                'email_verified_at' => now(),
            ],
        );

        $approver = User::query()->firstOrCreate(
            ['email' => 'legislation-approver@example.com'],
            [
                'name' => 'Legislative Reviewer',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        $roles = [
            LegislativeRoleValue::PrincipalAuthor->value => LegislativeRole::query()->updateOrCreate(
                ['value' => LegislativeRoleValue::PrincipalAuthor],
                [
                    'name' => LegislativeRoleValue::PrincipalAuthor->label(),
                    'slug' => 'principal-author',
                    'label' => LegislativeRoleValue::PrincipalAuthor->label(),
                    'sort_order' => 10,
                    'is_active' => true,
                ],
            ),
            LegislativeRoleValue::CoAuthor->value => LegislativeRole::query()->updateOrCreate(
                ['value' => LegislativeRoleValue::CoAuthor],
                [
                    'name' => LegislativeRoleValue::CoAuthor->label(),
                    'slug' => 'co-author',
                    'label' => LegislativeRoleValue::CoAuthor->label(),
                    'sort_order' => 20,
                    'is_active' => true,
                ],
            ),
            LegislativeRoleValue::Sponsor->value => LegislativeRole::query()->updateOrCreate(
                ['value' => LegislativeRoleValue::Sponsor],
                [
                    'name' => LegislativeRoleValue::Sponsor->label(),
                    'slug' => 'sponsor',
                    'label' => LegislativeRoleValue::Sponsor->label(),
                    'sort_order' => 30,
                    'is_active' => true,
                ],
            ),
        ];

        foreach ($this->records() as $index => $recordData) {
            $status = LegislativeStatus::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['status'])],
                [
                    'name' => $recordData['status'],
                    'label' => $recordData['status'],
                    'tone' => LegislativeStatusTone::from($recordData['statusTone']),
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $topic = LegislativeTopic::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['topic'])],
                [
                    'name' => $recordData['topic'],
                    'description' => 'Legislative tracker topic migrated from the verified Phase 6 route props.',
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $record = LegislativeRecord::query()->updateOrCreate(
                ['bill_number' => $recordData['billNumber']],
                [
                    'legislative_role_id' => $roles[$recordData['role']]->id,
                    'legislative_status_id' => $status->id,
                    'legislative_topic_id' => $topic->id,
                    'slug' => Str::slug($recordData['billNumber'].' '.$recordData['publicTitle']),
                    'official_title' => $recordData['officialTitle'],
                    'public_title' => $recordData['publicTitle'],
                    'congress' => $recordData['congress'],
                    'committee' => $recordData['committee'],
                    'filed_at_label' => $recordData['filedAt'],
                    'filed_at' => null,
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'plain_language_summary' => $recordData['plainLanguageSummary'],
                    'why_it_matters' => $recordData['whyItMatters'],
                    'san_juan_relevance' => $recordData['sanJuanRelevance'],
                    'official_source_label' => $recordData['officialSourceLabel'],
                    'official_source_url' => $recordData['officialSourceUrl'],
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'published_at' => now()->subDay(),
                    'review_due_at' => now()->addMonth(),
                    'sort_order' => ($index + 1) * 10,
                    'metadata' => ['phase_6_source' => true],
                ],
            );

            $record->sourceHistories()->updateOrCreate(
                ['source_url' => $recordData['officialSourceUrl']],
                [
                    'source_label' => $recordData['officialSourceLabel'],
                    'status_snapshot' => $recordData['status'],
                    'checked_by_id' => $approver->id,
                    'checked_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'notes' => 'Phase 6 legislative tracker source migrated into backend-owned source history.',
                    'metadata' => ['phase_7_legislation_slice' => true],
                ],
            );

            $this->attachGovernance($record, $owner, $approver);
        }
    }

    /**
     * @return array<int, array{
     *     billNumber: string,
     *     officialTitle: string,
     *     publicTitle: string,
     *     congress: string,
     *     role: string,
     *     status: string,
     *     statusTone: string,
     *     topic: string,
     *     committee: string,
     *     filedAt: string,
     *     plainLanguageSummary: string,
     *     whyItMatters: string,
     *     sanJuanRelevance: string,
     *     officialSourceLabel: string,
     *     officialSourceUrl: string
     * }>
     */
    private function records(): array
    {
        return [
            [
                'billNumber' => 'HB00123',
                'officialTitle' => 'Act promoting solo parent welfare',
                'publicTitle' => 'Solo Parent Welfare Extension Act',
                'congress' => '20th Congress',
                'role' => LegislativeRoleValue::PrincipalAuthor->value,
                'status' => 'Passed House; Sent to Senate',
                'statusTone' => LegislativeStatusTone::Passed->value,
                'topic' => 'Solo Parents and Family Support',
                'committee' => 'House Bill Tracker (K099)',
                'filedAt' => 'Source-pending date',
                'plainLanguageSummary' => 'This measure is presented as a solo-parent welfare bill in the Phase 1 attribution worksheet. The public summary should explain support for solo parents and their families without adding claims beyond the official record.',
                'whyItMatters' => 'Solo-parent households need clear information on welfare, education, and family-support measures that may affect benefits or public-service access.',
                'sanJuanRelevance' => 'San Juan residents can see Rep. Bel Zamora\'s documented role and follow the official House source for the full bill history.',
                'officialSourceLabel' => 'View House member record',
                'officialSourceUrl' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
            ],
            [
                'billNumber' => 'HB00456',
                'officialTitle' => 'Act establishing digital classrooms',
                'publicTitle' => 'Digital Classroom Infrastructure Grant',
                'congress' => '20th Congress',
                'role' => LegislativeRoleValue::CoAuthor->value,
                'status' => 'Pending Committee Review',
                'statusTone' => LegislativeStatusTone::Pending->value,
                'topic' => 'Education and Digital Access',
                'committee' => 'Pending committee review',
                'filedAt' => 'Source-pending date',
                'plainLanguageSummary' => 'This measure is listed as a digital-classroom bill in the Phase 1 attribution worksheet. Public copy should focus on classroom access and education infrastructure while preserving the official title.',
                'whyItMatters' => 'Digital learning infrastructure affects students, teachers, and families who rely on reliable school technology.',
                'sanJuanRelevance' => 'The tracker identifies Rep. Bel Zamora as co-author and directs residents to official House records for status changes.',
                'officialSourceLabel' => 'View House member record',
                'officialSourceUrl' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
            ],
            [
                'billNumber' => 'HB00789',
                'officialTitle' => 'San Juan heritage preservation act',
                'publicTitle' => 'San Juan Heritage Preservation Act',
                'congress' => '20th Congress',
                'role' => LegislativeRoleValue::PrincipalAuthor->value,
                'status' => 'Passed into Republic Act (RA 12345)',
                'statusTone' => LegislativeStatusTone::Enacted->value,
                'topic' => 'Heritage and Culture',
                'committee' => 'Official Gazette RA Registry',
                'filedAt' => 'Source-pending date',
                'plainLanguageSummary' => 'This measure is listed as a San Juan heritage preservation act in the Phase 1 attribution worksheet. Any public claim should remain tied to the official Gazette or House record.',
                'whyItMatters' => 'Heritage measures can protect places, history, and civic identity that matter to San Juan residents.',
                'sanJuanRelevance' => 'The measure is locally relevant because it concerns San Juan heritage and should be checked against the final official Republic Act record before launch.',
                'officialSourceLabel' => 'View Official Gazette',
                'officialSourceUrl' => 'https://www.officialgazette.gov.ph/',
            ],
        ];
    }

    private function attachGovernance(LegislativeRecord $record, User $owner, User $approver): void
    {
        $record->sourceRecords()->updateOrCreate(
            ['title' => $record->official_source_label],
            [
                'url' => $record->official_source_url,
                'source_type' => SourceType::PrimaryOfficial,
                'source_tier' => 'tier_1',
                'description' => 'Phase 7 backend-owned legislative source record migrated from verified Phase 6 public content.',
                'content_owner_id' => $owner->id,
                'verified_by_id' => $approver->id,
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'last_reviewed_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'next_review_at' => now()->addMonth(),
                'public_status' => 'published',
                'metadata' => ['phase_7_legislation_slice' => true],
            ],
        );

        $record->approvalRecords()->updateOrCreate(
            ['revision_number' => 1],
            [
                'status' => ContentApprovalStatus::Published,
                'submitted_by_id' => $owner->id,
                'reviewed_by_id' => $approver->id,
                'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subDay(),
                'approved_at' => now()->subDay(),
                'published_at' => now()->subDay(),
                'review_notes' => 'Approved Phase 6 legislative tracker content moved into backend-owned Phase 7 records.',
                'is_fast_track' => false,
                'metadata' => ['phase_7_legislation_slice' => true],
            ],
        );
    }
}
