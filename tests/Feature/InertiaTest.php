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
}
