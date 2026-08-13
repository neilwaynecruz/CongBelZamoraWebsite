<?php

use App\Http\Controllers\AdminConsole\AdminConsoleAuthController;
use App\Http\Controllers\AdminConsole\AuditLogController;
use App\Http\Controllers\AdminConsole\ContentApprovalController;
use App\Http\Controllers\AdminConsole\ContentApprovalWorkflowController;
use App\Http\Controllers\AdminConsole\ContentLifecycleController;
use App\Http\Controllers\AdminConsole\DashboardController;
use App\Http\Controllers\AdminConsole\DesignSystemController;
use App\Http\Controllers\AdminConsole\InquiryController;
use App\Http\Controllers\AdminConsole\InquiryWorkflowController;
use App\Http\Controllers\AdminConsole\LegislativeRecordController;
use App\Http\Controllers\AdminConsole\NewsMediaManagerController;
use App\Http\Controllers\AdminConsole\NewsRecordController;
use App\Http\Controllers\AdminConsole\ProgramController;
use App\Http\Controllers\AdminConsole\ProjectController;
use App\Http\Controllers\AdminConsole\ProjectEvidenceManagerController;
use App\Http\Controllers\AdminConsole\PublicPageController;
use App\Http\Controllers\AdminConsole\ReferenceDataController;
use App\Http\Controllers\AdminConsole\ReportsController;
use App\Http\Controllers\AdminConsole\SearchController;
use App\Http\Controllers\AdminConsole\SettingsController;
use App\Http\Controllers\AdminConsole\SourceRecordController;
use App\Http\Controllers\AdminConsole\StaffUserController;
use App\Http\Middleware\EnsureAdminConsoleAccess;
use Illuminate\Support\Facades\Route;

