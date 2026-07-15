<?php

namespace App\Services;

use App\Enums\ContactPreference;
use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use App\Notifications\InquiryReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SubmitInquiry
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function handle(array $validated, Request $request): Inquiry
    {
        $inquiry = DB::transaction(function () use ($validated, $request): Inquiry {
            $inquiry = Inquiry::query()->create([
                'reference_code' => $this->generateReferenceCode(),
                'citizen_name' => (string) $validated['citizen_name'],
                'contact_preference' => (string) $validated['contact_preference'],
                'citizen_email' => $validated['citizen_email'] ?? null,
                'citizen_mobile' => $validated['citizen_mobile'] ?? null,
                'inquiry_category' => (string) $validated['inquiry_category'],
                'message_body' => (string) $validated['message_body'],
                'status' => InquiryStatus::Pending,
                'privacy_consent_at' => now(),
                'retention_expires_at' => now()->addDays((int) config('belzamora.privacy.inquiry_retention_days')),
                'metadata' => [
                    'form_version' => 'phase_7_mvp',
                    'privacy_notice_version' => '2026-07-15',
                ],
            ]);

            $this->auditLog->record(
                event: 'inquiry.submitted',
                auditable: $inquiry,
                request: $request,
                newValues: [
                    'reference_code' => $inquiry->reference_code,
                    'inquiry_category' => $inquiry->inquiry_category->value,
                    'contact_preference' => $inquiry->contact_preference->value,
                    'status' => $inquiry->status->value,
                    'retention_expires_at' => (string) $inquiry->retention_expires_at,
                ],
                metadata: [
                    'contains_sensitive_upload' => false,
                    'pii_values_redacted' => true,
                ],
            );

            return $inquiry;
        });

        if (
            $inquiry->contact_preference === ContactPreference::Email
            && $inquiry->citizen_email !== null
        ) {
            Notification::route('mail', $inquiry->citizen_email)
                ->notify(new InquiryReceivedNotification($inquiry));
        }

        return $inquiry;
    }

    protected function generateReferenceCode(): string
    {
        do {
            $referenceCode = 'INQ-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (Inquiry::query()->where('reference_code', $referenceCode)->exists());

        return $referenceCode;
    }
}
