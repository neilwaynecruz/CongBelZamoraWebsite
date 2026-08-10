<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;

class ReopenInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('reopen', $inquiry) === true;
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
            'note.required' => 'Explain why this inquiry needs another review cycle.',
            'note.min' => 'Provide a more specific reason for reopening the inquiry.',
            'note.max' => 'Keep the reopen reason within 5,000 characters.',
        ];
    }
}
