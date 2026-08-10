<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Enums\AdminRole;
use App\Models\Inquiry;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('assign', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'assigned_officer_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn (Builder $query): Builder => $query
                    ->where('is_active', true)
                    ->whereIn('admin_role', [
                        AdminRole::SuperAdministrator->value,
                        AdminRole::InquiryOfficer->value,
                    ])),
            ],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'assigned_officer_id.required' => 'Select the officer who will own this inquiry.',
            'assigned_officer_id.exists' => 'Select an active staff member authorized to manage inquiries.',
            'note.max' => 'Keep the assignment note within 2,000 characters.',
        ];
    }
}
