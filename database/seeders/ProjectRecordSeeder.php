<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Enums\SourceType;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectRecordSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'projects@example.com'],
            [
                'name' => 'Project Content Owner',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ProjectEditor,
                'email_verified_at' => now(),
            ],
        );

        $approver = User::query()->firstOrCreate(
            ['email' => 'projects-approver@example.com'],
            [
                'name' => 'Project Reviewer',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        foreach ($this->records() as $index => $recordData) {
            $category = ProjectCategory::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['category'])],
                [
                    'name' => $recordData['category'],
                    'description' => 'Project category used for public transparency browsing and source review.',
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $status = ProjectStatus::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['statusLabel'])],
                [
                    'name' => $recordData['statusLabel'],
                    'value' => ProjectStatusValue::from($recordData['status']),
                    'label' => $recordData['statusLabel'],
                    'tone' => ProjectStatusTone::from($recordData['statusTone']),
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );

            $partner = ProjectPartner::query()->updateOrCreate(
                ['slug' => Str::slug($recordData['partner'])],
                [
                    'name' => $recordData['partner'],
                    'office_type' => $recordData['partnerOfficeType'],
                    'website_url' => $recordData['sourceUrl'],
                    'notes' => 'Partner attribution captured for public transparency records.',
                    'is_active' => true,
                ],
            );

            $project = Project::query()->updateOrCreate(
                ['slug' => $recordData['slug']],
                [
                    'project_category_id' => $category->id,
                    'project_status_id' => $status->id,
                    'primary_partner_id' => $partner->id,
                    'title' => $recordData['title'],
                    'barangay' => $recordData['barangay'],
                    'location' => $recordData['location'],
                    'responsible_office' => $recordData['responsibleOffice'],
                    'partner_label' => $recordData['partner'],
                    'summary' => $recordData['summary'],
                    'evidence_summary' => $recordData['evidenceSummary'],
                    'impact_note' => $recordData['impactNote'],
                    'image_guidance' => $recordData['imageGuidance'],
                    'source_label' => $recordData['sourceLabel'],
                    'source_url' => $recordData['sourceUrl'],
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'published_at' => now()->subDay(),
                    'review_due_at' => now()->addMonth(),
                    'sort_order' => ($index + 1) * 10,
                    'metadata' => ['phase_6_source' => true],
                ],
            );

            $project->evidences()->updateOrCreate(
                ['source_url' => $recordData['sourceUrl']],
                [
                    'title' => $recordData['title'].' evidence',
                    'source_label' => $recordData['sourceLabel'],
                    'caption' => $recordData['evidenceSummary'],
                    'rights_note' => $recordData['imageGuidance'],
                    'media_path' => null,
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'verified_by_id' => $approver->id,
                    'sort_order' => 10,
                    'metadata' => ['phase_7_projects_slice' => true],
                ],
            );

            $this->attachGovernance($project, $owner, $approver);
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
     *     barangay: string,
     *     location: string,
     *     responsibleOffice: string,
     *     partner: string,
     *     partnerOfficeType: string,
     *     summary: string,
     *     evidenceSummary: string,
     *     impactNote: string,
     *     imageGuidance: string,
     *     sourceLabel: string,
     *     sourceUrl: string
     * }>
     */
    private function records(): array
    {
        return [
            [
                'title' => 'Educational Assistance for Children of Solo Parents',
                'slug' => 'solo-parent-educational-assistance',
                'category' => 'Public Assistance and Education',
                'status' => ProjectStatusValue::Completed->value,
                'statusLabel' => 'Completed / documented',
                'statusTone' => ProjectStatusTone::Completed->value,
                'barangay' => 'Citywide',
                'location' => 'San Juan City, official activity record',
                'responsibleOffice' => 'San Juan City Government with documented District Office participation',
                'partner' => 'San Juan City Government',
                'partnerOfficeType' => 'city_government',
                'summary' => 'The official source describes a distribution of educational assistance for children of registered solo parents in San Juan.',
                'evidenceSummary' => 'Use the official San Juan activity page as the canonical source and request the approved original image before publication.',
                'impactNote' => 'Supports families and students while keeping the page clear that the activity is a city-government record with documented officials present.',
                'imageGuidance' => 'Use an approved local activity image with source, rights, caption, and alt text review; external CMS images are not used as live website assets.',
                'sourceLabel' => 'View official San Juan activity page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan',
            ],
            [
                'title' => 'Bagong Pilipinas eGovPH Serbisyo Hub',
                'slug' => 'egovph-serbisyo-hub',
                'category' => 'Digital Government',
                'status' => ProjectStatusValue::Ongoing->value,
                'statusLabel' => 'Active / ongoing service',
                'statusTone' => ProjectStatusTone::Ongoing->value,
                'barangay' => 'Citywide',
                'location' => 'Makabagong San Juan National Government Center',
                'responsibleOffice' => 'DICT, national government, San Juan City Government, and partner offices',
                'partner' => 'DICT / eGovPH and city partners',
                'partnerOfficeType' => 'national_agency',
                'summary' => 'The official source covers the inauguration and launch of the eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center.',
                'evidenceSummary' => 'The record presents this as a government partnership and points residents to the correct service channel.',
                'impactNote' => 'Helps residents understand where national digital government services are available in San Juan.',
                'imageGuidance' => 'Use only an approved local copy with source, rights, caption, and alt text review.',
                'sourceLabel' => 'View official San Juan news page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
            ],
            [
                'title' => '2025 National Women\'s Month Services',
                'slug' => 'national-womens-month-services-2025',
                'category' => 'Women and Family Services',
                'status' => ProjectStatusValue::Completed->value,
                'statusLabel' => 'Completed / archived',
                'statusTone' => ProjectStatusTone::Completed->value,
                'barangay' => 'Citywide',
                'location' => 'San Juan City, official activity record',
                'responsibleOffice' => 'City Government and partner departments with District Office coordination where documented',
                'partner' => 'City Health Department and partner offices',
                'partnerOfficeType' => 'city_government',
                'summary' => 'The official source describes a Women\'s Month activity with healthcare, employment, legal consultation, civil-registry, and wellness services.',
                'evidenceSummary' => 'The record identifies the city and partner offices as organizers when supported by the source.',
                'impactNote' => 'Highlights access to women, family, health, legal, and civil-registry services without overstating District Office ownership.',
                'imageGuidance' => 'Use activity images only after source, consent, caption, and privacy review.',
                'sourceLabel' => 'View official San Juan activity page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration',
            ],
            [
                'title' => 'Gawad Agraryo 2025 at Makabagong San Juan',
                'slug' => 'gawad-agraryo-2025',
                'category' => 'Government Partnerships',
                'status' => ProjectStatusValue::Completed->value,
                'statusLabel' => 'Completed / documented',
                'statusTone' => ProjectStatusTone::Completed->value,
                'barangay' => 'Citywide',
                'location' => 'Makabagong San Juan National Government Center',
                'responsibleOffice' => 'National government and city partners',
                'partner' => 'National government and San Juan City partners',
                'partnerOfficeType' => 'national_agency',
                'summary' => 'The official source documents Gawad Agraryo 2025 at the Makabagong San Juan National Government Center and identifies Congresswoman Bel Zamora among officials present.',
                'evidenceSummary' => 'Use this only for the related event, gallery, or government-partnership context with a factual caption.',
                'impactNote' => 'Shows San Juan as a venue for national-government partnership activity without implying sole District Office operation.',
                'imageGuidance' => 'Avoid cropping the stage title when it is necessary to identify the event.',
                'sourceLabel' => 'View official San Juan news page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025',
            ],
            [
                'title' => 'Balik Sigla, Bigay Saya Community Gift-Giving',
                'slug' => 'balik-sigla-bigay-saya',
                'category' => 'Community Outreach',
                'status' => ProjectStatusValue::Completed->value,
                'statusLabel' => 'Completed / documented',
                'statusTone' => ProjectStatusTone::Completed->value,
                'barangay' => 'Citywide',
                'location' => 'San Juan City, official activity record',
                'responsibleOffice' => 'San Juan City Government and documented partner officials',
                'partner' => 'San Juan City Government',
                'partnerOfficeType' => 'city_government',
                'summary' => 'The official San Juan page and PIA coverage document the Balik Sigla, Bigay Saya community gift-giving activity.',
                'evidenceSummary' => 'Present as a source-linked community-outreach record and avoid using private news images without permission.',
                'impactNote' => 'Provides residents with a public record of community support activity while preserving source attribution.',
                'imageGuidance' => 'Request approved original image files from the District Office or City Public Information Department.',
                'sourceLabel' => 'View official San Juan activity page',
                'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program',
            ],
        ];
    }

    private function attachGovernance(Project $project, User $owner, User $approver): void
    {
        $project->sourceRecords()->updateOrCreate(
            ['title' => $project->source_label],
            [
                'url' => $project->source_url,
                'source_type' => SourceType::PrimaryOfficial,
                'source_tier' => 'tier_1',
                'description' => 'Backend-owned project source record for public verification and review.',
                'content_owner_id' => $owner->id,
                'verified_by_id' => $approver->id,
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'last_reviewed_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'next_review_at' => now()->addMonth(),
                'public_status' => 'published',
                'metadata' => ['phase_7_projects_slice' => true],
            ],
        );

        $project->approvalRecords()->updateOrCreate(
            ['revision_number' => 1],
            [
                'status' => ContentApprovalStatus::Published,
                'submitted_by_id' => $owner->id,
                'reviewed_by_id' => $approver->id,
                'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subDay(),
                'approved_at' => now()->subDay(),
                'published_at' => now()->subDay(),
                'review_notes' => 'Approved project content moved into backend-owned public records.',
                'is_fast_track' => false,
                'metadata' => ['phase_7_projects_slice' => true],
            ],
        );
    }
}
