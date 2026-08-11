<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\ContentLifecycle\ArchiveContentRecordRequest;
use App\Http\Requests\AdminConsole\ContentLifecycle\DeleteContentRecordRequest;
use App\Http\Requests\AdminConsole\ContentLifecycle\RestoreContentRecordRequest;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\User;
use App\Services\AdminCivicContentLifecycleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ContentLifecycleController extends Controller
{
    public function archiveProgram(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $program,
    ): RedirectResponse {
        $record = $this->program($program);

        return $this->archive($request, $service, $record, 'Program archived.');
    }

    public function restoreProgram(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $program,
    ): RedirectResponse {
        $record = $this->program($program);

        return $this->restore($request, $service, $record, 'Program restored.');
    }

    public function deleteProgram(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $program,
    ): RedirectResponse {
        $record = $this->program($program);

        return $this->forceDelete($request, $service, $record, 'Program permanently deleted.');
    }

    public function archiveProject(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $project,
    ): RedirectResponse {
        $record = $this->project($project);

        return $this->archive($request, $service, $record, 'Project archived.');
    }

    public function restoreProject(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $project,
    ): RedirectResponse {
        $record = $this->project($project);

        return $this->restore($request, $service, $record, 'Project restored.');
    }

    public function deleteProject(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $project,
    ): RedirectResponse {
        $record = $this->project($project);

        return $this->forceDelete($request, $service, $record, 'Project permanently deleted.');
    }

    public function archiveLegislation(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $legislativeRecord,
    ): RedirectResponse {
        $record = $this->legislativeRecord($legislativeRecord);

        return $this->archive($request, $service, $record, 'Legislative record archived.');
    }

    public function restoreLegislation(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $legislativeRecord,
    ): RedirectResponse {
        $record = $this->legislativeRecord($legislativeRecord);

        return $this->restore($request, $service, $record, 'Legislative record restored.');
    }

    public function deleteLegislation(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $legislativeRecord,
    ): RedirectResponse {
        $record = $this->legislativeRecord($legislativeRecord);

        return $this->forceDelete($request, $service, $record, 'Legislative record permanently deleted.');
    }

    public function archiveNews(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $newsRecord,
    ): RedirectResponse {
        $record = $this->newsRecord($newsRecord);

        return $this->archive($request, $service, $record, 'News/media record archived.');
    }

    public function restoreNews(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $newsRecord,
    ): RedirectResponse {
        $record = $this->newsRecord($newsRecord);

        return $this->restore($request, $service, $record, 'News/media record restored.');
    }

    public function deleteNews(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $newsRecord,
    ): RedirectResponse {
        $record = $this->newsRecord($newsRecord);

        return $this->forceDelete($request, $service, $record, 'News/media record permanently deleted.');
    }

    public function archivePublicPage(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $publicPage,
    ): RedirectResponse {
        $record = $this->publicPage($publicPage);

        return $this->archive($request, $service, $record, 'Public page archived.');
    }

    public function restorePublicPage(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $publicPage,
    ): RedirectResponse {
        $record = $this->publicPage($publicPage);

        return $this->restore($request, $service, $record, 'Public page restored.');
    }

    public function deletePublicPage(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        string $publicPage,
    ): RedirectResponse {
        $record = $this->publicPage($publicPage);

        return $this->forceDelete($request, $service, $record, 'Public page permanently deleted.');
    }

    private function archive(
        ArchiveContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        Model $record,
        string $message,
    ): RedirectResponse {
        Gate::authorize('delete', $record);

        /** @var User $user */
        $user = $request->user();

        $service->archive($record, $user, $request, (string) $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }

    private function restore(
        RestoreContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        Model $record,
        string $message,
    ): RedirectResponse {
        Gate::authorize('restore', $record);

        /** @var User $user */
        $user = $request->user();

        $service->restore($record, $user, $request, $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return back();
    }

    private function forceDelete(
        DeleteContentRecordRequest $request,
        AdminCivicContentLifecycleService $service,
        Model $record,
        string $message,
    ): RedirectResponse {
        Gate::authorize('forceDelete', $record);

        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $service->forceDelete(
            $record,
            $user,
            $request,
            (string) $validated['confirmation'],
            (string) $validated['reason'],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return redirect()->route($this->indexRoute($record));
    }

    private function program(string $id): Program
    {
        return Program::withTrashed()->findOrFail($id);
    }

    private function project(string $id): Project
    {
        return Project::withTrashed()->findOrFail($id);
    }

    private function legislativeRecord(string $id): LegislativeRecord
    {
        return LegislativeRecord::withTrashed()->findOrFail($id);
    }

    private function newsRecord(string $id): NewsRecord
    {
        return NewsRecord::withTrashed()->findOrFail($id);
    }

    private function publicPage(string $id): PublicPage
    {
        return PublicPage::withTrashed()->findOrFail($id);
    }

    private function indexRoute(Model $record): string
    {
        return match (true) {
            $record instanceof Program => 'admin-console.programs.index',
            $record instanceof Project => 'admin-console.projects.index',
            $record instanceof LegislativeRecord => 'admin-console.legislation.index',
            $record instanceof NewsRecord => 'admin-console.news.index',
            $record instanceof PublicPage => 'admin-console.public-pages.index',
            default => 'admin-console.dashboard',
        };
    }
}
