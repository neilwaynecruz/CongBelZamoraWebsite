<?php

namespace App\Listeners;

use App\Events\InquirySubmitted;
use App\Services\PublicCacheInvalidator;

class InvalidateAdminDashboardOnInquiry
{
    public function __construct(private readonly PublicCacheInvalidator $invalidator) {}

    public function handle(InquirySubmitted $event): void
    {
        $this->invalidator->forgetAdminDashboardCounts();
    }
}
