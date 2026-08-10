<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddInquiryInternalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('addInternalNote', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'template_key' => ['nullable', 'string', Rule::in(array_keys($this->templates()))],
            'note' => ['required', 'string', 'min:3', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'template_key.in' => 'Select an approved District Office response template.',
            'note.required' => 'Enter the internal note that should appear in the handling timeline.',
            'note.min' => 'Provide a more specific internal note.',
            'note.max' => 'Keep the internal note within 5,000 characters.',
        ];
    }

    /** @return array<string, mixed> */
    private function templates(): array
    {
        $templates = config('belzamora.inquiries.canned_replies', []);

        return is_array($templates) ? $templates : [];
    }
}
