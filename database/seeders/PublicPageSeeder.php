<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\SourceType;
use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicPageSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->firstOrCreate(
            ['email' => 'static-content@example.com'],
            [
                'name' => 'Static Content Owner',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ContentAdministrator,
                'email_verified_at' => now(),
            ],
        );

        $approver = User::query()->firstOrCreate(
            ['email' => 'static-content-approver@example.com'],
            [
                'name' => 'Static Content Reviewer',
                'password' => bcrypt('password'),
                'admin_role' => AdminRole::ReviewerApprover,
                'email_verified_at' => now(),
            ],
        );

        foreach ($this->pages() as $pageData) {
            $page = PublicPage::query()->updateOrCreate(
                ['slug' => $pageData['slug']],
                [
                    'title' => $pageData['title'],
                    'component' => $pageData['component'],
                    'props' => $pageData['props'],
                    'owner_label' => $pageData['owner_label'],
                    'source_label' => $pageData['source_label'],
                    'source_url' => $pageData['source_url'],
                    'verified_at' => now()->setDate(2026, 7, 12)->startOfDay(),
                    'review_due_at' => now()->addMonth(),
                    'content_owner_id' => $owner->id,
                    'approved_by_id' => $approver->id,
                    'published_at' => now()->subDay(),
                    'metadata' => ['phase_7_static_page_slice' => true],
                ],
            );

            $this->attachGovernance($page, $owner, $approver);
        }
    }

    /**
     * @return array<int, array{
     *     slug: string,
     *     title: string,
     *     component: string,
     *     owner_label: string,
     *     source_label: string,
     *     source_url: string|null,
     *     props: array<string, mixed>
     * }>
     */
    private function pages(): array
    {
        return [
            [
                'slug' => 'home',
                'title' => 'Home',
                'component' => 'Public/Home',
                'owner_label' => 'Content Administrator',
                'source_label' => 'Approved public home content',
                'source_url' => null,
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home'],
                    ],
                    'quickActions' => [
                        [
                            'title' => 'Check program status',
                            'eyebrow' => 'Programs',
                            'href' => '/programs',
                            'summary' => 'Find open programs, eligibility, requirements, deadlines, and verified contact channels.',
                        ],
                        [
                            'title' => 'Know where to ask',
                            'eyebrow' => 'Gabay San Juaneño',
                            'href' => '/programs/gabay',
                            'summary' => 'Route concerns to the District Office, city government, barangay, or national agency.',
                        ],
                        [
                            'title' => 'Contact the District Office',
                            'eyebrow' => 'Assistance',
                            'href' => '/contact#inquiry-form',
                            'summary' => 'Use the privacy-safe inquiry path for basic questions without sending sensitive documents.',
                        ],
                    ],
                    'featuredPrograms' => [
                        [
                            'title' => 'CHED UniFAST Tulong Dunong',
                            'href' => '/programs/education/tulong-dunong',
                            'sector' => 'Education',
                            'status' => 'open',
                            'statusLabel' => 'Bukas / Open',
                            'summary' => 'District Office triage and orientation for eligible San Juan college students under CHED and UniFAST rules.',
                            'verifiedAt' => 'July 12, 2026',
                        ],
                        [
                            'title' => 'Solo Parent Educational Assistance',
                            'href' => '/programs',
                            'sector' => 'Solo Parents',
                            'status' => 'open',
                            'statusLabel' => 'Bukas / Open',
                            'summary' => 'Education support guidance for children of registered San Juan solo parents, coordinated with city partners.',
                            'verifiedAt' => 'July 12, 2026',
                        ],
                        [
                            'title' => 'eGovPH Serbisyo Hub',
                            'href' => '/programs/gabay',
                            'sector' => 'Digital Services',
                            'status' => 'ongoing',
                            'statusLabel' => 'Ongoing',
                            'summary' => 'Guidance for residents using national digital government services hosted in Makabagong San Juan.',
                            'verifiedAt' => 'July 12, 2026',
                        ],
                    ],
                    'legislativeSpotlight' => [
                        'billNumber' => 'HB00123',
                        'title' => 'Solo Parent Welfare Extension Act',
                        'role' => 'Principal Author',
                        'status' => 'Passed House; Sent to Senate',
                        'summary' => 'A plain-language legislative summary explains the measure in practical terms and links residents to the official House record for full details.',
                        'sourceLabel' => 'Track on House registry',
                        'sourceUrl' => 'https://www.congress.gov.ph/',
                    ],
                    'latestNews' => [
                        [
                            'title' => 'Tulong Dunong orientation schedule for eligible students',
                            'href' => '/news',
                            'date' => 'July 12, 2026',
                            'category' => 'Education',
                            'summary' => 'The advisory directs eligible students to current requirements, deadlines, and official application channels.',
                        ],
                        [
                            'title' => 'Gabay San Juaneño service-routing directory prepared',
                            'href' => '/programs/gabay',
                            'date' => 'July 12, 2026',
                            'category' => 'Public Service',
                            'summary' => 'The directory separates District Office, city government, barangay, and national-agency responsibilities.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'About',
                'component' => 'Public/About',
                'owner_label' => 'Content Administrator with Chief of Staff review',
                'source_label' => 'House of Representatives member record K099',
                'source_url' => 'https://www.congress.gov.ph/house-members/view/K099',
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'About'],
                    ],
                    'profile' => [
                        'publicName' => 'Atty. Bel Zamora',
                        'fullName' => 'Ysabel Maria Jorge Zamora',
                        'title' => 'Representative, Lone District of San Juan City',
                        'district' => 'Lone District of San Juan City',
                        'summary' => 'A verified public profile for Atty. Congresswoman Bel Zamora, written in clear public-service language and limited to confirmed official records.',
                        'verifiedAt' => 'July 12, 2026',
                    ],
                    'facts' => [
                        [
                            'label' => 'Public service name',
                            'value' => 'Atty. Bel Zamora',
                            'sourceLabel' => 'Official website and public profile records',
                            'sourceUrl' => 'https://belzamora.com/',
                        ],
                        [
                            'label' => 'Full legal name',
                            'value' => 'Ysabel Maria Jorge Zamora',
                            'sourceLabel' => 'House of Representatives member record K099',
                            'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/K099',
                        ],
                        [
                            'label' => 'Current office',
                            'value' => 'Representative / Congresswoman, Lone District of San Juan City',
                            'sourceLabel' => 'House of Representatives member record K099',
                            'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/K099',
                        ],
                        [
                            'label' => 'Terms of office',
                            'value' => '19th Congress (2022-2025), 20th Congress (2025-2028)',
                            'sourceLabel' => 'House of Representatives member database',
                            'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/K099',
                        ],
                        [
                            'label' => 'Bar admission',
                            'value' => 'Roll No. 59288; admitted May 3, 2011',
                            'sourceLabel' => 'Supreme Court law list / IBP record cited in the approved source file',
                        ],
                        [
                            'label' => 'Education',
                            'value' => 'Juris Doctor, Ateneo Law School; AB Psychology, Ateneo de Manila University',
                            'sourceLabel' => 'Supreme Court roll records / official biography source file',
                        ],
                    ],
                    'timeline' => [
                        [
                            'period' => '2025-present',
                            'title' => '20th Congress Representative',
                            'description' => 'Serving as Representative of the Lone District of San Juan City, with verified committee memberships recorded in the House profile.',
                            'sourceLabel' => 'House of Representatives member and committee records',
                        ],
                        [
                            'period' => '2022-2025',
                            'title' => '19th Congress Representative',
                            'description' => 'Served as Representative of the Lone District of San Juan City during the 19th Congress.',
                            'sourceLabel' => 'House of Representatives member database',
                        ],
                        [
                            'period' => 'May 3, 2011',
                            'title' => 'Philippine Bar admission',
                            'description' => 'Admitted to the Philippine Bar under Roll No. 59288, as cited in the approved biography verification file.',
                            'sourceLabel' => 'Supreme Court law list / IBP record cited in the approved source file',
                        ],
                    ],
                    'committeeMemberships' => [
                        'Committee on Accounts - Member for the Majority',
                        'Committee on Appropriations - Member for the Majority',
                        'Committee on Health - Member for the Majority',
                        'Committee on Rules - Member for the Majority',
                        'Committee on Women and Gender Equality - Member for the Majority',
                    ],
                    'valuePillars' => [
                        [
                            'title' => 'Edukasyon at Kabataan',
                            'summary' => 'Education assistance and youth pathways are presented with current status, source links, and no approval promises.',
                        ],
                        [
                            'title' => 'Kababaihan at Pamilya',
                            'summary' => 'Women, family, and solo-parent concerns are explained with dignity, privacy safeguards, and responsible-office boundaries.',
                        ],
                        [
                            'title' => 'Kalusugan at Kapakanan',
                            'summary' => 'Health and public-welfare concerns are routed carefully to the District Office, city government, or national agencies as appropriate.',
                        ],
                    ],
                    'responsibilityBoundaries' => [
                        [
                            'title' => 'Congressional and legislative work',
                            'office' => 'District Office / Legislative staff',
                            'summary' => 'Bills, resolutions, committee work, and policy feedback belong to the congressional role and are checked against official House records.',
                            'nextStepLabel' => 'View legislative tracker',
                            'nextStepHref' => '/legislation',
                        ],
                        [
                            'title' => 'Programs with national or city partners',
                            'office' => 'District Office with partner agency',
                            'summary' => 'The District Office may provide orientation and routing, but eligibility, slots, and final approval follow the current official program rules.',
                            'nextStepLabel' => 'Check program status',
                            'nextStepHref' => '/programs',
                        ],
                        [
                            'title' => 'City, barangay, and national-agency services',
                            'office' => 'Proper city, barangay, or national office',
                            'summary' => 'Local permits, barangay certificates, city services, and agency-managed benefits start with the correct responsible office.',
                            'nextStepLabel' => 'Use Gabay San Juaneño',
                            'nextStepHref' => '/programs/gabay',
                        ],
                    ],
                    'sourceLinks' => [
                        [
                            'label' => 'House member record',
                            'href' => 'https://www.congress.gov.ph/house-members/view/K099',
                            'note' => 'Primary source for official office, district, and member profile details.',
                        ],
                        [
                            'label' => 'House committee memberships',
                            'href' => 'https://www.congress.gov.ph/house-members/view/K099',
                            'note' => 'Primary source for committee roles and membership updates.',
                        ],
                        [
                            'label' => 'Official website archive',
                            'href' => 'https://belzamora.com/',
                            'note' => 'Reference for existing public identity and contact information, subject to District Office confirmation.',
                        ],
                        [
                            'label' => 'San Juan City Government directory',
                            'href' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
                            'note' => 'Official source for District Office location verification.',
                        ],
                    ],
                    'verifiedAt' => 'July 12, 2026',
                ],
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact',
                'component' => 'Public/Contact',
                'owner_label' => 'Content Administrator with District Office confirmation',
                'source_label' => 'San Juan City Government directory',
                'source_url' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Contact'],
                    ],
                    'office' => [
                        'name' => 'District Office of Atty. Congresswoman Bel Zamora',
                        'address' => 'Upper Ground Floor, Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City',
                        'locationNote' => 'The official San Juan City Government directory lists the District Office of the Congresswoman at the Upper Ground Floor with extensions 264 to 265.',
                        'officeHours' => 'Office hours and visitor procedures are displayed after District Office confirmation.',
                        'verifiedAt' => 'July 12, 2026',
                        'sourceLabel' => 'View official San Juan City Government directory',
                        'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
                    ],
                    'channels' => [
                        [
                            'label' => 'Official email',
                            'value' => 'info@belzamora.com',
                            'href' => 'mailto:info@belzamora.com',
                            'note' => 'Use for non-urgent public inquiries after District Office activation and monitoring are confirmed.',
                        ],
                        [
                            'label' => 'Published legacy email',
                            'value' => 'atty.belzamora@gmail.com',
                            'href' => 'mailto:atty.belzamora@gmail.com',
                            'note' => 'Previously published public channel; retained while the District Office confirms the long-term email process.',
                        ],
                        [
                            'label' => 'Office directory extensions',
                            'value' => 'Extensions 264 to 265',
                            'note' => 'Directory listing only; direct phone number and visitor process depend on District Office confirmation.',
                        ],
                    ],
                    'privacyHighlights' => [
                        'The form asks only for name, one contact channel, inquiry category, and a short message.',
                        'Do not include government ID numbers, birth certificates, income records, grades, medical records, prescriptions, diagnosis, or photos of documents.',
                        'Document uploads are not accepted through this public inquiry form. Any future upload process goes through separate privacy, legal, security, retention, and access review.',
                    ],
                    'responseExpectations' => [
                        'This form is for non-emergency routing and basic questions only.',
                        'Submission does not guarantee program eligibility, assistance approval, appointment confirmation, or case acceptance.',
                        'The District Office may route the concern to the proper city, barangay, national-agency, or partner-office channel.',
                    ],
                    'verifiedAt' => 'July 12, 2026',
                ],
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Notice',
                'component' => 'Public/Privacy',
                'owner_label' => 'Privacy lead with Content Administrator',
                'source_label' => 'Approved privacy requirements',
                'source_url' => null,
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Privacy Notice'],
                    ],
                    'effectiveDate' => 'July 15, 2026',
                    'sections' => [
                        [
                            'title' => 'Data minimization',
                            'body' => 'The public inquiry form asks only for a name, contact preference, contact detail, inquiry category, message, and privacy acknowledgement so staff can route and respond to the concern.',
                            'items' => [
                                'No government ID numbers are requested.',
                                'No medical, income, school, or civil-registry documents are requested.',
                                'No file uploads are accepted in the public inquiry form.',
                            ],
                        ],
                        [
                            'title' => 'Purpose and use',
                            'body' => 'Information submitted through the inquiry form is used only to route citizen concerns to authorized District Office staff.',
                            'items' => [
                                'Program questions may be routed to the responsible program officer.',
                                'City, barangay, or national-agency concerns may be redirected to the correct office.',
                                'Legislative feedback may be reviewed by authorized legislative staff.',
                            ],
                        ],
                        [
                            'title' => 'Retention and storage',
                            'body' => 'Inquiry records use encrypted storage, least-privilege staff access, audit logs, and the approved 180-day retention and anonymization policy.',
                        ],
                        [
                            'title' => 'Privacy rights',
                            'body' => 'Residents may ask how their inquiry information is handled and may request correction, deletion, or clarification through the privacy contact pathway.',
                        ],
                    ],
                    'privacyContact' => [
                        'label' => 'Contact the District Office',
                        'href' => '/contact#inquiry-form',
                        'note' => 'For privacy questions, use the Contact page and choose General inquiry while the dedicated privacy mailbox is being confirmed.',
                    ],
                ],
            ],
            [
                'slug' => 'accessibility',
                'title' => 'Accessibility',
                'component' => 'Public/Accessibility',
                'owner_label' => 'Content Administrator',
                'source_label' => 'Approved accessibility requirements',
                'source_url' => null,
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Accessibility'],
                    ],
                    'reviewDate' => 'July 15, 2026',
                    'sections' => [
                        [
                            'title' => 'Target standard',
                            'body' => 'The public website targets WCAG 2.2 Level AA for priority citizen journeys, including program discovery, service routing, legislative lookup, and contact form use.',
                            'items' => [
                                'Semantic headings and landmarks are required.',
                                'Interactive controls show visible focus states.',
                                'Important status information does not rely on color alone.',
                            ],
                        ],
                        [
                            'title' => 'Ongoing accessibility review',
                            'body' => 'Automated checks, keyboard behavior, responsive layouts, and screen-reader-oriented reviews are part of the continuing launch and maintenance process.',
                        ],
                        [
                            'title' => 'Low-bandwidth and mobile support',
                            'body' => 'Pages avoid autoplay video and heavy feeds. Public-service content is delivered through SSR-friendly Inertia pages with mobile-first layouts.',
                        ],
                    ],
                    'supportContact' => [
                        'label' => 'Report an Access Barrier',
                        'href' => '/contact#inquiry-form',
                        'note' => 'If a page, form, or service guide is difficult to use with assistive technology, report the barrier through the Contact page.',
                    ],
                ],
            ],
            [
                'slug' => 'disclaimer',
                'title' => 'Disclaimer',
                'component' => 'Public/Disclaimer',
                'owner_label' => 'Content Administrator',
                'source_label' => 'Approved public-service disclaimer',
                'source_url' => null,
                'props' => [
                    'breadcrumbs' => [
                        ['title' => 'Home', 'href' => '/'],
                        ['title' => 'Disclaimer'],
                    ],
                    'reviewDate' => 'July 15, 2026',
                    'sections' => [
                        [
                            'title' => 'No approval promise',
                            'body' => 'Program pages, service guides, and inquiry responses explain next steps and responsible offices. They do not guarantee approval, slots, assistance, funding, or priority processing.',
                        ],
                        [
                            'title' => 'Responsible-office boundaries',
                            'body' => 'Some concerns belong to San Juan City Government, barangays, national agencies, schools, hospitals, or courts. The District Office may guide or route the concern without replacing the responsible office.',
                            'items' => [
                                'Emergency concerns use official emergency channels.',
                                'Legal, medical, and financial decisions come from qualified offices or professionals.',
                                'National-agency requirements may change without notice to the District Office.',
                            ],
                        ],
                        [
                            'title' => 'External source links',
                            'body' => 'The website links to official House, city, national-agency, and partner sources for verification. External websites have their own privacy, accessibility, and content policies.',
                        ],
                        [
                            'title' => 'Current information',
                            'body' => 'Dates, program status, requirements, contacts, and source links are reviewed on the cadence approved in the content governance plan.',
                        ],
                    ],
                    'contact' => [
                        'label' => 'Ask for Routing Help',
                        'href' => '/contact#inquiry-form',
                        'note' => 'Use the Contact page if you need help identifying the correct office for a concern.',
                    ],
                ],
            ],
        ];
    }

    private function attachGovernance(PublicPage $page, User $owner, User $approver): void
    {
        $page->sourceRecords()->updateOrCreate(
            ['title' => $page->source_label],
            [
                'url' => $page->source_url,
                'source_type' => SourceType::PrimaryOfficial,
                'source_tier' => 'tier_1',
                'description' => 'Backend-owned static page source record for public verification and review.',
                'content_owner_id' => $owner->id,
                'verified_by_id' => $approver->id,
                'verified_at' => $page->verified_at,
                'last_reviewed_at' => $page->verified_at,
                'next_review_at' => $page->review_due_at,
                'public_status' => 'published',
                'metadata' => ['phase_7_static_page_slice' => true],
            ],
        );

        $page->approvalRecords()->updateOrCreate(
            ['revision_number' => 1],
            [
                'status' => ContentApprovalStatus::Published,
                'submitted_by_id' => $owner->id,
                'reviewed_by_id' => $approver->id,
                'submitted_at' => now()->subDays(2),
                'reviewed_at' => now()->subDay(),
                'approved_at' => now()->subDay(),
                'published_at' => now()->subDay(),
                'review_notes' => 'Approved static public content moved into backend-owned review records.',
                'is_fast_track' => false,
                'metadata' => ['phase_7_static_page_slice' => true],
            ],
        );
    }
}
