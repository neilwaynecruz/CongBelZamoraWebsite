<?php

use App\Http\Controllers\AdminConsole\AdminConsoleAuthController;
use App\Http\Controllers\AdminConsole\ContentApprovalController;
use App\Http\Controllers\AdminConsole\ContentApprovalWorkflowController;
use App\Http\Controllers\AdminConsole\ContentLifecycleController;
use App\Http\Controllers\AdminConsole\DashboardController;
use App\Http\Controllers\AdminConsole\DesignSystemController;
use App\Http\Controllers\AdminConsole\InquiryController;
use App\Http\Controllers\AdminConsole\InquiryWorkflowController;
use App\Http\Controllers\AdminConsole\NewsRecordController;
use App\Http\Controllers\AdminConsole\ProgramController;
use App\Http\Controllers\AdminConsole\ProjectController;
use App\Http\Controllers\AdminConsole\SearchController;
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

        Route::get('/news', [NewsRecordController::class, 'index'])->name('news.index');
        Route::get('/news/create', [NewsRecordController::class, 'create'])->name('news.create');
        Route::post('/news', [NewsRecordController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('news.store');
        Route::get('/news/{newsRecord}/edit', [NewsRecordController::class, 'edit'])->name('news.edit');
        Route::put('/news/{newsRecord}', [NewsRecordController::class, 'update'])
            ->middleware('throttle:30,1')
            ->name('news.update');
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
