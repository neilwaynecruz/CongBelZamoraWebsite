<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class RollbackContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'snapshot_id' => ['required', 'integer', 'exists:content_snapshots,id'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'snapshot_id.required' => 'Select the approved or published snapshot to restore.',
            'reason.required' => 'Record why this rollback is necessary.',
            'reason.min' => 'Provide a rollback reason of at least 10 characters.',
        ];
    }

    protected function ability(): string
    {
        return 'rollback';
    }
}
