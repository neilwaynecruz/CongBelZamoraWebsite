<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\InquiryStatus;
use App\Filament\Resources\InquiryResource;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\User;
use App\Notifications\InquiryReceivedNotification;
use App\Notifications\StaffInquiryReceivedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Phase7InquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_inquiry_is_encrypted_audited_and_confirmed(): void
    {
        Notification::fake();

        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

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
            ->assertSessionHas('inquiryConfirmation.retentionNotice');

        $inquiry = Inquiry::query()->firstOrFail();
        $rawInquiry = DB::table('inquiries')->where('id', $inquiry->id)->first();

        expect($inquiry->citizen_name)->toBe('Juan Dela Cruz')
            ->and($inquiry->citizen_email)->toBe('juan@example.com')
            ->and($inquiry->message_body)->toContain('Tulong Dunong')
            ->and($inquiry->reference_code)->toStartWith('INQ-')
            ->and($inquiry->retention_expires_at?->isFuture())->toBeTrue()
            ->and($rawInquiry->citizen_name)->not->toBe('Juan Dela Cruz')
            ->and($rawInquiry->citizen_email)->not->toBe('juan@example.com')
            ->and($rawInquiry->message_body)->not->toContain('Tulong Dunong');

        $auditLog = AuditLog::query()->where('event', 'inquiry.submitted')->firstOrFail();

        expect($auditLog->auditable_id)->toBe($inquiry->id)
            ->and($auditLog->new_values)->toHaveKey('reference_code')
            ->and(json_encode($auditLog->new_values))->not->toContain('Juan Dela Cruz')
            ->and(json_encode($auditLog->new_values))->not->toContain('juan@example.com')
            ->and($auditLog->metadata['pii_values_redacted'])->toBeTrue();

        Notification::assertSentOnDemand(
            InquiryReceivedNotification::class,
            fn (InquiryReceivedNotification $notification, array $channels, object $notifiable): bool => $notifiable->routeNotificationFor('mail') === 'juan@example.com',
        );

        Notification::assertSentTo($inquiryOfficer, StaffInquiryReceivedNotification::class);
        Notification::assertNotSentTo($contentAdmin, StaffInquiryReceivedNotification::class);
    }

    public function test_admin_policies_separate_content_staff_from_inquiry_data(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiry = Inquiry::factory()->create(['assigned_officer_id' => $inquiryOfficer->id]);

        expect($contentAdmin->can('viewAny', Inquiry::class))->toBeFalse()
            ->and($inquiryOfficer->can('view', $inquiry))->toBeTrue()
            ->and($inquiryOfficer->can('update', $inquiry))->toBeTrue()
            ->and($auditor->can('view', $inquiry))->toBeTrue()
            ->and($auditor->can('update', $inquiry))->toBeFalse();
    }

    public function test_filament_inquiry_resource_is_role_gated(): void
    {
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($inquiryOfficer)
            ->get(InquiryResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($contentAdmin)
            ->get(InquiryResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_expired_inquiries_are_anonymized_without_removing_reference_record(): void
    {
        $inquiry = Inquiry::factory()->expired()->create([
            'citizen_name' => 'Maria Santos',
            'citizen_email' => 'maria@example.com',
            'message_body' => 'Please route this inquiry before it expires.',
        ]);

        $this->artisan('inquiries:anonymize-expired')
            ->expectsOutput('Anonymized 1 expired inquiry record(s).')
            ->assertExitCode(0);

        $inquiry->refresh();
        $rawInquiry = DB::table('inquiries')->where('id', $inquiry->id)->first();

        expect($inquiry->reference_code)->toStartWith('INQ-')
            ->and($inquiry->status)->toBe(InquiryStatus::Anonymized)
            ->and($inquiry->citizen_name)->toBe('Anonymized citizen')
            ->and($inquiry->citizen_email)->toBeNull()
            ->and($inquiry->message_body)->toBe('Anonymized after retention period.')
            ->and($inquiry->anonymized_at)->not->toBeNull()
            ->and($rawInquiry->citizen_name)->not->toBe('Maria Santos')
            ->and(AuditLog::query()->where('event', 'inquiry.anonymized')->exists())->toBeTrue();
    }
}
