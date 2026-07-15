<?php

use App\Enums\InquiryCategory;
use App\Http\Controllers\PublicInquiryController;
use App\Http\Controllers\PublicProgramController;
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

Route::get('/about', function () {
    return Inertia::render('Public/About', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'About'],
        ],
        'profile' => [
            'publicName' => 'Atty. Bel Zamora',
            'fullName' => 'Ysabel Maria Jorge Zamora',
            'title' => 'Representative, Lone District of San Juan City',
            'district' => 'Lone District of San Juan City',
            'summary' => 'A verified public profile for Atty. Congresswoman Bel Zamora, written in plain public-service language and limited to facts recorded in the Phase 1 source register.',
            'verifiedAt' => 'July 12, 2026',
        ],
        'facts' => [
            [
                'label' => 'Public service name',
                'value' => 'Atty. Bel Zamora',
                'sourceLabel' => 'Official website and public profile records',
                'sourceUrl' => 'https://www.belzamora.com/',
            ],
            [
                'label' => 'Full legal name',
                'value' => 'Ysabel Maria Jorge Zamora',
                'sourceLabel' => 'House of Representatives member record K099',
                'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
            ],
            [
                'label' => 'Current office',
                'value' => 'Representative / Congresswoman, Lone District of San Juan City',
                'sourceLabel' => 'House of Representatives member record K099',
                'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
            ],
            [
                'label' => 'Terms of office',
                'value' => '19th Congress (2022-2025), 20th Congress (2025-2028)',
                'sourceLabel' => 'House of Representatives member database',
                'sourceUrl' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
            ],
            [
                'label' => 'Bar admission',
                'value' => 'Roll No. 59288; admitted May 3, 2011',
                'sourceLabel' => 'Supreme Court law list / IBP record cited in Phase 1',
            ],
            [
                'label' => 'Education',
                'value' => 'Juris Doctor, Ateneo Law School; AB Psychology, Ateneo de Manila University',
                'sourceLabel' => 'Supreme Court roll records / official biography portal cited in Phase 1',
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
                'description' => 'Admitted to the Philippine Bar under Roll No. 59288, as cited in the Phase 1 biography verification worksheet.',
                'sourceLabel' => 'Supreme Court law list / IBP record cited in Phase 1',
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
                'summary' => 'Bills, resolutions, committee work, and policy feedback belong to the congressional role and should be checked against official House records.',
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
                'summary' => 'Local permits, barangay certificates, city services, and agency-managed benefits should start with the correct responsible office.',
                'nextStepLabel' => 'Use Gabay San Juaneño',
                'nextStepHref' => '/programs/gabay',
            ],
        ],
        'sourceLinks' => [
            [
                'label' => 'House member record',
                'href' => 'https://www.congress.gov.ph/house-members/view/?member=K099',
                'note' => 'Primary source for official office, district, and member profile details.',
            ],
            [
                'label' => 'House committee memberships',
                'href' => 'https://www.congress.gov.ph/house-members/view/memberships/?member=K099&page=',
                'note' => 'Primary source for committee roles and membership updates.',
            ],
            [
                'label' => 'Official website archive',
                'href' => 'https://www.belzamora.com/',
                'note' => 'Reference for existing public identity and contact registry, subject to Phase 1 cleanup rules.',
            ],
            [
                'label' => 'San Juan City Government directory',
                'href' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
                'note' => 'Official source for District Office location verification.',
            ],
        ],
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.about');

Route::get('/programs', [PublicProgramController::class, 'index'])->name('public.programs');
Route::get('/programs/gabay', [PublicProgramController::class, 'gabay'])->name('public.programs.gabay');
Route::get('/programs/education/tulong-dunong', [PublicProgramController::class, 'tulongDunong'])->name('public.programs.tulong-dunong');

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

Route::get('/projects', function (Request $request) {
    $projects = [
        [
            'title' => 'Educational Assistance for Children of Solo Parents',
            'slug' => 'solo-parent-educational-assistance',
            'category' => 'Public Assistance and Education',
            'status' => 'completed',
            'statusLabel' => 'Completed / documented',
            'statusTone' => 'completed',
            'barangay' => 'Citywide',
            'location' => 'San Juan City, official activity record',
            'responsibleOffice' => 'San Juan City Government with documented District Office participation',
            'partner' => 'San Juan City Government',
            'summary' => 'The official source describes a distribution of educational assistance for children of registered solo parents in San Juan.',
            'evidenceSummary' => 'Use the official San Juan activity page as the canonical source and request the approved original image before publication.',
            'impactNote' => 'Supports families and students while keeping the page clear that the activity is a city-government record with documented officials present.',
            'imageGuidance' => 'Approved original activity image required; do not hotlink the temporary CMS image URL.',
            'sourceLabel' => 'View official San Juan activity page',
            'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/1028/pamamahagi-ng-p300000-educational-assistance-para-sa-mga-anak-ng-solo-parents-ng-san-juan',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => 'Bagong Pilipinas eGovPH Serbisyo Hub',
            'slug' => 'egovph-serbisyo-hub',
            'category' => 'Digital Government',
            'status' => 'ongoing',
            'statusLabel' => 'Active / ongoing service',
            'statusTone' => 'ongoing',
            'barangay' => 'Citywide',
            'location' => 'Makabagong San Juan National Government Center',
            'responsibleOffice' => 'DICT, national government, San Juan City Government, and partner offices',
            'partner' => 'DICT / eGovPH and city partners',
            'summary' => 'The official source covers the inauguration and launch of the eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center.',
            'evidenceSummary' => 'The page should present this as a government partnership and point residents to the correct service channel.',
            'impactNote' => 'Helps residents understand where national digital government services are available in San Juan.',
            'imageGuidance' => 'Use only an approved local copy with source, rights, caption, and alt text review.',
            'sourceLabel' => 'View official San Juan news page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => '2025 National Women\'s Month Services',
            'slug' => 'national-womens-month-services-2025',
            'category' => 'Women and Family Services',
            'status' => 'completed',
            'statusLabel' => 'Completed / archived',
            'statusTone' => 'completed',
            'barangay' => 'Citywide',
            'location' => 'San Juan City, official activity record',
            'responsibleOffice' => 'City Government and partner departments with District Office coordination where documented',
            'partner' => 'City Health Department and partner offices',
            'summary' => 'The official source describes a Women\'s Month activity with healthcare, employment, legal consultation, civil-registry, and wellness services.',
            'evidenceSummary' => 'The page must identify the city and partner offices as organizers when supported by the source.',
            'impactNote' => 'Highlights access to women, family, health, legal, and civil-registry services without overstating District Office ownership.',
            'imageGuidance' => 'Use the image as advocacy evidence only after source, consent, caption, and privacy review.',
            'sourceLabel' => 'View official San Juan activity page',
            'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => 'Gawad Agraryo 2025 at Makabagong San Juan',
            'slug' => 'gawad-agraryo-2025',
            'category' => 'Government Partnerships',
            'status' => 'completed',
            'statusLabel' => 'Completed / documented',
            'statusTone' => 'completed',
            'barangay' => 'Citywide',
            'location' => 'Makabagong San Juan National Government Center',
            'responsibleOffice' => 'National government and city partners',
            'partner' => 'National government and San Juan City partners',
            'summary' => 'The official source documents Gawad Agraryo 2025 at the Makabagong San Juan National Government Center and identifies Congresswoman Bel Zamora among officials present.',
            'evidenceSummary' => 'Use this only for the related event, gallery, or government-partnership context with a factual caption.',
            'impactNote' => 'Shows San Juan as a venue for national-government partnership activity without implying sole District Office operation.',
            'imageGuidance' => 'Avoid cropping the stage title when it is necessary to identify the event.',
            'sourceLabel' => 'View official San Juan news page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025',
            'verifiedAt' => 'July 12, 2026',
        ],
        [
            'title' => 'Balik Sigla, Bigay Saya Community Gift-Giving',
            'slug' => 'balik-sigla-bigay-saya',
            'category' => 'Community Outreach',
            'status' => 'completed',
            'statusLabel' => 'Completed / documented',
            'statusTone' => 'completed',
            'barangay' => 'Citywide',
            'location' => 'San Juan City, official activity record',
            'responsibleOffice' => 'San Juan City Government and documented partner officials',
            'partner' => 'San Juan City Government',
            'summary' => 'The official San Juan page and PIA coverage document the Balik Sigla, Bigay Saya community gift-giving activity.',
            'evidenceSummary' => 'Present as a source-linked community-outreach record and avoid using private news images without permission.',
            'impactNote' => 'Provides residents with a public record of community support activity while preserving source attribution.',
            'imageGuidance' => 'Request approved original image files from the District Office or City Public Information Department.',
            'sourceLabel' => 'View official San Juan activity page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program',
            'verifiedAt' => 'July 12, 2026',
        ],
    ];

    $statusFromQuery = match ($request->query('status')) {
        'completed' => 'Completed / documented',
        'ongoing' => 'Active / ongoing service',
        'source-pending' => 'Source pending',
        default => 'All statuses',
    };

    return Inertia::render('Public/Projects/Index', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Projects'],
        ],
        'projects' => $projects,
        'barangayOptions' => collect($projects)->pluck('barangay')->unique()->values()->all(),
        'statusOptions' => collect($projects)->pluck('statusLabel')->unique()->values()->all(),
        'categoryOptions' => collect($projects)->pluck('category')->unique()->values()->all(),
        'initialFilters' => [
            'barangay' => $request->query('barangay', 'All barangays'),
            'status' => $statusFromQuery,
            'category' => $request->query('category', 'All categories'),
        ],
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.projects');

Route::get('/news', function (Request $request) {
    $newsItems = [
        [
            'title' => 'Tulong Dunong orientation schedule for eligible students',
            'slug' => 'tulong-dunong-orientation',
            'category' => 'Education',
            'status' => 'current',
            'statusLabel' => 'Current advisory',
            'statusTone' => 'current',
            'publishedAt' => 'July 12, 2026',
            'updatedAt' => 'July 12, 2026',
            'sourceLabel' => 'CHED UniFAST information',
            'sourceUrl' => 'https://unifast.gov.ph/',
            'summary' => 'Verified advisory content directs eligible San Juan students to current Tulong Dunong requirements, orientation guidance, and official application channels.',
            'officialUpdate' => 'Students should confirm the active cycle, accepted documents, and deadline with the District Office and CHED / UniFAST source before preparing documents.',
            'nextStep' => 'Read the Tulong Dunong details page and use official channels only for follow-up questions.',
            'staleNote' => 'Review before every application cycle because slots, deadlines, and documentary requirements can change.',
            'owner' => 'Communications Lead with Education Assistance staff review',
        ],
        [
            'title' => 'Bagong Pilipinas eGovPH Serbisyo Hub launched in San Juan',
            'slug' => 'egovph-serbisyo-hub-launch',
            'category' => 'Digital Government',
            'status' => 'current',
            'statusLabel' => 'Current public-service update',
            'statusTone' => 'current',
            'publishedAt' => 'July 12, 2026',
            'updatedAt' => 'July 12, 2026',
            'sourceLabel' => 'View official San Juan news page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2136/inauguration-and-official-launch-of-the-bagong-pilipinas-egovph-serbisyo-hub-at-makabagong-san-juan-national-government-center',
            'summary' => 'The official San Juan source covers the inauguration and official launch of the eGovPH Serbisyo Hub at the Makabagong San Juan National Government Center.',
            'officialUpdate' => 'Present this as a government-partnership service update and route residents to the responsible national or city channel for transaction-specific requirements.',
            'nextStep' => 'Use Gabay San Juaneño to identify the correct office for digital government service questions.',
            'staleNote' => 'Recheck the service scope and responsible office before publishing new transaction guidance.',
            'owner' => 'Communications Lead',
        ],
        [
            'title' => '2025 National Women\'s Month services documented',
            'slug' => 'national-womens-month-services-2025',
            'category' => 'Women and Family',
            'status' => 'archived',
            'statusLabel' => 'Archived official update',
            'statusTone' => 'archived',
            'publishedAt' => 'March 2025',
            'updatedAt' => 'July 12, 2026',
            'sourceLabel' => 'View official San Juan activity page',
            'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Selected_Activities/2054/2025-national-womens-month-celebration',
            'summary' => 'The official activity page documents Women\'s Month services including healthcare, employment, legal consultation, civil-registry, and wellness support.',
            'officialUpdate' => 'Use as an archived service record with accurate city and partner-office attribution.',
            'nextStep' => 'For current services, check the latest official San Juan City or District Office advisory.',
            'staleNote' => 'Do not reuse dates, service lists, or participating offices for a new year without a fresh source.',
            'owner' => 'Communications Lead with partner-office confirmation',
        ],
        [
            'title' => 'Gawad Agraryo 2025 held at Makabagong San Juan',
            'slug' => 'gawad-agraryo-2025',
            'category' => 'Government Partnerships',
            'status' => 'archived',
            'statusLabel' => 'Archived official update',
            'statusTone' => 'archived',
            'publishedAt' => '2025',
            'updatedAt' => 'July 12, 2026',
            'sourceLabel' => 'View official San Juan news page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2144/gawad-agraryo-2025',
            'summary' => 'The official San Juan news page documents Gawad Agraryo 2025 at the Makabagong San Juan National Government Center and identifies officials present.',
            'officialUpdate' => 'Present the item as an event and government-partnership record without implying sole District Office operation.',
            'nextStep' => 'Use the official source for event context, captions, and approved publication wording.',
            'staleNote' => 'Confirm event title, date, agency ownership, and approved image rights before gallery use.',
            'owner' => 'Communications Lead',
        ],
        [
            'title' => 'Balik Sigla, Bigay Saya community gift-giving record needs review',
            'slug' => 'balik-sigla-bigay-saya-review',
            'category' => 'Community Outreach',
            'status' => 'stale_review',
            'statusLabel' => 'Needs review before reuse',
            'statusTone' => 'staleReview',
            'publishedAt' => 'December 2024',
            'updatedAt' => 'July 12, 2026',
            'sourceLabel' => 'View official San Juan activity page',
            'sourceUrl' => 'https://sanjuancity.gov.ph/Makabagong_SJ_Selected_News/2118/balik-sigla-bigay-saya-simultaneous-christmas-gift-giving-program',
            'summary' => 'The official San Juan page and PIA coverage document a previous holiday gift-giving activity involving San Juan officials.',
            'officialUpdate' => 'This record should be retained only as historical evidence until current dates, partners, captions, and permissions are reviewed.',
            'nextStep' => 'Ask the communications team to verify whether a new advisory, image set, or partner attribution exists.',
            'staleNote' => 'Do not display as a current assistance schedule. Mark as stale until reviewed by the content owner.',
            'owner' => 'Communications Lead and Chief of Staff approval',
        ],
    ];

    $statusFromQuery = match ($request->query('status')) {
        'current' => 'Current advisory',
        'archived' => 'Archived official update',
        'stale-review' => 'Needs review before reuse',
        default => 'All statuses',
    };

    return Inertia::render('Public/News/Index', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'News'],
        ],
        'newsItems' => $newsItems,
        'categoryOptions' => collect($newsItems)->pluck('category')->unique()->values()->all(),
        'statusOptions' => collect($newsItems)->pluck('statusLabel')->unique()->values()->all(),
        'yearOptions' => collect($newsItems)
            ->pluck('publishedAt')
            ->map(fn (string $date) => str($date)->afterLast(' ')->toString())
            ->unique()
            ->values()
            ->all(),
        'initialFilters' => [
            'category' => $request->query('category', 'All categories'),
            'status' => $statusFromQuery,
            'year' => $request->query('year', 'All years'),
        ],
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.news');

Route::get('/contact', function (Request $request) {
    return Inertia::render('Public/Contact', [
        'breadcrumbs' => [
            ['title' => 'Home', 'href' => '/'],
            ['title' => 'Contact'],
        ],
        'office' => [
            'name' => 'District Office of Atty. Congresswoman Bel Zamora',
            'address' => 'Upper Ground Floor, Makabagong San Juan National Government Center, Pinaglabanan St, San Juan City',
            'locationNote' => 'The official San Juan City Government directory lists the District Office of the Congresswoman at the Upper Ground Floor with extensions 264 to 265.',
            'officeHours' => 'Office hours and visitor procedures must be reconfirmed with the District Office before launch.',
            'verifiedAt' => 'July 12, 2026',
            'sourceLabel' => 'View official San Juan City Government directory',
            'sourceUrl' => 'https://www.sanjuancity.gov.ph/Makabagong_SJ_Directory',
        ],
        'channels' => [
            [
                'label' => 'Official email',
                'value' => 'info@belzamora.com',
                'href' => 'mailto:info@belzamora.com',
                'note' => 'Use for non-urgent public inquiries after the custom-domain email is activated.',
            ],
            [
                'label' => 'Published legacy email',
                'value' => 'atty.belzamora@gmail.com',
                'href' => 'mailto:atty.belzamora@gmail.com',
                'note' => 'Listed in the Phase 1 contact registry; confirm final long-term channel before launch.',
            ],
            [
                'label' => 'Office directory extensions',
                'value' => 'Extensions 264 to 265',
                'note' => 'Directory listing only; direct phone number and visitor process require District Office confirmation.',
            ],
        ],
        'inquiryCategories' => InquiryCategory::publicOptions(),
        'privacyHighlights' => [
            'The form asks only for name, one contact channel, inquiry category, and a short message.',
            'Do not include government ID numbers, birth certificates, income records, grades, medical records, prescriptions, diagnosis, or photos of documents.',
            'Document uploads are not part of the MVP and require a separate privacy, legal, security, retention, and access review.',
        ],
        'responseExpectations' => [
            'This form is for non-emergency routing and basic questions only.',
            'Submission does not guarantee program eligibility, assistance approval, appointment confirmation, or case acceptance.',
            'The District Office may route the concern to the proper city, barangay, national-agency, or partner-office channel.',
        ],
        'inquiryAction' => route('public.contact.inquiries.store'),
        'confirmation' => $request->session()->get('inquiryConfirmation'),
        'verifiedAt' => 'July 12, 2026',
    ]);
})->name('public.contact');

Route::post('/contact/inquiries', [PublicInquiryController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('public.contact.inquiries.store');

Route::get('/privacy', fn () => Inertia::render('Public/Privacy', [
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
                'No file uploads are accepted in the Phase 6 public form.',
            ],
        ],
        [
            'title' => 'Purpose and use',
            'body' => 'Information submitted through the inquiry form is used only to validate the form contract in Phase 6 and, after Phase 7 is approved, to route citizen concerns to authorized District Office staff.',
            'items' => [
                'Program questions may be routed to the responsible program officer.',
                'City, barangay, or national-agency concerns may be redirected to the correct office.',
                'Legislative feedback may be reviewed by authorized legislative staff.',
            ],
        ],
        [
            'title' => 'Retention and future storage',
            'body' => 'The current Phase 6 form validates submissions but does not persist them. Phase 7 must add encrypted storage, least-privilege staff access, audit logs, and the approved 180-day retention and anonymization policy before production inquiry intake.',
        ],
        [
            'title' => 'Privacy rights',
            'body' => 'Residents may ask how their inquiry information is handled and may request correction, deletion, or clarification through the privacy contact after production intake is enabled.',
        ],
    ],
    'privacyContact' => [
        'label' => 'Contact the District Office',
        'href' => '/contact#inquiry-form',
        'note' => 'For privacy questions, use the Contact page and choose General inquiry until the final Data Protection Officer mailbox is confirmed.',
    ],
]))->name('public.privacy');

Route::get('/accessibility', fn () => Inertia::render('Public/Accessibility', [
    'breadcrumbs' => [
        ['title' => 'Home', 'href' => '/'],
        ['title' => 'Accessibility'],
    ],
    'reviewDate' => 'July 15, 2026',
    'sections' => [
        [
            'title' => 'Target standard',
            'body' => 'The public website targets WCAG 2.2 Level AA for priority citizen journeys, including program discovery, service routing, legislative lookup, and Contact form use.',
            'items' => [
                'Semantic headings and landmarks are required.',
                'Interactive controls must show visible focus states.',
                'Important status information must not rely on color alone.',
            ],
        ],
        [
            'title' => 'Known verification gap',
            'body' => 'Automated unit and build checks pass for Phase 6 slices, but browser-level screen reader, keyboard, hydration, and responsive checks must still be completed before launch approval.',
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
]))->name('public.accessibility');

Route::get('/disclaimer', fn () => Inertia::render('Public/Disclaimer', [
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
                'Emergency concerns should use official emergency channels.',
                'Legal, medical, and financial decisions should come from qualified offices or professionals.',
                'National-agency requirements may change without notice to the District Office.',
            ],
        ],
        [
            'title' => 'External source links',
            'body' => 'The website links to official House, city, national-agency, and partner sources for verification. External websites have their own privacy, accessibility, and content policies.',
        ],
        [
            'title' => 'Current information',
            'body' => 'Dates, program status, requirements, contacts, and source links must be rechecked before launch and reviewed on the cadence approved in the content governance plan.',
        ],
    ],
    'contact' => [
        'label' => 'Ask for Routing Help',
        'href' => '/contact#inquiry-form',
        'note' => 'Use the Contact page if you need help identifying the correct office for a concern.',
    ],
]))->name('public.disclaimer');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
