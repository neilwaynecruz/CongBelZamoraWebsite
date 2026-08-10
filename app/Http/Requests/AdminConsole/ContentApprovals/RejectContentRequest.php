<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class RejectContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['rejection_reason' => ['required', 'string', 'min:10', 'max:5000']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Record the official reason for rejecting this revision.',
            'rejection_reason.min' => 'Provide a specific rejection reason of at least 10 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'reject';
    }
}
