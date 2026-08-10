<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

use Illuminate\Validation\Rule;

class ArchiveContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $reasons = config('belzamora.governance.content_approval.archive_reasons', []);

        return [
            'reason_key' => ['required', 'string', Rule::in(array_keys(is_array($reasons) ? $reasons : []))],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reason_key.required' => 'Select an official archive or unpublish reason.',
            'reason_key.in' => 'Select one of the configured governance reasons.',
            'note.max' => 'Keep the supporting note within 2,000 characters.',
        ];
    }

    protected function ability(): string
    {
        return $this->routeIs('admin-console.content-approvals.unpublish') ? 'unpublish' : 'archive';
    }
}
