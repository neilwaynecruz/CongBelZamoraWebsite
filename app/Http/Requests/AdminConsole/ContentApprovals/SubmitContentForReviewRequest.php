<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class SubmitContentForReviewRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['change_summary' => ['required', 'string', 'min:10', 'max:2000']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'change_summary.required' => 'Describe what changed before submitting this revision.',
            'change_summary.min' => 'Provide a more specific change summary of at least 10 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'submit';
    }
}
