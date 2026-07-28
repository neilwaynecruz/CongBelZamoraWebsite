<?php

use App\Jobs\SendOverdueInquiryReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inquiries:anonymize-expired')->dailyAt('02:30');
Schedule::job(new SendOverdueInquiryReminders)->hourly();
Schedule::command('content:flag-stale')->dailyAt('03:00');
Schedule::command('content:publish-scheduled')->everyFifteenMinutes();
Schedule::command('queue:check-failed --threshold=1')->hourly();
Schedule::command('launch:readiness --allow-pending-approval')->dailyAt('03:30');
