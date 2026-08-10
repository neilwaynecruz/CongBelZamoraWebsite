<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\ContentSnapshotType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\ContentApprovals\ApproveContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\ArchiveContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\AssignContentReviewerRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\CancelContentWorkflowRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\PublishContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\RejectContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\RequestContentChangesRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\RollbackContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\ScheduleContentRequest;
use App\Http\Requests\AdminConsole\ContentApprovals\SubmitContentForReviewRequest;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\User;
use App\Services\ContentApprovalWorkflowService;
use App\Services\ContentPublicationService;
use App\Support\ContentGovernanceException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ContentApprovalWorkflowController extends Controller
{
    public function __construct(
        private readonly ContentApprovalWorkflowService $workflow,
        private readonly ContentPublicationService $publication,
    ) {}

    public function assignReviewer(AssignContentReviewerRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $reviewer = User::query()->findOrFail((int) $request->validated('reviewer_id'));

        $this->workflow->assignReviewer($contentApproval, $reviewer, $this->actor($request));

        return $this->success($contentApproval, 'Review ownership updated.');
    }

    public function submit(SubmitContentForReviewRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->workflow->submitForReview(
            $contentApproval,
            $this->actor($request),
            (string) $request->validated('change_summary'),
        );

        return $this->success($contentApproval, 'Revision submitted to the governed review queue.');
    }

    public function approve(ApproveContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $validated = $request->validated();

        $this->workflow->approve(
            $contentApproval,
            $this->actor($request),
            (string) $validated['change_summary'],
            filled($validated['review_notes'] ?? null) ? (string) $validated['review_notes'] : null,
        );

        return $this->success($contentApproval, 'Revision approved and captured as an approved snapshot.');
    }

    public function requestChanges(RequestContentChangesRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->workflow->requestChanges(
            $contentApproval,
            $this->actor($request),
            (string) $request->validated('review_notes'),
        );

        return $this->success($contentApproval, 'Revision returned with actionable review guidance.');
    }

    public function reject(RejectContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->workflow->reject(
            $contentApproval,
            $this->actor($request),
            (string) $request->validated('rejection_reason'),
        );

        return $this->success($contentApproval, 'Revision rejected with an auditable reason.');
    }

    public function cancel(CancelContentWorkflowRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->workflow->cancel(
            $contentApproval,
            $this->actor($request),
            (string) $request->validated('reason'),
        );

        return $this->success($contentApproval, 'The active review or publication schedule was cancelled.');
    }

    public function schedule(ScheduleContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->runGovernedAction(fn () => $this->publication->schedule(
            $contentApproval,
            (string) $request->validated('scheduled_for'),
            $this->actor($request),
        ));

        return $this->success($contentApproval, 'Approved content scheduled for publication.');
    }

    public function publish(PublishContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->runGovernedAction(fn () => $this->publication->publish(
            $contentApproval,
            $this->actor($request),
        ));

        return $this->success($contentApproval, 'Content published after passing governance checks.');
    }

    public function unpublish(ArchiveContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->publication->unpublish(
            $contentApproval,
            $this->archiveReason($request),
            $this->actor($request),
            ContentSnapshotType::Unpublished,
        );

        return $this->success($contentApproval, 'Content removed from public publication with a recorded reason.');
    }

    public function archive(ArchiveContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $this->publication->archive(
            $contentApproval,
            $this->archiveReason($request),
            $this->actor($request),
        );

        return $this->success($contentApproval, 'Content archived with a recorded governance reason.');
    }

    public function rollback(RollbackContentRequest $request, ContentApproval $contentApproval): RedirectResponse
    {
        $snapshot = ContentSnapshot::query()
            ->where('content_approval_id', $contentApproval->id)
            ->findOrFail((int) $request->validated('snapshot_id'));

        $this->runGovernedAction(fn () => $this->publication->rollback(
            $contentApproval,
            $snapshot,
            $this->actor($request),
            (string) $request->validated('reason'),
        ));

        return $this->success($contentApproval, 'Approved content restored from the selected snapshot.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->user();

        abort_unless($actor instanceof User, 403);

        return $actor;
    }

    private function archiveReason(ArchiveContentRequest $request): string
    {
        $validated = $request->validated();
        $options = config('belzamora.governance.content_approval.archive_reasons', []);
        $label = (string) data_get(is_array($options) ? $options : [], (string) $validated['reason_key'], $validated['reason_key']);

        return filled($validated['note'] ?? null)
            ? $label.': '.(string) $validated['note']
            : $label;
    }

    private function runGovernedAction(callable $action): void
    {
        try {
            $action();
        } catch (ContentGovernanceException $exception) {
            throw ValidationException::withMessages([
                'action' => implode(' ', $exception->issues()),
            ]);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'action' => $exception->getMessage(),
            ]);
        }
    }

    private function success(ContentApproval $contentApproval, string $message): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => $message,
        ]);

        return redirect()->route('admin-console.content-approvals.show', $contentApproval);
    }
}
