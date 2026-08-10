<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Inquiry */
class InquirySummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referenceCode' => $this->reference_code,
            'category' => [
                'value' => $this->inquiry_category->value,
                'label' => $this->inquiry_category->label(),
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'officeUnit' => $this->office_unit,
            'assigneeId' => $this->assigned_officer_id,
            'assignee' => $this->assignedOfficer?->name,
            'createdAt' => $this->created_at?->toIso8601String(),
            'createdAtLabel' => $this->created_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'firstResponseDueAt' => $this->first_response_due_at?->toIso8601String(),
            'firstResponseDueLabel' => $this->first_response_due_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'isOverdue' => $this->first_responded_at === null
                && $this->responded_at === null
                && ($this->first_response_due_at?->isPast() ?? false),
            'href' => route('admin-console.inquiries.show', $this->resource),
        ];
    }
}
