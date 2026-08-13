<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryStatus;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_custom_admin_login(): void
    {
        $this->get(route('admin-console.dashboard'))
            ->assertRedirect(route('admin-console.login'));

        $this->get(route('admin-console.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Auth/Login')
                ->where('loginUrl', route('login.store'))
                ->where('office.district', config('belzamora.office.district')));
    }

    public function test_custom_login_sets_admin_console_as_intended_destination(): void
    {
        $staff = User::factory()->adminRole(AdminRole::InquiryOfficer)->create([
            'email' => 'staff@example.test',
        ]);

        $this->get(route('admin-console.login'))->assertOk();

        $this->post(route('login.store'), [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect(route('admin-console.dashboard'));

        $this->actingAs($staff)
            ->get(route('admin-console.login'))
            ->assertRedirect(route('admin-console.dashboard'));
    }

    public function test_non_staff_and_inactive_staff_cannot_access_console(): void
    {
        $nonStaff = User::factory()->create(['admin_role' => null]);
        $inactiveStaff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create([
            'is_active' => false,
        ]);

        $this->actingAs($nonStaff)
            ->get(route('admin-console.dashboard'))
            ->assertForbidden();

        $this->actingAs($inactiveStaff)
            ->get(route('admin-console.dashboard'))
            ->assertForbidden();
    }

    public function test_unverified_staff_is_sent_to_verification_notice(): void
    {
        $staff = User::factory()
            ->unverified()
            ->adminRole(AdminRole::ContentAdministrator)
            ->create();

        $this->actingAs($staff)
            ->get(route('admin-console.dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_confirmed_two_factor_is_enforced_when_required(): void
    {
        config()->set('belzamora.staff.require_2fa', true);

        $withoutTwoFactor = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $withTwoFactor = User::factory()
            ->withTwoFactor()
            ->adminRole(AdminRole::InquiryOfficer)
            ->create();

        $this->actingAs($withoutTwoFactor)
            ->get(route('admin-console.dashboard'))
            ->assertRedirect(route('security.edit'));

        $this->actingAs($withTwoFactor)
            ->get(route('admin-console.dashboard'))
            ->assertOk();
    }

    public function test_dashboard_renders_real_metrics_and_role_filtered_navigation(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        Inquiry::factory()->create([
            'status' => InquiryStatus::Pending,
            'assigned_officer_id' => $officer->id,
            'first_response_due_at' => now()->subHour(),
        ]);

        $this->actingAs($officer)
            ->get(route('admin-console.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('adminConsole.user.role', AdminRole::InquiryOfficer->value)
                ->has('adminConsole.navigation')
                ->where('inquiryQueue.assigned_overdue', 1)
                ->where('metrics.unanswered_inquiries', 1)
                ->has('attentionItems', 1)
                ->where('attentionItems.0.type', 'Inquiry')
                ->missing('attentionItems.0.citizen_name')
                ->missing('attentionItems.0.message_body'));

        $navigation = app(AdminConsoleNavigationService::class)->for($officer);
        $labels = collect($navigation)->pluck('items')->flatten(1)->pluck('label');

        $this->assertTrue($labels->contains('Inquiry Cases'));
        $this->assertFalse($labels->contains('Content Review'));
        $this->assertFalse($labels->contains('Design System'));
    }

    public function test_dashboard_attention_news_and_source_items_use_custom_admin_console_links(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $record = NewsRecord::factory()->create([
            'title' => 'Balik Sigla, Bigay Saya community gift-giving record needs review',
            'review_due_at' => now()->subDays(2),
        ]);

        SourceRecord::factory()
            ->for($record, 'sourceable')
            ->create([
                'title' => 'Source for Balik Sigla news record',
                'next_review_at' => now()->subDay(),
            ]);

        $response = $this->actingAs($staff)->get(route('admin-console.dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('attentionItems.0.type', 'News Record')
                ->where('attentionItems.0.href', route('admin-console.news.show', $record))
                ->where('attentionItems.0.usesLegacyFallback', false)
                ->where('attentionItems.1.type', 'Source')
                ->where('attentionItems.1.href', route('admin-console.news.show', $record))
                ->where('attentionItems.1.usesLegacyFallback', false));

        $this->assertStringNotContainsString('/admin/news-records', $response->getContent());
    }

    public function test_inquiry_lists_never_expose_citizen_identity_and_auditor_detail_is_redacted(): void
    {
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiry = Inquiry::factory()->create([
            'reference_code' => 'INQ-2026-REDACTED',
            'citizen_name' => 'Private Citizen',
            'citizen_email' => 'private@example.test',
            'message_body' => 'Sensitive case information.',
        ]);

        $index = $this->actingAs($auditor)->get(route('admin-console.inquiries.index'));
        $index->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Index')
            ->where('inquiries.data.0.referenceCode', 'INQ-2026-REDACTED'));
        $this->assertStringNotContainsString('Private Citizen', $index->getContent());
        $this->assertStringNotContainsString('private@example.test', $index->getContent());
        $this->assertStringNotContainsString('Sensitive case information.', $index->getContent());

        $detail = $this->actingAs($auditor)->get(route('admin-console.inquiries.show', $inquiry));
        $detail->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Inquiries/Show')
            ->where('inquiry.citizen.restricted', true)
            ->where('inquiry.citizen.name', null)
            ->where('inquiry.message', null)
            ->where('inquiry.canViewCitizenDetails', false));
        $this->assertStringNotContainsString('Private Citizen', $detail->getContent());
        $this->assertStringNotContainsString('Sensitive case information.', $detail->getContent());
    }

    public function test_inquiry_officer_can_view_authorized_citizen_detail(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $inquiry = Inquiry::factory()->create([
            'assigned_officer_id' => $officer->id,
            'citizen_name' => 'Authorized Citizen',
            'message_body' => 'Case content visible to assigned officer.',
        ]);

        $this->actingAs($officer)
            ->get(route('admin-console.inquiries.show', $inquiry))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('inquiry.citizen.restricted', false)
                ->where('inquiry.citizen.name', 'Authorized Citizen')
                ->where('inquiry.message', 'Case content visible to assigned officer.')
                ->where('inquiry.canViewCitizenDetails', true));
    }

    public function test_admin_search_only_returns_record_types_the_role_can_view(): void
    {
        $officer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        Program::factory()->create(['title' => 'Scholarship Search Match']);
        NewsRecord::factory()->create(['title' => 'Scholarship News Match']);

        $response = $this->actingAs($officer)->getJson(route('admin-console.search', [
            'term' => 'Scholarship',
        ]));

        $response->assertOk();
        $types = collect($response->json('data'))->pluck('type');

        $this->assertTrue($types->contains('Program'));
        $this->assertFalse($types->contains('News'));
    }

    public function test_authorized_reviewer_can_open_read_only_content_approval_queue_and_dossier(): void
    {
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $program = Program::factory()->create(['title' => 'Governed Program Record']);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::ForReview,
                'submitted_at' => now(),
            ]);

        $this->actingAs($reviewer)
            ->get(route('admin-console.content-approvals.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ContentApprovals/Index')
                ->where('approvals.data.0.recordTitle', 'Governed Program Record')
                ->where('approvals.data.0.status.value', ContentApprovalStatus::ForReview->value));

        $this->actingAs($reviewer)
            ->get(route('admin-console.content-approvals.show', $approval))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ContentApprovals/Show')
                ->where('approval.recordTitle', 'Governed Program Record')
                ->where('approval.status.value', ContentApprovalStatus::ForReview->value));
    }

    public function test_design_system_is_restricted_to_super_administrators(): void
    {
        $editor = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        $this->actingAs($editor)
            ->get(route('admin-console.design-system'))
            ->assertForbidden();

        $this->actingAs($super)
            ->get(route('admin-console.design-system'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Admin/DesignSystem'));
    }
}
