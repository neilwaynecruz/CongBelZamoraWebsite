<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Public/Home', [
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
                'summary' => 'Use the DPA-safe inquiry path for basic questions without sending sensitive documents.',
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
            'summary' => 'A plain-language legislative summary will help citizens understand how the measure supports solo parents and families while linking to the official House record for full details.',
            'sourceLabel' => 'Track on House registry',
            'sourceUrl' => 'https://www.congress.gov.ph/',
        ],
        'latestNews' => [
            [
                'title' => 'Tulong Dunong orientation schedule for eligible students',
                'href' => '/news',
                'date' => 'July 12, 2026',
                'category' => 'Education',
                'summary' => 'Verified advisory content will direct students to current requirements, deadlines, and official application channels.',
            ],
            [
                'title' => 'Gabay San Juaneño service-routing directory prepared',
                'href' => '/programs/gabay',
                'date' => 'July 12, 2026',
                'category' => 'Public Service',
                'summary' => 'The directory separates District Office, city government, barangay, and national-agency responsibilities.',
            ],
        ],
    ]);
})->name('home');

Route::get('/programs', function () {
    $programs = [
        [
            'title' => 'CHED UniFAST Tulong Dunong',
            'href' => '/programs/education/tulong-dunong',
            'sector' => 'Education',
            'status' => 'open',
            'statusLabel' => 'Bukas / Open',
            'summary' => 'District Office triage and orientation for eligible San Juan college students under CHED and UniFAST rules.',
            'responsibleOfficeType' => 'joint_initiative',
            'responsibleOfficeLabel' => 'District Office and CHED / UniFAST partnership',
            'partnerAgency' => 'CHED / UniFAST',
            'beneficiaryGroup' => 'San Juan college and incoming college students',
            'applicationPeriod' => 'Current round must be reconfirmed with the District Office',
            'verifiedAt' => 'July 12, 2026',
            'sourceLabel' => 'CHED UniFAST information',
            'sourceUrl' => 'https://ched.gov.ph/unifast/',
        ],
        [
            'title' => 'Solo Parent Educational Assistance',
            'href' => '/programs/gabay',
            'sector' => 'Solo Parents',
            'status' => 'open',
            'statusLabel' => 'Bukas / Open',
            'summary' => 'Routing guidance for education-support programs connected to registered San Juan solo parents.',
            'responsibleOfficeType' => 'joint_initiative',
            'responsibleOfficeLabel' => 'San Juan City Government with District Office coordination',
            'partnerAgency' => 'San Juan City Government',
            'beneficiaryGroup' => 'Children of registered San Juan solo parents',
            'applicationPeriod' => 'Follow the current city government advisory',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => 'eGovPH Serbisyo Hub',
            'href' => '/programs/gabay',
            'sector' => 'Digital Services',
            'status' => 'ongoing',
            'statusLabel' => 'Ongoing',
            'summary' => 'Guidance for residents using national digital government services hosted in Makabagong San Juan.',
            'responsibleOfficeType' => 'joint_initiative',
            'responsibleOfficeLabel' => 'DICT, national government, and city partners',
            'partnerAgency' => 'DICT / eGovPH',
            'beneficiaryGroup' => 'San Juan residents using national digital services',
            'applicationPeriod' => 'Ongoing service guidance',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => 'Medical and Public Welfare Routing',
            'href' => '/programs/gabay',
            'sector' => 'Health',
            'status' => 'for_announcement',
            'statusLabel' => 'For Announcement',
            'summary' => 'Plain-language routing for hospital, public-welfare, and social-service concerns that may belong to city or national agencies.',
            'responsibleOfficeType' => 'city_government',
            'responsibleOfficeLabel' => 'San Juan City Government or national agency, depending on concern',
            'partnerAgency' => 'City Health Office / DSWD / DOH as applicable',
            'beneficiaryGroup' => 'Residents seeking medical or welfare guidance',
            'applicationPeriod' => 'Use Gabay to identify the correct office first',
            'verifiedAt' => 'July 12, 2026',
        ],
    ];

    return Inertia::render('Public/Programs/Index', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Programs'],
        ],
        'programs' => $programs,
        'sectors' => collect($programs)->pluck('sector')->unique()->values()->all(),
        'statuses' => collect($programs)->pluck('statusLabel')->unique()->values()->all(),
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.programs');

Route::get('/programs/gabay', function () {
    return Inertia::render('Public/Programs/Gabay', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Programs', 'href' => '/programs'],
            ['title' => 'Gabay San Juaneño'],
        ],
        'serviceOptions' => [
            [
                'id' => 'education-assistance',
                'label' => 'Education assistance / Tulong Dunong',
                'concern' => 'Scholarship or education-assistance inquiry',
                'responsibleOfficeType' => 'joint_initiative',
                'responsibleOfficeLabel' => 'District Office and CHED / UniFAST',
                'districtOfficeRole' => 'The District Office can help explain the current announcement, triage questions, and guide eligible students to the approved channel.',
                'canDistrictOfficeDirectlyHandle' => true,
                'explanation' => 'Tulong Dunong follows CHED and UniFAST rules. The District Office may help with orientation and routing, but final eligibility and approval depend on the current official program rules.',
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
                'nextStep' => 'Read the Tulong Dunong page first, then contact the District Office only through official channels if details remain unclear.',
                'contactLabel' => 'Read Tulong Dunong details',
                'contactHref' => '/programs/education/tulong-dunong',
            ],
            [
                'id' => 'medical-public-welfare',
                'label' => 'Medical or public-welfare concern',
                'concern' => 'Medical, hospital, or public-welfare routing',
                'responsibleOfficeType' => 'city_government',
                'responsibleOfficeLabel' => 'San Juan City Government / City Health Office',
                'districtOfficeRole' => 'The District Office may provide routing information, but medical and welfare program operations usually belong to city or national agencies.',
                'canDistrictOfficeDirectlyHandle' => false,
                'explanation' => 'General medical support, hospital routing, and city social-service concerns should start with the proper city or national office so residents receive correct requirements and official processing steps.',
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
                'nextStep' => 'Use the official San Juan City Government or City Health Office channel for requirements and processing details.',
                'contactLabel' => 'Visit San Juan City portal',
                'contactHref' => 'https://www.sanjuancity.gov.ph/',
            ],
            [
                'id' => 'local-permit-barangay',
                'label' => 'Local permits, roads, or barangay issue',
                'concern' => 'Business permit, local road, zoning, or barangay service',
                'responsibleOfficeType' => 'city_government',
                'responsibleOfficeLabel' => 'San Juan City Government or barangay office',
                'districtOfficeRole' => 'The District Office can clarify that these concerns are local-government responsibilities and point residents to the proper city or barangay channel.',
                'canDistrictOfficeDirectlyHandle' => false,
                'explanation' => 'City permits, barangay certificates, zoning, road maintenance, and local ordinance enforcement are handled by local government offices rather than the congressional District Office.',
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
                'nextStep' => 'Contact the relevant barangay hall or city department and keep any reference number they provide.',
                'contactLabel' => 'Visit San Juan City portal',
                'contactHref' => 'https://www.sanjuancity.gov.ph/',
            ],
            [
                'id' => 'legislative-feedback',
                'label' => 'Legislative concern or feedback',
                'concern' => 'Bill, resolution, policy, or legislative feedback',
                'responsibleOfficeType' => 'district_office',
                'responsibleOfficeLabel' => 'District Office / Legislative staff',
                'districtOfficeRole' => 'The District Office can receive general feedback, route policy questions to legislative staff, and point residents to official House records.',
                'canDistrictOfficeDirectlyHandle' => true,
                'explanation' => 'Legislative feedback is within the congressional role, but staff still need clear, non-sensitive messages and official source references where possible.',
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
                'nextStep' => 'Use the public inquiry path or review the legislative tracker once the full Phase 6 template is implemented.',
                'contactLabel' => 'Send a basic inquiry',
                'contactHref' => '/contact#inquiry-form',
            ],
        ],
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.programs.gabay');

Route::get('/programs/education/tulong-dunong', function () {
    return Inertia::render('Public/Programs/TulongDunong', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Programs', 'href' => '/programs'],
            ['title' => 'Education'],
            ['title' => 'Tulong Dunong'],
        ],
        'program' => [
            'title' => 'CHED UniFAST Tulong Dunong Program',
            'eyebrow' => 'Education assistance',
            'description' => 'Financial assistance guidance for qualified college or incoming college students, subject to CHED UniFAST rules and the current official District Office announcement.',
            'status' => 'open',
            'statusLabel' => 'Bukas / Open',
            'responsibleOfficeLabel' => 'District Office and CHED / UniFAST partnership',
            'partnerAgencies' => [
                'CHED / UniFAST',
                'District Office of Congresswoman Atty. Bel Zamora',
                'Approved implementing partners named in the current announcement',
            ],
            'benefit' => 'Historical reference: ₱7,500 per qualified scholar for the cited round. The current approved benefit must be reconfirmed for every new application cycle.',
            'applicationPeriod' => 'Current round must be reconfirmed with the District Office',
            'verifiedAt' => 'July 12, 2026',
            'sourceLabel' => 'CHED UniFAST information',
            'sourceUrl' => 'https://ched.gov.ph/unifast/',
            'officeRole' => 'Triage, orientation, and public guidance. Final eligibility, slots, and approval follow official CHED / UniFAST and District Office rules.',
            'eligibility' => [
                'Filipino citizen and San Juan City resident, when required by the current official announcement.',
                'Currently enrolled or incoming college student in a CHED-recognized higher education institution.',
                'Meets academic, family-income, and other UniFAST eligibility rules for the current cycle.',
                'Not disqualified by overlapping scholarship or assistance rules stated in the current announcement.',
            ],
            'requirementGroups' => [
                [
                    'title' => 'Identity and residency',
                    'items' => [
                        'Valid identification, if required by the current official announcement.',
                        'Proof of San Juan residency or barangay certificate, when required.',
                    ],
                    'note' => 'Do not send ID scans through the basic inquiry form.',
                ],
                [
                    'title' => 'School and eligibility',
                    'items' => [
                        'Enrollment form or certificate of registration.',
                        'Academic record or certificate required by the current cycle.',
                        'Income-related document or certificate of indigency only when officially requested.',
                        'Official application form, when released.',
                    ],
                    'note' => 'Document lists may change per academic year.',
                ],
            ],
            'processSteps' => [
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
            'privacyNotes' => [
                'The MVP inquiry form must not collect ID scans, income records, student records, or sensitive uploads.',
                'Any future document-upload portal requires separate privacy, security, retention, and access review.',
                'Use official channels only when submitting required documents.',
            ],
            'contactHref' => '/contact#inquiry-form',
            'latestAnnouncementHref' => '/news',
        ],
    ]);
})->name('public.programs.tulong-dunong');

Route::get('/legislation', function (Request $request) {
    $legislativeItems = [
        [
            'billNumber' => 'HB00123',
            'officialTitle' => 'Act promoting solo parent welfare',
            'publicTitle' => 'Solo Parent Welfare Extension Act',
            'congress' => '20th Congress',
            'role' => 'principal_author',
            'roleLabel' => 'Principal Author',
            'status' => 'Passed House; Sent to Senate',
            'statusTone' => 'passed',
            'topic' => 'Solo Parents and Family Support',
            'committee' => 'House Bill Tracker (K099)',
            'filedAt' => 'Source-pending date',
            'verifiedAt' => 'July 12, 2026',
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
            'role' => 'co_author',
            'roleLabel' => 'Co-Author',
            'status' => 'Pending Committee Review',
            'statusTone' => 'pending',
            'topic' => 'Education and Digital Access',
            'committee' => 'Pending committee review',
            'filedAt' => 'Source-pending date',
            'verifiedAt' => 'July 12, 2026',
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
            'role' => 'principal_author',
            'roleLabel' => 'Principal Author',
            'status' => 'Passed into Republic Act (RA 12345)',
            'statusTone' => 'enacted',
            'topic' => 'Heritage and Culture',
            'committee' => 'Official Gazette RA Registry',
            'filedAt' => 'Source-pending date',
            'verifiedAt' => 'July 12, 2026',
            'plainLanguageSummary' => 'This measure is listed as a San Juan heritage preservation act in the Phase 1 attribution worksheet. Any public claim should remain tied to the official Gazette or House record.',
            'whyItMatters' => 'Heritage measures can protect places, history, and civic identity that matter to San Juan residents.',
            'sanJuanRelevance' => 'The measure is locally relevant because it concerns San Juan heritage and should be checked against the final official Republic Act record before launch.',
            'officialSourceLabel' => 'View Official Gazette',
            'officialSourceUrl' => 'https://www.officialgazette.gov.ph/',
        ],
    ];

    $roleFromQuery = match ($request->query('role')) {
        'principal' => 'Principal Author',
        'co-author' => 'Co-Author',
        'sponsor' => 'Sponsor',
        default => 'All roles',
    };

    return Inertia::render('Public/Legislation/Index', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Legislation'],
        ],
        'legislativeItems' => $legislativeItems,
        'roleOptions' => collect($legislativeItems)->pluck('roleLabel')->unique()->values()->all(),
        'statusOptions' => collect($legislativeItems)->pluck('status')->unique()->values()->all(),
        'topicOptions' => collect($legislativeItems)->pluck('topic')->unique()->values()->all(),
        'initialFilters' => [
            'role' => $roleFromQuery,
            'status' => $request->query('status', 'All statuses'),
            'topic' => $request->query('topic', 'All topics'),
        ],
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.legislation');

$publicPageShells = [
    [
        'path' => '/about',
        'name' => 'public.about',
        'props' => [
            'title' => 'About Atty. Bel Zamora',
            'eyebrow' => 'Public profile',
            'description' => 'Verified biography, congressional role, legal background, and public-service values for the Lone District of San Juan City.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'About'],
            ],
            'sections' => [
                [
                    'title' => 'Verified official facts',
                    'body' => 'Biography content follows the Phase 1 source register, including official House records, bar admission details, and approved office identity.',
                ],
                [
                    'title' => 'Congressional scope',
                    'body' => 'The page will distinguish congressional responsibilities from city-government, barangay, and national-agency services.',
                ],
            ],
        ],
    ],
    [
        'path' => '/projects',
        'name' => 'public.projects',
        'props' => [
            'title' => 'Projects and Results',
            'eyebrow' => 'Transparency',
            'description' => 'A source-verified project directory for San Juan results, locations, partners, status, captions, and public evidence.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Projects'],
            ],
            'sections' => [
                [
                    'title' => 'Location-aware updates',
                    'body' => 'Project templates support barangay filtering and distinguish completed, ongoing, and source-pending records.',
                ],
                [
                    'title' => 'Evidence and captions',
                    'body' => 'Images require source, rights, alt text, factual captions, and privacy review before publication.',
                ],
            ],
        ],
    ],
    [
        'path' => '/news',
        'name' => 'public.news',
        'props' => [
            'title' => 'News and Media',
            'eyebrow' => 'Official updates',
            'description' => 'Verified announcements, press releases, community advisories, and future media-kit resources.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'News'],
            ],
            'sections' => [
                [
                    'title' => 'Current announcements',
                    'body' => 'Urgent public updates need review, source dates, and clear contact or next-step information.',
                ],
                [
                    'title' => 'Media resources',
                    'body' => 'Approved photos, biography notes, and fact sheets require communications approval before public download.',
                ],
            ],
        ],
    ],
    [
        'path' => '/contact',
        'name' => 'public.contact',
        'props' => [
            'title' => 'Contact the District Office',
            'eyebrow' => 'Assistance and inquiries',
            'description' => 'District Office location, official contact channels, and the future DPA-safe inquiry form layout.',
            'contentId' => 'inquiry-form',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Contact'],
            ],
            'sections' => [
                [
                    'title' => 'Office location',
                    'body' => 'Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City.',
                ],
                [
                    'title' => 'DPA-safe inquiry form',
                    'body' => 'The MVP form will collect only basic routing and contact details and must not accept IDs, medical records, or other sensitive documents.',
                ],
            ],
        ],
    ],
    [
        'path' => '/privacy',
        'name' => 'public.privacy',
        'props' => [
            'title' => 'Privacy Notice',
            'eyebrow' => 'Data Privacy Act',
            'description' => 'Public notice for data collection purpose, processing, retention, rights, and privacy contact.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Privacy Notice'],
            ],
            'sections' => [
                [
                    'title' => 'Data minimization',
                    'body' => 'The MVP inquiry form must collect only the minimum information needed to route and answer a basic inquiry.',
                ],
                [
                    'title' => 'Sensitive documents',
                    'body' => 'Sensitive document uploads are out of MVP scope and require a separate reviewed process.',
                ],
            ],
        ],
    ],
    [
        'path' => '/accessibility',
        'name' => 'public.accessibility',
        'props' => [
            'title' => 'Accessibility Statement',
            'eyebrow' => 'WCAG 2.2 AA target',
            'description' => 'Accessibility commitment for keyboard navigation, semantic landmarks, contrast, touch targets, and support contact.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Accessibility'],
            ],
            'sections' => [
                [
                    'title' => 'Keyboard and screen reader support',
                    'body' => 'Priority citizen journeys must work with semantic landmarks, explicit labels, and predictable focus order.',
                ],
                [
                    'title' => 'Mobile accessibility',
                    'body' => 'Touch targets, contrast, text size, and low-bandwidth behavior are part of the acceptance criteria.',
                ],
            ],
        ],
    ],
    [
        'path' => '/disclaimer',
        'name' => 'public.disclaimer',
        'props' => [
            'title' => 'Public Information Disclaimer',
            'eyebrow' => 'Official guidance',
            'description' => 'Clarifies external links, service routing, non-emergency use, and no-guarantee statements for assistance inquiries.',
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Disclaimer'],
            ],
            'sections' => [
                [
                    'title' => 'No approval promise',
                    'body' => 'Service guides explain next steps and responsible offices without promising assistance approval.',
                ],
                [
                    'title' => 'External sources',
                    'body' => 'Official records may link to House, city, barangay, national-agency, or partner organization websites.',
                ],
            ],
        ],
    ],
];

foreach ($publicPageShells as $page) {
    Route::get($page['path'], fn () => Inertia::render('Public/PageShell', $page['props']))
        ->name($page['name']);
}

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
