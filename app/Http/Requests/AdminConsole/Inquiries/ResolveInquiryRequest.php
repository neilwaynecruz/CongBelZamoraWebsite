<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;

class ResolveInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('resolve', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'note' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'note.required' => 'Record the outcome before resolving this inquiry.',
            'note.min' => 'Provide a more specific resolution summary.',
            'note.max' => 'Keep the resolution summary within 5,000 characters.',
        ];
    }
}
