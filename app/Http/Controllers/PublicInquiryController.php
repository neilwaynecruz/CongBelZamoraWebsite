<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicInquiryRequest;
use App\Services\SubmitInquiry;
use Illuminate\Http\RedirectResponse;

class PublicInquiryController extends Controller
{
    public function store(StorePublicInquiryRequest $request, SubmitInquiry $submitInquiry): RedirectResponse
    {
        $inquiry = $submitInquiry->handle($request->validated(), $request);

        return redirect()
            ->route('public.contact')
            ->with('inquiryConfirmation', [
                'referenceCode' => $inquiry->reference_code,
                'categoryLabel' => $inquiry->inquiry_category->label(),
                'contactPreferenceLabel' => $inquiry->contact_preference->label(),
                'submittedAt' => now('Asia/Manila')->format('F j, Y g:i A'),
                'retentionNotice' => 'Your inquiry is stored with encrypted contact details and message content. It will be retained for up to '.config('belzamora.privacy.inquiry_retention_days').' days, then anonymized under the approved privacy policy.',
            ]);
    }
}
