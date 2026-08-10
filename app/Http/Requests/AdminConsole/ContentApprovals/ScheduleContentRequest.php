<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

class ScheduleContentRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['scheduled_for' => ['required', 'date', 'after:now']];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'scheduled_for.required' => 'Choose the approved publication date and time.',
            'scheduled_for.after' => 'Scheduled publication must be in the future.',
        ];
    }

    protected function ability(): string
    {
        return 'schedule';
    }
}
