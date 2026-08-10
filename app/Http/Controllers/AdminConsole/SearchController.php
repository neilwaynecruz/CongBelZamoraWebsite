<?php

namespace App\Http\Controllers\AdminConsole;

use App\Filament\Resources\LegislativeRecordResource;
use App\Filament\Resources\NewsRecordResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProjectResource;
use App\Http\Controllers\Controller;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use App\Services\AdminContentSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request, AdminContentSearchService $search): JsonResponse
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'min:2', 'max:80'],
        ]);

        /** @var User $user */
        $user = $request->user();

        $results = collect($search->searchFor($user, $validated['term']))
            ->map(function (array $result) use ($user): array {
                $href = $this->legacyRecordUrl($result, $user);

                return [
                    ...$result,
                    'href' => $href,
                    'usesLegacyFallback' => $href !== null,
                ];
            })
            ->values();

        return response()->json(['data' => $results]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function legacyRecordUrl(array $result, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        [$model, $resource] = match ($result['type'] ?? null) {
            'Program' => [Program::class, ProgramResource::class],
            'Legislation' => [LegislativeRecord::class, LegislativeRecordResource::class],
            'Project' => [Project::class, ProjectResource::class],
            'News' => [NewsRecord::class, NewsRecordResource::class],
            default => [null, null],
        };

        if ($model === null || $resource === null) {
            return null;
        }

        $record = $model::query()->find($result['id'] ?? null);

        if ($record === null || ! $user->can('view', $record)) {
            return null;
        }

        return $resource::getUrl($user->can('update', $record) ? 'edit' : 'view', ['record' => $record]);
    }
}
