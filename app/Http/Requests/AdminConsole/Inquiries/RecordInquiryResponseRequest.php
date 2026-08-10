<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordInquiryResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('respond', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'template_key' => ['nullable', 'string', Rule::in(array_keys($this->templates()))],
            'note' => ['required', 'string', 'min:5', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'template_key.in' => 'Select an approved District Office response template.',
            'note.required' => 'Record what response was delivered through the approved office channel.',
            'note.min' => 'Provide a more specific response summary.',
            'note.max' => 'Keep the response summary within 5,000 characters.',
        ];
    }

    /** @return array<string, mixed> */
    private function templates(): array
    {
        $templates = config('belzamora.inquiries.canned_replies', []);

        return is_array($templates) ? $templates : [];
    }
}
