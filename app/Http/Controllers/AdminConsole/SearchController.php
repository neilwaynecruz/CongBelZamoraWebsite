<?php

namespace App\Http\Controllers\AdminConsole;

use App\Filament\Resources\LegislativeRecordResource;
use App\Filament\Resources\NewsRecordResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\PublicPageResource as FilamentPublicPageResource;
use App\Http\Controllers\Controller;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
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
                $href = $this->recordUrl($result, $user);

                return [
                    ...$result,
                    'href' => $href,
                    'usesLegacyFallback' => $this->usesLegacyFallback($result, $href),
                ];
            })
            ->values();

        return response()->json(['data' => $results]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function recordUrl(array $result, User $user): ?string
    {
        if (($result['type'] ?? null) === 'Program') {
            $record = Program::query()->find($result['id'] ?? null);

            return $record !== null && $user->can('view', $record)
                ? route('admin-console.programs.show', $record)
                : null;
        }

        if (($result['type'] ?? null) === 'Project') {
            $record = Project::query()->find($result['id'] ?? null);

            return $record !== null && $user->can('view', $record)
                ? route('admin-console.projects.show', $record)
                : null;
        }

        if (($result['type'] ?? null) === 'News') {
            $record = NewsRecord::query()->find($result['id'] ?? null);

            return $record !== null && $user->can('view', $record)
                ? route('admin-console.news.show', $record)
                : null;
        }

        if (($result['type'] ?? null) === 'Legislation') {
            $record = LegislativeRecord::query()->find($result['id'] ?? null);

            return $record !== null && $user->can('view', $record)
                ? route('admin-console.legislation.show', $record)
                : null;
        }

        if (($result['type'] ?? null) === 'Public Page') {
            $record = PublicPage::query()->find($result['id'] ?? null);

            return $record !== null && $user->can('view', $record)
                ? route('admin-console.public-pages.show', $record)
                : null;
        }

        return $this->legacyRecordUrl($result, $user);
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
            'Public Page' => [PublicPage::class, FilamentPublicPageResource::class],
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

    /**
     * @param  array<string, mixed>  $result
     */
    private function usesLegacyFallback(array $result, ?string $href): bool
    {
        return $href !== null && ! in_array($result['type'] ?? null, ['Program', 'Project', 'News', 'Legislation', 'Public Page'], true);
    }
}
