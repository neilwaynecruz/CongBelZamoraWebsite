<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Enums\SourceType;
use App\Models\NewsCategory;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsRecordSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'communications@example.com'],
            [
                'name' => 'Communications Content Owner',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::CommunicationsEditor,
                'email_verified_at' => now(),
            ],
        );

        $approver = User::query()->firstOrCreate(
            ['email' => 'communications-approver@example.com'],
            [
                'name' => 'Communications Reviewer',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        foreach ($this->records() as $index => $recordData) {
            $category = NewsCategory::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['category'])],
                [
                    'name' => $recordData['category'],
                    'description' => 'News category used for public advisories, archive browsing, and source review.',
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $status = NewsStatus::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['statusLabel'])],
                [
                    'name' => $recordData['statusLabel'],
                    'value' => NewsStatusValue::from($recordData['status']),
                    'label' => $recordData['statusLabel'],
                    'tone' => NewsStatusTone::from($recordData['statusTone']),
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $record = NewsRecord::query()->updateOrCreate(
                ['slug' => $recordData['slug']],
                [
                    'news_category_id' => $category->id,
                    'news_status_id' => $status->id,
                    'title' => $recordData['title'],
                    'published_at_label' => $recordData['publishedAt'],
                    'published_at' => $recordData['publishedAtDate'],
                    'public_updated_at_label' => $recordData['updatedAt'],
                    'public_updated_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'source_label' => $recordData['sourceLabel'],
                    'source_url' => $recordData['sourceUrl'],
                    'summary' => $recordData['summary'],
                    'official_update' => $recordData['officialUpdate'],
                    'next_step' => $recordData['nextStep'],
                    'stale_note' => $recordData['staleNote'],
                    'owner_label' => $recordData['owner'],
                    'source_verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'review_due_at' => $recordData['reviewDueAt'],
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'sort_order' => ($index + 1) * 10,
                    'metadata' => ['phase_6_source' => true],
                ],
            );

            $record->mediaAssets()->updateOrCreate(
                ['source_url' => $recordData['sourceUrl']],
                [
                    'title' => $recordData['title'].' media review',
                    'media_type' => 'image',
                    'media_path' => null,
                    'source_label' => $recordData['sourceLabel'],
                    'caption' => $recordData['summary'],
                    'alt_text' => 'Public-service news image awaiting approved local asset, caption, and alt-text review.',
                    'rights_note' => $recordData['mediaRightsNote'],
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'verified_by_id' => $approver->id,
                    'sort_order' => 10,
                    'metadata' => ['phase_7_news_slice' => true],
                ],
            );

            $this->attachGovernance($record, $owner, $approver);
        }
    }

    /**
     * @return array<int, array{
     *     title: string,
     *     slug: string,
     *     category: string,
     *     status: string,
     *     statusLabel: string,
     *     statusTone: string,
     *     publishedAt: string,
     *     publishedAtDate: \DateTimeInterface,
     *     updatedAt: string,
     *     sourceLabel: string,
     *     sourceUrl: string,
     *     summary: string,
     *     officialUpdate: string,
     *     nextStep: string,
     *     staleNote: string,
     *     owner: string,
     *     mediaRightsNote: string,
     *     reviewDueAt: \DateTimeInterface
     * }>
     */
    private function records(): array
    {
        return [
            [
                'title' => 'Tulong Dunong orientation schedule for eligible students',
                'slug' => 'tulong-dunong-orientation',
                'category' => 'Education',
                'status' => NewsStatusValue::Current->value,
                'statusLabel' => 'Current advisory',
                'statusTone' => NewsStatusTone::Current->value,
                'publishedAt' => 'July 12, 2026',
                'publishedAtDate' => now()->setDate(2026, 7, 12)->startOfDay(),
                'updatedAt' => 'July 12, 2026',
                'sourceLabel' => 'CHED UniFAST information',
                'sourceUrl' => 'https://unifast.gov.ph/',
                'summary' => 'The advisory directs eligible San Juan students to current Tulong Dunong requirements, orientation guidance, and official application channels.',
                'officialUpdate' => 'Students are advised to confirm the active cycle, accepted documents, and deadline with the District Office and CHED / UniFAST source before preparing documents.',
                'nextStep' => 'Read the Tulong Dunong details page and use official channels only for follow-up questions.',
                'staleNote' => 'Review before every application cycle because slots, deadlines, and documentary requirements can change.',
                'owner' => 'Communications Lead with Education Assistance staff review',
                'mediaRightsNote' => 'Do not publish student images or documents without District Office approval, guardian consent where required, caption review, and alt-text review.',
                'reviewDueAt' => now()->addMonth(),
            ],
            [
                'title' => 'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
                'slug' => 'egovph-serbisyo-hub-launch',
                'category' => 'Digital Government',
                'status' => NewsStatusValue::Current->value,
                'statusLabel' => 'Current public-service update',
                'statusTone' => NewsStatusTone::Current->value,
                'publishedAt' => 'July 12, 2026',
                'publishedAtDate' => now()->setDate(2026, 7, 12)->startOfDay(),
                'updatedAt' => 'July 12, 2026',
                'sourceLabel' => 'View official San Juan news page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
                'summary' => 'The official San Juan source covers the inauguration and official launch of the eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center.',
                'officialUpdate' => 'Present this as a government-partnership service update and route residents to the responsible national or city channel for transaction-specific requirements.',
                'nextStep' => 'Use Gabay San Juaneño to identify the correct office for digital government service questions.',
                'staleNote' => 'Recheck the service scope and responsible office before publishing new transaction guidance.',
                'owner' => 'Communications Lead',
                'mediaRightsNote' => 'Use only an approved local copy with source, rights, caption, privacy, and alt-text review.',
                'reviewDueAt' => now()->addMonth(),
            ],
            [
                'title' => '2025 National Women\'s Month services documented',
                'slug' => 'national-womens-month-services-2025',
                'category' => 'Women and Family',
                'status' => NewsStatusValue::Archived->value,
                'statusLabel' => 'Archived official update',
                'statusTone' => NewsStatusTone::Archived->value,
                'publishedAt' => 'March 2025',
                'publishedAtDate' => now()->setDate(2025, 3, 1)->startOfDay(),
                'updatedAt' => 'July 12, 2026',
                'sourceLabel' => 'View official San Juan activity page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration',
                'summary' => 'The official activity page documents Women\'s Month services including healthcare, employment, legal consultation, civil-registry, and wellness support.',
                'officialUpdate' => 'Use as an archived service record with accurate city and partner-office attribution.',
                'nextStep' => 'For current services, check the latest official San Juan City or District Office advisory.',
                'staleNote' => 'Do not reuse dates, service lists, or participating offices for a new year without a fresh source.',
                'owner' => 'Communications Lead with partner-office confirmation',
                'mediaRightsNote' => 'Review consent, privacy, caption, and reuse rights before publishing any activity image involving residents.',
                'reviewDueAt' => now()->addMonths(3),
            ],
            [
                'title' => 'Gawad Agraryo 2025 held at Makabagong San Juan',
                'slug' => 'gawad-agraryo-2025',
                'category' => 'Government Partnerships',
                'status' => NewsStatusValue::Archived->value,
                'statusLabel' => 'Archived official update',
                'statusTone' => NewsStatusTone::Archived->value,
                'publishedAt' => '2025',
                'publishedAtDate' => now()->setDate(2025, 1, 1)->startOfDay(),
                'updatedAt' => 'July 12, 2026',
                'sourceLabel' => 'View official San Juan news page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025',
                'summary' => 'The official San Juan news page documents Gawad Agraryo 2025 at the Makabagong San Juan National Government Center and identifies officials present.',
                'officialUpdate' => 'Present the item as an event and government-partnership record without implying sole District Office operation.',
                'nextStep' => 'Use the official source for event context, captions, and approved publication wording.',
                'staleNote' => 'Confirm event title, date, agency ownership, and approved image rights before gallery use.',
                'owner' => 'Communications Lead',
                'mediaRightsNote' => 'Use only approved event images that keep event context visible and avoid misleading crops.',
                'reviewDueAt' => now()->addMonths(3),
            ],
            [
                'title' => 'Balik Sigla, Bigay Saya community gift-giving record needs review',
                'slug' => 'balik-sigla-bigay-saya-review',
                'category' => 'Community Outreach',
                'status' => NewsStatusValue::StaleReview->value,
                'statusLabel' => 'Needs review before reuse',
                'statusTone' => NewsStatusTone::StaleReview->value,
                'publishedAt' => 'December 2024',
                'publishedAtDate' => now()->setDate(2024, 12, 1)->startOfDay(),
                'updatedAt' => 'July 12, 2026',
                'sourceLabel' => 'View official San Juan activity page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program',
                'summary' => 'The official San Juan page and PIA coverage document a previous holiday gift-giving activity involving San Juan officials.',
                'officialUpdate' => 'This record is retained as historical evidence until current dates, partners, captions, and permissions are reviewed.',
                'nextStep' => 'Ask the communications team to verify whether a new advisory, image set, or partner attribution exists.',
                'staleNote' => 'Do not display as a current assistance schedule. Mark as stale until reviewed by the content owner.',
                'owner' => 'Communications Lead and Chief of Staff approval',
                'mediaRightsNote' => 'Do not reuse private news images without permission; request approved District Office or city image files.',
                'reviewDueAt' => now()->subDay(),
            ],
        ];
    }

    private function attachGovernance(NewsRecord $record, User $owner, User $approver): void
    {
        $record->sourceRecords()->updateOrCreate(
            ['title' => $record->source_label],
            [
                'url' => $record->source_url,
                'source_type' => SourceType::PrimaryOfficial,
                'source_tier' => 'tier_1',
                'description' => 'Backend-owned news source record for public verification and review.',
                'content_owner_id' => $owner->id,
                'verified_by_id' => $approver->id,
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'last_reviewed_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'next_review_at' => $record->review_due_at,
                'public_status' => 'published',
                'metadata' => ['phase_7_news_slice' => true],
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
                'review_notes' => 'Approved news and media content moved into backend-owned public records.',
                'is_fast_track' => false,
                'metadata' => ['phase_7_news_slice' => true],
            ],
        );
    }
}
