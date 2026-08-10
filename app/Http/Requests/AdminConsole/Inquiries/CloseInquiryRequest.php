<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CloseInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('close', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'closed_reason' => ['required', 'string', Rule::in(array_keys($this->closureReasons()))],
            'note' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'closed_reason.required' => 'Select the official reason for closing this inquiry.',
            'closed_reason.in' => 'Select an approved closure reason.',
            'note.max' => 'Keep the closure note within 5,000 characters.',
        ];
    }

    /** @return array<string, mixed> */
    private function closureReasons(): array
    {
        $reasons = config('belzamora.inquiries.case_management.closure_reasons', []);

        return is_array($reasons) ? $reasons : [];
    }
}
