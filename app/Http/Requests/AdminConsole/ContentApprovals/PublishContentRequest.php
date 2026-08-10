<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class PublishContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [];
    }

    protected function ability(): string
    {
        return 'publish';
    }
}
