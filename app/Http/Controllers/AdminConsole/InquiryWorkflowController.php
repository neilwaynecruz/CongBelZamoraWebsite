<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Inquiries\AddInquiryInternalNoteRequest;
use App\Http\Requests\AdminConsole\Inquiries\AssignInquiryRequest;
use App\Http\Requests\AdminConsole\Inquiries\CloseInquiryRequest;
use App\Http\Requests\AdminConsole\Inquiries\MarkInquiryUnderReviewRequest;
use App\Http\Requests\AdminConsole\Inquiries\RecordInquiryResponseRequest;
use App\Http\Requests\AdminConsole\Inquiries\ReopenInquiryRequest;
use App\Http\Requests\AdminConsole\Inquiries\ResolveInquiryRequest;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\InquiryCaseManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InquiryWorkflowController extends Controller
{
    public function __construct(private readonly InquiryCaseManagementService $caseManagement) {}

    public function assign(AssignInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();
        $assignee = User::query()->findOrFail((int) $validated['assigned_officer_id']);

        $this->caseManagement->assign(
            inquiry: $inquiry,
            assignee: $assignee,
            actor: $this->actor($request),
            note: filled($validated['note'] ?? null) ? (string) $validated['note'] : null,
        );

        return $this->success($inquiry, 'Inquiry ownership updated.');
    }

    public function markUnderReview(MarkInquiryUnderReviewRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->markUnderReview(
            inquiry: $inquiry,
            actor: $this->actor($request),
            note: filled($validated['note'] ?? null) ? (string) $validated['note'] : null,
        );

        return $this->success($inquiry, 'Inquiry moved into active review.');
    }

    public function addInternalNote(AddInquiryInternalNoteRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->addInternalNote(
            inquiry: $inquiry,
            actor: $this->actor($request),
            note: (string) $validated['note'],
            templateKey: filled($validated['template_key'] ?? null) ? (string) $validated['template_key'] : null,
        );

        return $this->success($inquiry, 'Internal note added to the case timeline.');
    }

    public function recordResponse(RecordInquiryResponseRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->markResponded(
            inquiry: $inquiry,
            actor: $this->actor($request),
            note: (string) $validated['note'],
            templateKey: filled($validated['template_key'] ?? null) ? (string) $validated['template_key'] : null,
        );

        return $this->success($inquiry, 'Response milestone recorded.');
    }

    public function resolve(ResolveInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->resolve(
            inquiry: $inquiry,
            actor: $this->actor($request),
            note: (string) $validated['note'],
        );

        return $this->success($inquiry, 'Inquiry marked as resolved.');
    }

    public function close(CloseInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->close(
            inquiry: $inquiry,
            actor: $this->actor($request),
            reason: (string) $validated['closed_reason'],
            note: filled($validated['note'] ?? null) ? (string) $validated['note'] : null,
        );

        return $this->success($inquiry, 'Inquiry closed with an official closure reason.');
    }

    public function reopen(ReopenInquiryRequest $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validated();

        $this->caseManagement->reopen(
            inquiry: $inquiry,
            actor: $this->actor($request),
            note: (string) $validated['note'],
        );

        return $this->success($inquiry, 'Inquiry reopened for follow-up review.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        return $actor;
    }

    private function success(Inquiry $inquiry, string $message): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return redirect()->route('admin-console.inquiries.show', $inquiry);
    }
}
