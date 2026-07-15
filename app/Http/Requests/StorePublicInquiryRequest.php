<?php

namespace App\Http\Requests;

use App\Enums\ContactPreference;
use App\Enums\InquiryCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'citizen_name' => ['required', 'string', 'max:100'],
            'contact_preference' => ['required', Rule::enum(ContactPreference::class)],
            'citizen_email' => ['nullable', 'required_if:contact_preference,email', 'email:rfc', 'max:255'],
            'citizen_mobile' => ['nullable', 'required_if:contact_preference,sms', 'regex:/^\+639[0-9]{9}$/'],
            'inquiry_category' => ['required', Rule::enum(InquiryCategory::class)],
            'message_body' => ['required', 'string', 'min:20', 'max:1000'],
            'privacy_consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'citizen_name.required' => 'Ang pangalan ay kailangan / The name field is required.',
            'contact_preference.required' => 'Pumili ng contact preference / Please choose a contact preference.',
            'citizen_email.required_if' => 'Kailangan ang email kung email ang pinili / Email is required when email is selected.',
            'citizen_email.email' => 'Maglagay ng valid email address / Please enter a valid email address.',
            'citizen_mobile.required_if' => 'Kailangan ang mobile number kung SMS ang pinili / Mobile number is required when SMS is selected.',
            'citizen_mobile.regex' => 'Gamitin ang format na +639XXXXXXXXX / Use the format +639XXXXXXXXX.',
            'inquiry_category.required' => 'Pumili ng kategorya / Please choose a category.',
            'message_body.required' => 'Maglagay ng maikling mensahe / Please enter a short message.',
            'message_body.min' => 'Dagdagan pa ang detalye nang kaunti / Please provide at least 20 characters.',
            'message_body.max' => 'Hanggang 1000 characters lamang / Please keep the message within 1000 characters.',
            'privacy_consent.accepted' => 'Kailangan ang privacy acknowledgement / Privacy acknowledgement is required.',
        ];
    }
}
