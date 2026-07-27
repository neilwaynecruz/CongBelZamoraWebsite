<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\InquiryStatus;
use App\Filament\Resources\ContentApprovalResource;
use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\InquiryResource\Pages\ListInquiries;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Tier1AdminOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_administrator_can_manage_staff_users(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $editor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();

        $this->assertTrue($super->can('viewAny', User::class));
        $this->assertFalse($editor->can('viewAny', User::class));

        $this->actingAs($super)
            ->get(UserResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($editor)
            ->get(UserResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_super_administrator_can_create_staff_user(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        Livewire::actingAs($super)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Inquiry Officer Two',
                'email' => 'officer-two@example.com',
                'admin_role' => AdminRole::InquiryOfficer->value,
                'is_active' => true,
                'password' => 'Password123!',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'officer-two@example.com',
            'admin_role' => AdminRole::InquiryOfficer->value,
            'is_active' => 1,
        ]);
    }

    public function test_inactive_staff_cannot_access_admin_panel(): void
    {
        $user = User::factory()->adminRole(AdminRole::ProgramOfficer)->create([
            'is_active' => false,
        ]);

        $this->assertFalse($user->canAccessPanel(filament()->getDefaultPanel()));
    }

    public function test_content_governance_navigation_group_is_registered(): void
    {
        $this->assertSame('Content Governance', ContentApprovalResource::getNavigationGroup());
    }

    public function test_approval_status_workflow_supports_submit_approve_publish(): void
    {
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $page = PublicPage::factory()->create();

        $approval = ContentApproval::query()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'revision_number' => 10,
            'status' => ContentApprovalStatus::Draft,
            'submitted_by_id' => $reviewer->id,
            'submitted_at' => now(),
            'is_fast_track' => false,
            'metadata' => ['launch_item' => 'Test gate'],
        ]);

        $this->assertTrue($reviewer->can('update', $approval));

        $approval->forceFill([
            'status' => ContentApprovalStatus::ForReview,
        ])->save();
        $this->assertSame(ContentApprovalStatus::ForReview, $approval->fresh()->status);

        $approval->forceFill([
            'status' => ContentApprovalStatus::Approved,
            'reviewed_by_id' => $reviewer->id,
            'reviewed_at' => now(),
            'approved_at' => now(),
        ])->save();
        $this->assertSame(ContentApprovalStatus::Approved, $approval->fresh()->status);

        $approval->forceFill([
            'status' => ContentApprovalStatus::Published,
            'published_at' => now(),
        ])->save();
        $this->assertSame(ContentApprovalStatus::Published, $approval->fresh()->status);
    }

    public function test_inquiry_redacted_export_omits_citizen_fields(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        Inquiry::factory()->create([
            'reference_code' => 'INQ-2026-EXPORT',
            'citizen_name' => 'Secret Citizen',
            'citizen_email' => 'secret@example.com',
            'message_body' => 'Secret message body',
            'status' => InquiryStatus::Pending,
            'assigned_officer_id' => $officer->id,
            'created_at' => now()->subHours(50),
        ]);

        $this->actingAs($officer);

        $response = InquiryResource::exportRedactedCsv(
            InquiryResource::getEloquentQuery()->where('reference_code', 'INQ-2026-EXPORT'),
        );

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $this->assertStringContainsString('reference_code', $csv);
        $this->assertStringContainsString('INQ-2026-EXPORT', $csv);
        $this->assertStringContainsString('yes', $csv);
        $this->assertStringNotContainsString('Secret Citizen', $csv);
        $this->assertStringNotContainsString('secret@example.com', $csv);
        $this->assertStringNotContainsString('Secret message body', $csv);
        $this->assertStringNotContainsString('citizen_name', $csv);
    }

    public function test_inquiry_list_is_available_to_inquiry_officer(): void
    {
        $officer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        Livewire::actingAs($officer)
            ->test(ListInquiries::class)
            ->assertSuccessful();
    }

    public function test_staff_user_list_page_loads_for_super_admin(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        Livewire::actingAs($super)
            ->test(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_canned_replies_are_configured(): void
    {
        $replies = config('belzamora.inquiries.canned_replies');

        $this->assertIsArray($replies);
        $this->assertArrayHasKey('ack_received', $replies);
        $this->assertNotEmpty($replies['ack_received']);
    }

    public function test_sla_and_media_config_defaults_are_precise(): void
    {
        $this->assertSame(48, (int) config('belzamora.inquiries.unanswered_sla_hours'));
        $this->assertSame(5120, (int) config('belzamora.media.max_upload_kilobytes'));
        $this->assertSame('approved-media/news', config('belzamora.media.news_directory'));
        $this->assertSame('approved-media/project-evidence', config('belzamora.media.project_evidence_directory'));
    }

    public function test_launch_readiness_smoke_paths_use_existing_social_preview_asset(): void
    {
        $this->assertFileExists(public_path('assets/images/brand/social-preview.png'));
    }

    public function test_super_administrator_cannot_demote_own_role(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        Livewire::actingAs($super)
            ->test(EditUser::class, [
                'record' => $super->getRouteKey(),
            ])
            ->fillForm([
                'name' => $super->name,
                'email' => $super->email,
                'admin_role' => AdminRole::ContentAdministrator->value,
                'is_active' => true,
            ])
            ->call('save')
            ->assertHasFormErrors(['admin_role']);

        $this->assertTrue($super->fresh()?->hasAdminRole(AdminRole::SuperAdministrator));
    }
}
