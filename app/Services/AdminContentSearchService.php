<?php

namespace App\Services;

use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminContentSearchService
{
    /**
     * @return list<array<string, int|string|null>>
     */
    public function search(string $term, int $limit = 25): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $results = [
            ...$this->searchModel(Program::query(), 'Program', ['title', 'summary', 'sector'], $term, $limit),
            ...$this->searchModel(LegislativeRecord::query(), 'Legislation', ['bill_number', 'public_title', 'official_title', 'plain_language_summary'], $term, $limit, 'public_title'),
            ...$this->searchModel(Project::query(), 'Project', ['title', 'summary', 'barangay', 'location'], $term, $limit),
            ...$this->searchModel(NewsRecord::query(), 'News', ['title', 'summary', 'official_update'], $term, $limit),
            ...$this->searchModel(PublicPage::query(), 'Public Page', ['title', 'slug', 'component', 'owner_label'], $term, $limit),
            ...$this->searchModel(SourceRecord::query(), 'Source', ['title', 'description', 'url'], $term, $limit),
        ];

        return array_slice($results, 0, $limit);
    }

    /**
     * Search only record types the staff member may view.
     *
     * @return list<array<string, int|string|null>>
     */
    public function searchFor(User $user, string $term, int $limit = 12): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $results = [];

        if ($user->can('viewAny', Program::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(Program::query(), 'Program', ['title', 'summary', 'sector'], $term, $limit),
            ];
        }

        if ($user->can('viewAny', LegislativeRecord::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(LegislativeRecord::query(), 'Legislation', ['bill_number', 'public_title', 'official_title', 'plain_language_summary'], $term, $limit, 'public_title'),
            ];
        }

        if ($user->can('viewAny', Project::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(Project::query(), 'Project', ['title', 'summary', 'barangay', 'location'], $term, $limit),
            ];
        }

        if ($user->can('viewAny', NewsRecord::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(NewsRecord::query(), 'News', ['title', 'summary', 'official_update'], $term, $limit),
            ];
        }

        if ($user->can('viewAny', PublicPage::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(PublicPage::query(), 'Public Page', ['title', 'slug', 'component', 'owner_label'], $term, $limit),
            ];
        }

        if ($user->can('viewAny', SourceRecord::class)) {
            $results = [
                ...$results,
                ...$this->searchModel(SourceRecord::query(), 'Source', ['title', 'description', 'url'], $term, $limit),
            ];
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     * @return list<array<string, int|string|null>>
     */
    private function searchModel(Builder $query, string $type, array $columns, string $term, int $limit, string $titleColumn = 'title'): array
    {
        $query->where(function (Builder $builder) use ($columns, $term): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', '%'.$term.'%');
            }
        });

        return array_values($query
            ->limit($limit)
            ->get()
            ->map(function (Model $record) use ($titleColumn, $type): array {
                $id = $record->getKey();

                return [
                    'type' => $type,
                    'title' => (string) ($record->getAttribute($titleColumn) ?? $record->getAttribute('title') ?? class_basename($record).' #'.$id),
                    'subtitle' => filled($record->getAttribute('slug')) ? (string) $record->getAttribute('slug') : null,
                    'id' => is_int($id) || is_string($id) ? $id : (string) $id,
                ];
            })
            ->values()
            ->all());
    }
}
