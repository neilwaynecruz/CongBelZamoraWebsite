<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Enums\SourceType;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ServiceGuide;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramAndServiceGuideSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'programs@example.com'],
            [
                'name' => 'Programs Content Owner',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ProgramOfficer,
                'email_verified_at' => now(),
            ],
        );

        $approver = User::query()->firstOrCreate(
            ['email' => 'approver@example.com'],
            [
                'name' => 'Content Reviewer',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        $education = ProgramCategory::query()->updateOrCreate(
            ['slug' => 'education'],
            [
                'name' => 'Education',
                'description' => 'Education assistance and youth-oriented program guidance.',
                'sort_order' => 10,
                'is_active' => true,
            ],
        );

        ProgramCategory::query()->updateOrCreate(
            ['slug' => 'solo-parents'],
            [
                'name' => 'Solo Parents',
                'description' => 'Routing guidance for solo-parent and family-support programs.',
                'sort_order' => 20,
                'is_active' => true,
            ],
        );

        ProgramCategory::query()->updateOrCreate(
            ['slug' => 'digital-services'],
            [
                'name' => 'Digital Services',
                'description' => 'Digital-government service routing for residents.',
                'sort_order' => 30,
                'is_active' => true,
            ],
        );

        ProgramCategory::query()->updateOrCreate(
            ['slug' => 'health'],
            [
                'name' => 'Health',
                'description' => 'Health and public-welfare routing guidance.',
                'sort_order' => 40,
                'is_active' => true,
            ],
        );

        $tulongDunong = Program::query()->updateOrCreate(
            ['slug' => 'ched-unifast-tulong-dunong'],
            [
                'program_category_id' => $education->id,
                'title' => 'CHED UniFAST Tulong Dunong',
                'href' => '/programs/education/tulong-dunong',
                'sector' => 'Education',
                'status' => ProgramStatus::Open,
                'summary' => 'District Office triage and orientation for eligible San Juan college students under CHED and UniFAST rules.',
                'description' => 'Financial assistance guidance for qualified college or incoming college students, subject to CHED UniFAST rules and the current official District Office announcement.',
                'responsible_office_type' => ResponsibleOfficeType::JointInitiative,
                'responsible_office_label' => 'District Office and CHED / UniFAST partnership',
                'partner_agency' => 'CHED / UniFAST',
                'beneficiary_group' => 'San Juan college and incoming college students',
                'application_period' => 'Current round must be reconfirmed with the District Office',
                'benefit' => 'Historical reference: ₱7,500 per qualified scholar for the cited round. The current approved benefit must be reconfirmed for every new application cycle.',
                'source_label' => 'CHED UniFAST information',
                'source_url' => 'https://unifast.gov.ph/',
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'content_owner_id' => $owner->id,
                'approved_by_id' => $approver->id,
                'published_at' => now()->subDay(),
                'review_due_at' => now()->addMonth(),
                'is_featured' => true,
                'sort_order' => 10,
                'metadata' => [
                    'detail_title' => 'CHED UniFAST Tulong Dunong Program',
                    'eyebrow' => 'Education assistance',
                    'partner_agencies' => [
                        'CHED / UniFAST',
                        'District Office of Congresswoman Atty. Bel Zamora',
                        'Approved implementing partners named in the current announcement',
                    ],
                    'office_role' => 'Triage, orientation, and public guidance. Final eligibility, slots, and approval follow official CHED / UniFAST and District Office rules.',
                    'eligibility' => [
                        'Filipino citizen and San Juan City resident, when required by the current official announcement.',
                        'Currently enrolled or incoming college student in a CHED-recognized higher education institution.',
                        'Meets academic, family-income, and other UniFAST eligibility rules for the current cycle.',
                        'Not disqualified by overlapping scholarship or assistance rules stated in the current announcement.',
                    ],
                    'process_steps' => [
                        'Review the current official announcement, status, deadline, and eligibility.',
                        'Prepare only the documents required for the current cycle.',
                        'Submit through the approved District Office or CHED / UniFAST channel.',
                        'Keep any confirmation or reference number from the official channel.',
                        'Wait for official validation and announcements; submission does not guarantee approval.',
                    ],
                    'reminders' => [
                        'Limited slots may apply and must be stated in the current announcement.',
                        'A first-come, first-served rule should only be shown when officially stated.',
                        'Do not send personal documents to unofficial pages, personal accounts, or comment threads.',
                        'Check publication and last-updated dates before relying on program details.',
                    ],
                    'privacy_notes' => [
                        'The MVP inquiry form must not collect ID scans, income records, student records, or sensitive uploads.',
                        'Any future document-upload portal requires separate privacy, security, retention, and access review.',
                        'Use official channels only when submitting required documents.',
                    ],
                    'contact_href' => '/contact#inquiry-form',
                    'latest_announcement_href' => '/news',
                ],
            ],
        );

        $tulongDunong->requirements()->delete();
        foreach ([
            ['Identity and residency', 'Valid identification, if required by the current official announcement.', 'Do not send ID scans through the basic inquiry form.', 10],
            ['Identity and residency', 'Proof of San Juan residency or barangay certificate, when required.', 'Do not send ID scans through the basic inquiry form.', 20],
            ['School and eligibility', 'Enrollment form or certificate of registration.', 'Document lists may change per academic year.', 30],
            ['School and eligibility', 'Academic record or certificate required by the current cycle.', 'Document lists may change per academic year.', 40],
            ['School and eligibility', 'Income-related document or certificate of indigency only when officially requested.', 'Document lists may change per academic year.', 50],
            ['School and eligibility', 'Official application form, when released.', 'Document lists may change per academic year.', 60],
        ] as [$groupTitle, $itemText, $note, $sortOrder]) {
            $tulongDunong->requirements()->create([
                'group_title' => $groupTitle,
                'item_text' => $itemText,
                'note' => $note,
                'sort_order' => $sortOrder,
            ]);
        }

        $tulongDunong->updates()->updateOrCreate(
            ['title' => 'Tulong Dunong orientation schedule for eligible students'],
            [
                'summary' => 'Verified advisory content directs eligible San Juan students to current Tulong Dunong requirements, orientation guidance, and official application channels.',
                'status' => ProgramStatus::Open,
                'source_label' => 'CHED UniFAST information',
                'source_url' => 'https://unifast.gov.ph/',
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'content_owner_id' => $owner->id,
                'published_at' => now()->subDay(),
                'metadata' => ['phase_6_source' => true],
            ],
        );

        $this->attachGovernance(
            model: $tulongDunong,
            title: 'CHED UniFAST information',
            url: 'https://unifast.gov.ph/',
            owner: $owner,
            approver: $approver,
        );

        foreach ($this->programCards() as $programData) {
            if ($programData['slug'] === 'ched-unifast-tulong-dunong') {
                continue;
            }

            $program = Program::query()->updateOrCreate(
                ['slug' => $programData['slug']],
                array_merge($programData['attributes'], [
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'published_at' => now()->subDay(),
                    'review_due_at' => now()->addMonth(),
                ]),
            );

            $this->attachGovernance(
                model: $program,
                title: $program->source_label ?? $program->title,
                url: $program->source_url,
                owner: $owner,
                approver: $approver,
            );
        }

        foreach ($this->serviceGuides() as $guideData) {
            $guide = ServiceGuide::query()->updateOrCreate(
                ['slug' => $guideData['slug']],
                array_merge($guideData['attributes'], [
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'published_at' => now()->subDay(),
                    'review_due_at' => now()->addMonth(),
                ]),
            );

            $guide->steps()->delete();

            foreach ($guideData['prepareItems'] as $index => $item) {
                $guide->steps()->create([
                    'section' => 'prepare',
                    'item_text' => $item,
                    'sort_order' => ($index + 1) * 10,
                ]);
            }

            foreach ($guideData['doNotSubmitItems'] as $index => $item) {
                $guide->steps()->create([
                    'section' => 'do_not_submit',
                    'item_text' => $item,
                    'sort_order' => ($index + 1) * 10,
                ]);
            }

            $this->attachGovernance(
                model: $guide,
                title: $guide->label,
                url: $guide->contact_href,
                owner: $owner,
                approver: $approver,
            );
        }
    }

    /**
     * @return array<int, array{slug: string, attributes: array<string, mixed>}>
     */
    private function programCards(): array
    {
        return [
            [
                'slug' => 'ched-unifast-tulong-dunong',
                'attributes' => [],
            ],
            [
                'slug' => 'solo-parent-educational-assistance',
                'attributes' => [
                    'title' => 'Solo Parent Educational Assistance',
                    'href' => '/programs/gabay',
                    'sector' => 'Solo Parents',
                    'status' => ProgramStatus::Open,
                    'summary' => 'Routing guidance for education-support programs connected to registered San Juan solo parents.',
                    'responsible_office_type' => ResponsibleOfficeType::JointInitiative,
                    'responsible_office_label' => 'San Juan City Government with District Office coordination',
                    'partner_agency' => 'San Juan City Government',
                    'beneficiary_group' => 'Children of registered San Juan solo parents',
                    'application_period' => 'Follow the current city government advisory',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 20,
                    'metadata' => ['phase_6_source' => true],
                ],
            ],
            [
                'slug' => 'egovph-serbisyo-hub',
                'attributes' => [
                    'title' => 'eGovPH Serbisyo Hub',
                    'href' => '/programs/gabay',
                    'sector' => 'Digital Services',
                    'status' => ProgramStatus::Ongoing,
                    'summary' => 'Guidance for residents using national digital government services hosted in Makabagong San Juan.',
                    'responsible_office_type' => ResponsibleOfficeType::JointInitiative,
                    'responsible_office_label' => 'DICT, national government, and city partners',
                    'partner_agency' => 'DICT / eGovPH',
                    'beneficiary_group' => 'San Juan residents using national digital services',
                    'application_period' => 'Ongoing service guidance',
                    'source_label' => 'View official San Juan news page',
                    'source_url' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 30,
                    'metadata' => ['phase_6_source' => true],
                ],
            ],
            [
                'slug' => 'medical-public-welfare-routing',
                'attributes' => [
                    'title' => 'Medical and Public Welfare Routing',
                    'href' => '/programs/gabay',
                    'sector' => 'Health',
                    'status' => ProgramStatus::ForAnnouncement,
                    'summary' => 'Plain-language routing for hospital, public-welfare, and social-service concerns that may belong to city or national agencies.',
                    'responsible_office_type' => ResponsibleOfficeType::CityGovernment,
                    'responsible_office_label' => 'San Juan City Government or national agency, depending on concern',
                    'partner_agency' => 'City Health Office / DSWD / DOH as applicable',
                    'beneficiary_group' => 'Residents seeking medical or welfare guidance',
                    'application_period' => 'Use Gabay to identify the correct office first',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => false,
                    'sort_order' => 40,
                    'metadata' => ['phase_6_source' => true],
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{slug: string, attributes: array<string, mixed>, prepareItems: array<int, string>, doNotSubmitItems: array<int, string>}>
     */
    private function serviceGuides(): array
    {
        return [
            [
                'slug' => 'education-assistance',
                'attributes' => [
                    'title' => 'Education assistance / Tulong Dunong',
                    'label' => 'Education assistance / Tulong Dunong',
                    'concern' => 'Scholarship or education-assistance inquiry',
                    'responsible_office_type' => ResponsibleOfficeType::JointInitiative,
                    'responsible_office_label' => 'District Office and CHED / UniFAST',
                    'district_office_role' => 'The District Office can help explain the current announcement, triage questions, and guide eligible students to the approved channel.',
                    'can_district_office_directly_handle' => true,
                    'explanation' => 'Tulong Dunong follows CHED and UniFAST rules. The District Office may help with orientation and routing, but final eligibility and approval depend on the current official program rules.',
                    'next_step' => 'Read the Tulong Dunong page first, then contact the District Office only through official channels if details remain unclear.',
                    'contact_label' => 'Read Tulong Dunong details',
                    'contact_href' => '/programs/education/tulong-dunong',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 10,
                    'metadata' => ['phase_6_source' => true],
                ],
                'prepareItems' => [
                    'Current school enrollment or registration details',
                    'Proof of San Juan residency when required by the current announcement',
                    'Valid contact information for official follow-up',
                    'Questions about eligibility, deadline, and accepted documents',
                ],
                'doNotSubmitItems' => [
                    'Government ID scans through the basic inquiry form',
                    'Income records, certificates, or student documents through unofficial accounts',
                    'Medical, financial, or family-sensitive records unless an approved secure process exists',
                ],
            ],
            [
                'slug' => 'medical-public-welfare',
                'attributes' => [
                    'title' => 'Medical or public-welfare concern',
                    'label' => 'Medical or public-welfare concern',
                    'concern' => 'Medical, hospital, or public-welfare routing',
                    'responsible_office_type' => ResponsibleOfficeType::CityGovernment,
                    'responsible_office_label' => 'San Juan City Government / City Health Office',
                    'district_office_role' => 'The District Office may provide routing information, but medical and welfare program operations usually belong to city or national agencies.',
                    'can_district_office_directly_handle' => false,
                    'explanation' => 'General medical support, hospital routing, and city social-service concerns should start with the proper city or national office so residents receive correct requirements and official processing steps.',
                    'next_step' => 'Use the official San Juan City Government or City Health Office channel for requirements and processing details.',
                    'contact_label' => 'Visit San Juan City portal',
                    'contact_href' => 'https://www.sanjuancity.gov.ph/',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 20,
                    'metadata' => ['phase_6_source' => true],
                ],
                'prepareItems' => [
                    'Name of the hospital, clinic, or public office involved',
                    'General description of the concern without sensitive medical details',
                    'Preferred official contact channel',
                ],
                'doNotSubmitItems' => [
                    'Medical certificates or prescriptions',
                    'Hospital bills with personal diagnosis details',
                    'Patient IDs, PhilHealth records, or other sensitive documents',
                ],
            ],
            [
                'slug' => 'local-permit-barangay',
                'attributes' => [
                    'title' => 'Local permits, roads, or barangay issue',
                    'label' => 'Local permits, roads, or barangay issue',
                    'concern' => 'Business permit, local road, zoning, or barangay service',
                    'responsible_office_type' => ResponsibleOfficeType::CityGovernment,
                    'responsible_office_label' => 'San Juan City Government or barangay office',
                    'district_office_role' => 'The District Office can clarify that these concerns are local-government responsibilities and point residents to the proper city or barangay channel.',
                    'can_district_office_directly_handle' => false,
                    'explanation' => 'City permits, barangay certificates, zoning, road maintenance, and local ordinance enforcement are handled by local government offices rather than the congressional District Office.',
                    'next_step' => 'Contact the relevant barangay hall or city department and keep any reference number they provide.',
                    'contact_label' => 'Visit San Juan City portal',
                    'contact_href' => 'https://www.sanjuancity.gov.ph/',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 30,
                    'metadata' => ['phase_6_source' => true],
                ],
                'prepareItems' => [
                    'Barangay or street location',
                    'Type of permit, certificate, or local service needed',
                    'Photos or evidence only when the receiving local office asks through an official channel',
                ],
                'doNotSubmitItems' => [
                    'Business registration files through the District Office inquiry form',
                    'Personal IDs or property documents through unofficial pages',
                    'Sensitive complaint details beyond what is needed for routing',
                ],
            ],
            [
                'slug' => 'legislative-feedback',
                'attributes' => [
                    'title' => 'Legislative concern or feedback',
                    'label' => 'Legislative concern or feedback',
                    'concern' => 'Bill, resolution, policy, or legislative feedback',
                    'responsible_office_type' => ResponsibleOfficeType::DistrictOffice,
                    'responsible_office_label' => 'District Office / Legislative staff',
                    'district_office_role' => 'The District Office can receive general feedback, route policy questions to legislative staff, and point residents to official House records.',
                    'can_district_office_directly_handle' => true,
                    'explanation' => 'Legislative feedback is within the congressional role, but staff still need clear, non-sensitive messages and official source references where possible.',
                    'next_step' => 'Use the public inquiry path or review the legislative tracker once the full Phase 6 template is implemented.',
                    'contact_label' => 'Send a basic inquiry',
                    'contact_href' => '/contact#inquiry-form',
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'is_featured' => true,
                    'sort_order' => 40,
                    'metadata' => ['phase_6_source' => true],
                ],
                'prepareItems' => [
                    'Bill number or policy topic, if known',
                    'Short plain-language comment or question',
                    'Preferred contact channel for a response',
                ],
                'doNotSubmitItems' => [
                    'Private case files unrelated to public policy',
                    'Sensitive personal records as evidence',
                    'Legal-service requests that require formal counsel',
                ],
            ],
        ];
    }

    private function attachGovernance(
        Program|ServiceGuide $model,
        string $title,
        ?string $url,
        User $owner,
        User $approver,
    ): void {
        $model->sourceRecords()->updateOrCreate(
            ['title' => $title],
            [
                'url' => $url,
                'source_type' => SourceType::PrimaryOfficial,
                'source_tier' => 'tier_1',
                'description' => 'Phase 7 backend-owned source record migrated from verified Phase 6 public content.',
                'content_owner_id' => $owner->id,
                'verified_by_id' => $approver->id,
                'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'last_reviewed_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                'next_review_at' => now()->addMonth(),
                'public_status' => 'published',
                'metadata' => ['phase_7_programs_slice' => true],
            ],
        );

        $model->approvalRecords()->updateOrCreate(
            ['revision_number' => 1],
            [
                'status' => ContentApprovalStatus::Published,
                'submitted_by_id' => $owner->id,
                'reviewed_by_id' => $approver->id,
                'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subDay(),
                'approved_at' => now()->subDay(),
                'published_at' => now()->subDay(),
                'review_notes' => 'Approved Phase 6 public content moved into backend-owned Phase 7 records.',
                'is_fast_track' => false,
                'metadata' => ['phase_7_programs_slice' => true],
            ],
        );
    }
}
