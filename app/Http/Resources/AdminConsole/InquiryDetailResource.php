<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Inquiry */
class InquiryDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $canViewCitizenDetails = $request->user()?->canManageInquiries() === true;

        return [
            ...(new InquirySummaryResource($this->resource))->toArray($request),
            'citizen' => $canViewCitizenDetails ? [
                'restricted' => false,
                'name' => $this->citizen_name,
                'contactPreference' => $this->contact_preference->value,
                'email' => $this->citizen_email,
                'mobile' => $this->citizen_mobile,
            ] : [
                'restricted' => true,
                'name' => null,
                'contactPreference' => null,
                'email' => null,
                'mobile' => null,
            ],
            'message' => $canViewCitizenDetails ? $this->message_body : null,
            'privacyConsentAt' => $this->privacy_consent_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'assignedAt' => $this->assigned_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'respondedAt' => $this->responded_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'firstRespondedAt' => $this->first_responded_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'resolvedAt' => $this->resolved_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'lastInternalActivityAt' => $this->last_internal_activity_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'closedAt' => $this->closed_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'closedReason' => $this->closed_reason,
            'anonymizedAt' => $this->anonymized_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'activities' => $this->chronologicalActivities->map(fn ($activity): array => [
                'id' => $activity->id,
                'type' => $activity->type->value,
                'typeLabel' => str($activity->type->value)->replace('_', ' ')->headline()->toString(),
                'body' => $canViewCitizenDetails ? $activity->body : null,
                'oldStatus' => $activity->old_status,
                'newStatus' => $activity->new_status,
                'actor' => $activity->actor?->name ?? 'System',
                'isInternalNote' => $activity->type->value === 'internal_note',
                'occurredAt' => $activity->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            ])->values(),
            'canViewCitizenDetails' => $canViewCitizenDetails,
        ];
    }
}
