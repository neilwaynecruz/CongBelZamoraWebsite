<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InertiaTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_public_home_component(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Home')
                ->has('breadcrumbs', 1)
                ->has('quickActions', 3)
                ->has('featuredPrograms', 3)
                ->has('legislativeSpotlight')
                ->has('latestNews', 2));
    }

    public function test_about_page_renders_verified_profile_slice_props(): void
    {
        $this->get(route('public.about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/About')
                ->has('breadcrumbs', 2)
                ->where('profile.publicName', 'Atty. Bel Zamora')
                ->where('profile.fullName', 'Ysabel Maria Jorge Zamora')
                ->has('facts', 6)
                ->where('facts.4.value', 'Roll No. 59288; admitted May 3, 2011')
                ->has('timeline', 3)
                ->has('committeeMemberships', 5)
                ->has('valuePillars', 3)
                ->has('responsibilityBoundaries', 3)
                ->has('sourceLinks', 4)
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_programs_index_renders_program_slice_props(): void
    {
        $this->get(route('public.programs'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Index')
                ->has('breadcrumbs', 2)
                ->has('programs', 4)
                ->where('programs.0.title', 'CHED UniFAST Tulong Dunong')
                ->where('programs.0.responsibleOfficeType', 'joint_initiative')
                ->has('sectors')
                ->has('statuses')
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_tulong_dunong_detail_renders_program_contract(): void
    {
        $this->get(route('public.programs.tulong-dunong'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/TulongDunong')
                ->has('breadcrumbs', 4)
                ->where('program.title', 'CHED UniFAST Tulong Dunong Program')
                ->where('program.statusLabel', 'Bukas / Open')
                ->has('program.eligibility', 4)
                ->has('program.requirementGroups', 2)
                ->has('program.privacyNotes', 3));
    }

    public function test_gabay_directory_renders_routing_options(): void
    {
        $this->get(route('public.programs.gabay'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Gabay')
                ->has('breadcrumbs', 3)
                ->has('serviceOptions', 4)
                ->where('serviceOptions.0.id', 'education-assistance')
                ->where('serviceOptions.1.responsibleOfficeType', 'city_government')
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_legislation_tracker_renders_legislative_slice_props(): void
    {
        $this->get(route('public.legislation'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Legislation/Index')
                ->has('breadcrumbs', 2)
                ->has('legislativeItems', 3)
                ->where('legislativeItems.0.billNumber', 'HB00123')
                ->where('legislativeItems.0.roleLabel', 'Principal Author')
                ->has('roleOptions')
                ->has('statusOptions')
                ->has('topicOptions')
                ->where('initialFilters.role', 'All roles')
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_legislation_tracker_accepts_approved_role_query_filter(): void
    {
        $this->get(route('public.legislation', ['role' => 'co-author']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Legislation/Index')
                ->where('initialFilters.role', 'Co-Author'));
    }

    public function test_projects_index_renders_project_slice_props(): void
    {
        $this->get(route('public.projects'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Projects/Index')
                ->has('breadcrumbs', 2)
                ->has('projects', 5)
                ->where('projects.0.slug', 'solo-parent-educational-assistance')
                ->where('projects.0.barangay', 'Citywide')
                ->where('projects.1.statusLabel', 'Active / ongoing service')
                ->has('barangayOptions')
                ->has('statusOptions')
                ->has('categoryOptions')
                ->where('initialFilters.status', 'All statuses')
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_projects_index_accepts_approved_status_query_filter(): void
    {
        $this->get(route('public.projects', ['status' => 'ongoing']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Projects/Index')
                ->where('initialFilters.status', 'Active / ongoing service'));
    }

    public function test_news_index_renders_news_slice_props(): void
    {
        $this->get(route('public.news'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/News/Index')
                ->has('breadcrumbs', 2)
                ->has('newsItems', 5)
                ->where('newsItems.0.slug', 'tulong-dunong-orientation')
                ->where('newsItems.0.statusLabel', 'Current advisory')
                ->where('newsItems.4.statusLabel', 'Needs review before reuse')
                ->has('categoryOptions')
                ->has('statusOptions')
                ->has('yearOptions')
                ->where('initialFilters.status', 'All statuses')
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_news_index_accepts_approved_status_query_filter(): void
    {
        $this->get(route('public.news', ['status' => 'stale-review']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/News/Index')
                ->where('initialFilters.status', 'Needs review before reuse'));
    }

    public function test_contact_page_renders_inquiry_form_contract(): void
    {
        $this->get(route('public.contact'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Contact')
                ->has('breadcrumbs', 2)
                ->where('office.name', 'District Office of Atty. Congresswoman Bel Zamora')
                ->has('channels', 3)
                ->has('inquiryCategories', 4)
                ->where('inquiryCategories.0.value', 'educational_assistance')
                ->has('privacyHighlights', 3)
                ->has('responseExpectations', 3)
                ->where('inquiryAction', route('public.contact.inquiries.store'))
                ->where('confirmation', null)
                ->where('verifiedAt', 'July 12, 2026'));
    }

    public function test_contact_inquiry_validates_without_persistence_and_flashes_confirmation(): void
    {
        $this->from(route('public.contact'))
            ->post(route('public.contact.inquiries.store'), [
                'citizen_name' => 'Juan Dela Cruz',
                'contact_preference' => 'email',
                'citizen_email' => 'juan@example.com',
                'citizen_mobile' => null,
                'inquiry_category' => 'educational_assistance',
                'message_body' => 'I would like to ask about the current Tulong Dunong orientation schedule.',
                'privacy_consent' => '1',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHas('inquiryConfirmation.referenceCode')
            ->assertSessionHas('inquiryConfirmation.categoryLabel', 'Educational assistance')
            ->assertSessionHas('inquiryConfirmation.contactPreferenceLabel', 'Email');
    }

    public function test_contact_inquiry_rejects_missing_privacy_acknowledgement_and_contact_channel(): void
    {
        $this->from(route('public.contact'))
            ->post(route('public.contact.inquiries.store'), [
                'citizen_name' => 'Juan Dela Cruz',
                'contact_preference' => 'sms',
                'citizen_email' => null,
                'citizen_mobile' => null,
                'inquiry_category' => 'general_inquiry',
                'message_body' => 'Short routing question for the District Office.',
                'privacy_consent' => '0',
            ])
            ->assertRedirect(route('public.contact'))
            ->assertSessionHasErrors(['citizen_mobile', 'privacy_consent']);
    }

    public function test_privacy_page_renders_dedicated_policy_template(): void
    {
        $this->get(route('public.privacy'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Privacy')
                ->has('breadcrumbs', 2)
                ->where('effectiveDate', 'July 15, 2026')
                ->has('sections', 4)
                ->where('sections.0.title', 'Data minimization')
                ->where('privacyContact.href', '/contact#inquiry-form'));
    }

    public function test_accessibility_page_renders_dedicated_statement_template(): void
    {
        $this->get(route('public.accessibility'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Accessibility')
                ->has('breadcrumbs', 2)
                ->where('reviewDate', 'July 15, 2026')
                ->has('sections', 3)
                ->where('sections.0.title', 'Target standard')
                ->where('supportContact.href', '/contact#inquiry-form'));
    }

    public function test_disclaimer_page_renders_dedicated_disclaimer_template(): void
    {
        $this->get(route('public.disclaimer'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Disclaimer')
                ->has('breadcrumbs', 2)
                ->where('reviewDate', 'July 15, 2026')
                ->has('sections', 4)
                ->where('sections.0.title', 'No approval promise')
                ->where('contact.href', '/contact#inquiry-form'));
    }
}
