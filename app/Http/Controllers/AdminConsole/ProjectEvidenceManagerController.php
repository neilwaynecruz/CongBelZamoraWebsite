<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Media\RemoveProjectEvidenceRequest;
use App\Http\Requests\AdminConsole\Media\SaveProjectEvidenceRequest;
use App\Http\Resources\AdminConsole\ManagedProjectEvidenceResource;
use App\Http\Resources\AdminConsole\ProjectResource;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminMediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectEvidenceManagerController extends Controller
{
    public function index(
        Request $request,
        Project $project,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('view', $project);
        Gate::authorize('viewAny', ProjectEvidence::class);

        $project->load([
            'category:id,name',
            'status:id,label,value,tone',
            'primaryPartner:id,name',
            'contentOwner:id,name',
            'approver:id,name',
            'evidences.verifier:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Media/ProjectEvidence', [
            'adminConsole' => $navigation->contextFor($user),
            'project' => (new ProjectResource($project))->resolve($request),
            'evidences' => ManagedProjectEvidenceResource::collection($project->evidences)->resolve($request),
            'mediaPolicy' => [
                'allowedMimetypes' => config('belzamora.media.allowed_mimetypes', []),
                'maxUploadKilobytes' => (int) config('belzamora.media.max_upload_kilobytes', 5120),
                'disk' => 'public',
                'visibility' => 'public',
                'sensitiveFilesNotice' => 'Only rights-cleared official public images may be uploaded. IDs, medical files, private resident documents, and unapproved attachments are not accepted.',
            ],
            'urls' => [
                'store' => route('admin-console.projects.evidence.store', $project),
                'back' => route('admin-console.projects.show', $project),
            ],
            'can' => [
                'create' => $user->can('create', ProjectEvidence::class),
            ],
        ]);
    }

    public function store(
        SaveProjectEvidenceRequest $request,
        Project $project,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $media->saveProjectEvidence($project, null, $request->payload(), $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Approved project evidence uploaded and logged.',
        ]);

        return redirect()->route('admin-console.projects.evidence', $project);
    }

    public function update(
        SaveProjectEvidenceRequest $request,
        Project $project,
        ProjectEvidence $projectEvidence,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        abort_unless($projectEvidence->project_id === $project->id, 404);

        /** @var User $user */
        $user = $request->user();

        $media->saveProjectEvidence($project, $projectEvidence, $request->payload(), $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Project evidence metadata updated and audited.',
        ]);

        return redirect()->route('admin-console.projects.evidence', $project);
    }

    public function destroy(
        RemoveProjectEvidenceRequest $request,
        Project $project,
        ProjectEvidence $projectEvidence,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        abort_unless($projectEvidence->project_id === $project->id, 404);

        /** @var User $user */
        $user = $request->user();

        $media->removeProjectEvidence($projectEvidence, $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Project evidence removed from the custom console.',
        ]);

        return redirect()->route('admin-console.projects.evidence', $project);
    }
}
