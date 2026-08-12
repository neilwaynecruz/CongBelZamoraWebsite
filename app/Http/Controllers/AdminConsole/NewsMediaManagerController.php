<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Media\RemoveNewsMediaAssetRequest;
use App\Http\Requests\AdminConsole\Media\SaveNewsMediaAssetRequest;
use App\Http\Resources\AdminConsole\ManagedNewsMediaAssetResource;
use App\Http\Resources\AdminConsole\NewsRecordResource;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminMediaLibraryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NewsMediaManagerController extends Controller
{
    public function index(
        Request $request,
        NewsRecord $newsRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('view', $newsRecord);
        Gate::authorize('viewAny', NewsMediaAsset::class);

        $newsRecord->load([
            'category:id,name',
            'status:id,label,value,tone',
            'contentOwner:id,name',
            'approver:id,name',
            'mediaAssets.verifier:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Media/NewsMedia', [
            'adminConsole' => $navigation->contextFor($user),
            'newsRecord' => (new NewsRecordResource($newsRecord))->resolve($request),
            'mediaAssets' => ManagedNewsMediaAssetResource::collection($newsRecord->mediaAssets)->resolve($request),
            'mediaPolicy' => [
                'allowedMimetypes' => config('belzamora.media.allowed_mimetypes', []),
                'maxUploadKilobytes' => (int) config('belzamora.media.max_upload_kilobytes', 5120),
                'disk' => 'public',
                'visibility' => 'public',
                'sensitiveFilesNotice' => 'Only rights-cleared official public images may be uploaded. IDs, medical files, private resident documents, and unapproved attachments are not accepted.',
            ],
            'urls' => [
                'store' => route('admin-console.news.media.store', $newsRecord),
                'back' => route('admin-console.news.show', $newsRecord),
            ],
            'can' => [
                'create' => $user->can('create', NewsMediaAsset::class),
            ],
        ]);
    }

    public function store(
        SaveNewsMediaAssetRequest $request,
        NewsRecord $newsRecord,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $media->saveNewsAsset($newsRecord, null, $request->payload(), $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Approved news media asset uploaded and logged.',
        ]);

        return redirect()->route('admin-console.news.media', $newsRecord);
    }

    public function update(
        SaveNewsMediaAssetRequest $request,
        NewsRecord $newsRecord,
        NewsMediaAsset $newsMediaAsset,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        abort_unless($newsMediaAsset->news_record_id === $newsRecord->id, 404);

        /** @var User $user */
        $user = $request->user();

        $media->saveNewsAsset($newsRecord, $newsMediaAsset, $request->payload(), $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'News media metadata updated and audited.',
        ]);

        return redirect()->route('admin-console.news.media', $newsRecord);
    }

    public function destroy(
        RemoveNewsMediaAssetRequest $request,
        NewsRecord $newsRecord,
        NewsMediaAsset $newsMediaAsset,
        AdminMediaLibraryService $media,
    ): RedirectResponse {
        abort_unless($newsMediaAsset->news_record_id === $newsRecord->id, 404);

        /** @var User $user */
        $user = $request->user();

        $media->removeNewsAsset($newsMediaAsset, $user, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'News media asset removed from the custom console.',
        ]);

        return redirect()->route('admin-console.news.media', $newsRecord);
    }
}
