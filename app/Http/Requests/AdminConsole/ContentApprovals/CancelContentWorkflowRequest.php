<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class CancelContentWorkflowRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:10', 'max:2000']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason.required' => 'Explain why this review or publication schedule is being cancelled.',
            'reason.min' => 'Provide a cancellation reason of at least 10 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'cancel';
    }
}
