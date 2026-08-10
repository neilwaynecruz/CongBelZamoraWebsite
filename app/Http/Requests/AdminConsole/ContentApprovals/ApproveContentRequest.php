<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class ApproveContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'change_summary' => ['required', 'string', 'min:10', 'max:2000'],
            'review_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'change_summary.required' => 'Record the approved change summary.',
            'change_summary.min' => 'Provide a more specific approval summary of at least 10 characters.',
            'review_notes.max' => 'Keep review notes within 5,000 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'approve';
    }
}
