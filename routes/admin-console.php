<?php

use App\Http\Controllers\AdminConsole\AdminConsoleAuthController;
use App\Http\Controllers\AdminConsole\ContentApprovalController;
use App\Http\Controllers\AdminConsole\ContentApprovalWorkflowController;
use App\Http\Controllers\AdminConsole\DashboardController;
use App\Http\Controllers\AdminConsole\DesignSystemController;
use App\Http\Controllers\AdminConsole\InquiryController;
use App\Http\Controllers\AdminConsole\InquiryWorkflowController;
use App\Http\Controllers\AdminConsole\ProgramController;
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
        Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

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
