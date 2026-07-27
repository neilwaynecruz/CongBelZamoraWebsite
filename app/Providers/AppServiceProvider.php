<?php

namespace App\Providers;

use App\Events\ContentApprovalDecided;
use App\Events\ContentApprovalSubmitted;
use App\Events\ContentPublished;
use App\Events\ContentScheduled;
use App\Events\InquiryAssigned;
use App\Events\InquirySubmitted;
use App\Events\SourceReviewDue;
use App\Listeners\InvalidateAdminDashboardOnInquiry;
use App\Listeners\InvalidatePublicContentCache;
use App\Listeners\NotifyContentApprovalDecided;
use App\Listeners\NotifyContentApprovalSubmitted;
use App\Listeners\NotifyInquiryAssigned;
use App\Listeners\RecordSourceReviewDue;
use App\Models\LegislativeRecord;
use App\Models\LegislativeSourceHistory;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\ProgramRequirement;
use App\Models\ProgramUpdate;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\ServiceGuideStep;
use App\Models\SourceRecord;
use App\Observers\CivicContentLifecycleObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureDomainEvents();
        $this->configureCivicContentObservers();
    }

    protected function configureDomainEvents(): void
    {
        Event::listen(ContentPublished::class, InvalidatePublicContentCache::class);
        Event::listen(ContentScheduled::class, InvalidatePublicContentCache::class);
        Event::listen(InquirySubmitted::class, InvalidateAdminDashboardOnInquiry::class);
        Event::listen(SourceReviewDue::class, RecordSourceReviewDue::class);
        Event::listen(InquiryAssigned::class, NotifyInquiryAssigned::class);
        Event::listen(ContentApprovalSubmitted::class, NotifyContentApprovalSubmitted::class);
        Event::listen(ContentApprovalDecided::class, NotifyContentApprovalDecided::class);
    }

    protected function configureCivicContentObservers(): void
    {
        foreach ([
            PublicPage::class,
            Program::class,
            ProgramRequirement::class,
            ProgramUpdate::class,
            ServiceGuide::class,
            ServiceGuideStep::class,
            LegislativeRecord::class,
            LegislativeSourceHistory::class,
            Project::class,
            ProjectEvidence::class,
            NewsRecord::class,
            NewsMediaAsset::class,
            SourceRecord::class,
        ] as $model) {
            $model::observe(CivicContentLifecycleObserver::class);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
