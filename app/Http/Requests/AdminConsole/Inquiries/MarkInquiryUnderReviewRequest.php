<?php

namespace App\Http\Requests\AdminConsole\Inquiries;

use App\Models\Inquiry;
use Illuminate\Foundation\Http\FormRequest;

class MarkInquiryUnderReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inquiry = $this->route('inquiry');

        return $inquiry instanceof Inquiry
            && $this->user()?->can('markUnderReview', $inquiry) === true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