Route::prefix('admin-console')->name('admin-console.')->group(function (): void {
    Route::get('/login', AdminConsoleAuthController::class)
        ->name('login');

    Route::middleware([EnsureAdminConsoleAccess::class, 'auth', 'verified'])->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/search', SearchController::class)
            ->middleware('throttle:30,1')
            ->name('search');

        Route::get('/settings', SettingsController::class)->name('settings');
        Route::get('/reports', ReportsController::class)->name('reports');
        Route::get('/source-records', [SourceRecordController::class, 'index'])->name('source-records.index');
        Route::get('/source-records/create', [SourceRecordController::class, 'create'])->name('source-records.create');
        Route::post('/source-records', [SourceRecordController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('source-records.store');
        Route::get('/source-records/{sourceRecord}/edit', [SourceRecordController::class, 'edit'])->name('source-records.edit');
        Route::put('/source-records/{sourceRecord}', [SourceRecordController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('source-records.update');
        Route::post('/source-records/{sourceRecord}/archive', [SourceRecordController::class, 'archive'])
            ->middleware('throttle:20,1')
            ->name('source-records.archive');
        Route::post('/source-records/{sourceRecord}/restore', [SourceRecordController::class, 'restore'])
            ->middleware('throttle:20,1')
            ->name('source-records.restore');
        Route::get('/source-records/{sourceRecord}', [SourceRecordController::class, 'show'])->name('source-records.show');
        Route::get('/reference-data/{type}', [ReferenceDataController::class, 'index'])->name('reference-data.index');
        Route::get('/reference-data/{type}/create', [ReferenceDataController::class, 'create'])->name('reference-data.create');
        Route::post('/reference-data/{type}', [ReferenceDataController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('reference-data.store');
        Route::get('/reference-data/{type}/{record}/edit', [ReferenceDataController::class, 'edit'])->name('reference-data.edit');
        Route::put('/reference-data/{type}/{record}', [ReferenceDataController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('reference-data.update');
        Route::get('/reference-data/{type}/{record}', [ReferenceDataController::class, 'show'])->name('reference-data.show');
        Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/audit/{auditLog}', [AuditLogController::class, 'show'])->name('audit.show');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
        Route::prefix('/inquiries/{inquiry}')
            ->name('inquiries.')
            ->middleware('throttle:60,1')
            ->group(function (): void {
                Route::post('/assignment', [InquiryWorkflowController::class, 'assign'])->name('assign');
                Route::post('/review', [InquiryWorkflowController::class, 'markUnderReview'])->name('mark-under-review');
                Route::post('/notes', [InquiryWorkflowController::class, 'addInternalNote'])->name('notes.store');
                Route::post('/responses', [InquiryWorkflowController::class, 'recordResponse'])->name('responses.store');
                Route::post('/resolve', [InquiryWorkflowController::class, 'resolve'])->name('resolve');
                Route::post('/close', [InquiryWorkflowController::class, 'close'])->name('close');
                Route::post('/reopen', [InquiryWorkflowController::class, 'reopen'])->name('reopen');
            });

        Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
        Route::get('/programs/create', [ProgramController::class, 'create'])->name('programs.create');
        Route::post('/programs', [ProgramController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('programs.store');
        Route::get('/programs/{program}/edit', [ProgramController::class, 'edit'])->name('programs.edit');
        Route::put('/programs/{program}', [ProgramController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('programs.update');
        Route::post('/programs/{program}/archive', [ContentLifecycleController::class, 'archiveProgram'])
            ->middleware('throttle:20,1')
            ->name('programs.archive');
        Route::post('/programs/{program}/restore', [ContentLifecycleController::class, 'restoreProgram'])
            ->middleware('throttle:20,1')
            ->name('programs.restore');
        Route::delete('/programs/{program}', [ContentLifecycleController::class, 'deleteProgram'])
            ->middleware('throttle:10,1')
            ->name('programs.delete');
        Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('projects.store');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('projects.update');
        Route::get('/projects/{project}/evidence', [ProjectEvidenceManagerController::class, 'index'])
            ->name('projects.evidence');
        Route::post('/projects/{project}/evidence', [ProjectEvidenceManagerController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('projects.evidence.store');
        Route::put('/projects/{project}/evidence/{projectEvidence}', [ProjectEvidenceManagerController::class, 'update'])
            ->middleware('throttle:20,1')
            ->name('projects.evidence.update');
        Route::delete('/projects/{project}/evidence/{projectEvidence}', [ProjectEvidenceManagerController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('projects.evidence.delete');
        Route::post('/projects/{project}/archive', [ContentLifecycleController::class, 'archiveProject'])
            ->middleware('throttle:20,1')
            ->name('projects.archive');
        Route::post('/projects/{project}/restore', [ContentLifecycleController::class, 'restoreProject'])
            ->middleware('throttle:20,1')
            ->name('projects.restore');
        Route::delete('/projects/{project}', [ContentLifecycleController::class, 'deleteProject'])
            ->middleware('throttle:10,1')
            ->name('projects.delete');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

        Route::get('/legislation', [LegislativeRecordController::class, 'index'])->name('legislation.index');
        Route::get('/legislation/create', [LegislativeRecordController::class, 'create'])->name('legislation.create');
        Route::post('/legislation', [LegislativeRecordController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('legislation.store');
        Route::get('/legislation/{legislativeRecord}/edit', [LegislativeRecordController::class, 'edit'])->name('legislation.edit');
        Route::put('/legislation/{legislativeRecord}', [LegislativeRecordController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('legislation.update');
        Route::post('/legislation/{legislativeRecord}/archive', [ContentLifecycleController::class, 'archiveLegislation'])
            ->middleware('throttle:20,1')
            ->name('legislation.archive');
        Route::post('/legislation/{legislativeRecord}/restore', [ContentLifecycleController::class, 'restoreLegislation'])
            ->middleware('throttle:20,1')
            ->name('legislation.restore');
        Route::delete('/legislation/{legislativeRecord}', [ContentLifecycleController::class, 'deleteLegislation'])
            ->middleware('throttle:10,1')
            ->name('legislation.delete');
        Route::get('/legislation/{legislativeRecord}', [LegislativeRecordController::class, 'show'])->name('legislation.show');

        Route::get('/news', [NewsRecordController::class, 'index'])->name('news.index');
        Route::get('/news/create', [NewsRecordController::class, 'create'])->name('news.create');
        Route::post('/news', [NewsRecordController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('news.store');
        Route::get('/news/{newsRecord}/edit', [NewsRecordController::class, 'edit'])->name('news.edit');
        Route::put('/news/{newsRecord}', [NewsRecordController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('news.update');
        Route::get('/news/{newsRecord}/media', [NewsMediaManagerController::class, 'index'])
            ->name('news.media');
        Route::post('/news/{newsRecord}/media', [NewsMediaManagerController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('news.media.store');
        Route::put('/news/{newsRecord}/media/{newsMediaAsset}', [NewsMediaManagerController::class, 'update'])
            ->middleware('throttle:20,1')
            ->name('news.media.update');
        Route::delete('/news/{newsRecord}/media/{newsMediaAsset}', [NewsMediaManagerController::class, 'destroy'])
            ->middleware('throttle:10,1')
            ->name('news.media.delete');
        Route::post('/news/{newsRecord}/archive', [ContentLifecycleController::class, 'archiveNews'])
            ->middleware('throttle:20,1')
            ->name('news.archive');
        Route::post('/news/{newsRecord}/restore', [ContentLifecycleController::class, 'restoreNews'])
            ->middleware('throttle:20,1')
            ->name('news.restore');
        Route::delete('/news/{newsRecord}', [ContentLifecycleController::class, 'deleteNews'])
            ->middleware('throttle:10,1')
            ->name('news.delete');
        Route::get('/news/{newsRecord}', [NewsRecordController::class, 'show'])->name('news.show');

        Route::get('/public-pages', [PublicPageController::class, 'index'])->name('public-pages.index');
        Route::get('/public-pages/create', [PublicPageController::class, 'create'])->name('public-pages.create');
        Route::post('/public-pages', [PublicPageController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('public-pages.store');
        Route::get('/public-pages/{publicPage}/edit', [PublicPageController::class, 'edit'])->name('public-pages.edit');
        Route::put('/public-pages/{publicPage}', [PublicPageController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('public-pages.update');
        Route::post('/public-pages/{publicPage}/archive', [ContentLifecycleController::class, 'archivePublicPage'])
            ->middleware('throttle:20,1')
            ->name('public-pages.archive');
        Route::post('/public-pages/{publicPage}/restore', [ContentLifecycleController::class, 'restorePublicPage'])
            ->middleware('throttle:20,1')
            ->name('public-pages.restore');
        Route::delete('/public-pages/{publicPage}', [ContentLifecycleController::class, 'deletePublicPage'])
            ->middleware('throttle:10,1')
            ->name('public-pages.delete');
        Route::get('/public-pages/{publicPage}', [PublicPageController::class, 'show'])->name('public-pages.show');

        Route::get('/users', [StaffUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [StaffUserController::class, 'create'])->name('users.create');
        Route::post('/users', [StaffUserController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('users.store');
        Route::get('/users/{user}/edit', [StaffUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [StaffUserController::class, 'update'])
            ->middleware('throttle:20,1')
            ->name('users.update');
        Route::post('/users/{user}/password-reset', [StaffUserController::class, 'sendPasswordReset'])
            ->middleware('throttle:10,1')
            ->name('users.password-reset');
        Route::get('/users/{user}', [StaffUserController::class, 'show'])->name('users.show');

        Route::get('/content-approvals', [ContentApprovalController::class, 'index'])
            ->name('content-approvals.index');
        Route::get('/content-approvals/{contentApproval}', [ContentApprovalController::class, 'show'])
            ->name('content-approvals.show');
        Route::prefix('/content-approvals/{contentApproval}')
            ->name('content-approvals.')
            ->middleware('throttle:40,1')
            ->group(function (): void {
                Route::post('/reviewer', [ContentApprovalWorkflowController::class, 'assignReviewer'])->name('reviewer.assign');
                Route::post('/submit', [ContentApprovalWorkflowController::class, 'submit'])->name('submit');
                Route::post('/approve', [ContentApprovalWorkflowController::class, 'approve'])->name('approve');
                Route::post('/request-changes', [ContentApprovalWorkflowController::class, 'requestChanges'])->name('request-changes');
                Route::post('/reject', [ContentApprovalWorkflowController::class, 'reject'])->name('reject');
                Route::post('/cancel', [ContentApprovalWorkflowController::class, 'cancel'])->name('cancel');
                Route::post('/schedule', [ContentApprovalWorkflowController::class, 'schedule'])->name('schedule');
                Route::post('/publish', [ContentApprovalWorkflowController::class, 'publish'])->name('publish');
                Route::post('/unpublish', [ContentApprovalWorkflowController::class, 'unpublish'])->name('unpublish');
                Route::post('/archive', [ContentApprovalWorkflowController::class, 'archive'])->name('archive');
                Route::post('/rollback', [ContentApprovalWorkflowController::class, 'rollback'])->name('rollback');
            });

        Route::get('/design-system', DesignSystemController::class)->name('design-system');
    });
});
