<?php

namespace App\Http\Requests\AdminConsole\ContentApprovals;

use Illuminate\Database\Query\Builder;
use Illuminate\Validation\Rule;

class AssignContentReviewerRequest extends ContentApprovalActionRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        $roles = config('belzamora.governance.workflow_roles.review', []);

        return [
            'reviewer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->whereIn('admin_role', is_array($roles) ? $roles : [])),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'reviewer_id.required' => 'Select the staff member accountable for this review.',
            'reviewer_id.exists' => 'Select an active staff member authorized to review public content.',
        ];
    }

    protected function ability(): string
    {
        return 'assignReviewer';
    }
}
