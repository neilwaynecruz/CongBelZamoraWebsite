<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class RequestContentChangesRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['review_notes' => ['required', 'string', 'min:10', 'max:5000']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'review_notes.required' => 'Explain exactly what must be revised.',
            'review_notes.min' => 'Provide actionable revision guidance of at least 10 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'requestChanges';
    }
}
