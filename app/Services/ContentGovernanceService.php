<?php

namespace App\Services;

use App\Models\ContentApproval;
use App\Support\ContentGovernanceException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ContentGovernanceService
{
    /**
     * @return list<string>
     */
    public function blockingIssues(ContentApproval $approval): array
    {
        $approvable = $this->approvable($approval);

        if (! $approvable instanceof Model) {
            return [];
        }

        $policy = $this->policyFor($approvable);

        if ($policy === null) {
            return [];
        }

        $issues = [];

        foreach ($this->requiredFields($policy) as $field) {
            if ($this->isBlankField($approvable, $field)) {
                $issues[] = $this->labelFor($policy).' is missing required publish field: '.$field.'.';
            }
        }

        $issues = [
            ...$issues,
            ...$this->dateIssues($approvable, $policy),
            ...$this->sourceIssues($approvable, $policy),
            ...$this->uncertainStatementIssues($approvable, $policy),
            ...$this->approvalIssues($approval),
        ];

        if (
            (bool) config('belzamora.governance.content_approval.fast_track_requires_review_note', true)
            && $approval->is_fast_track
            && blank($approval->review_notes)
        ) {
            $issues[] = 'Fast-track approvals require review notes before schedule or publish.';
        }

        return array_values(array_unique($issues));
    }

    public function assertPublishable(ContentApproval $approval): void
    {
        $issues = $this->blockingIssues($approval);

        if ($issues !== []) {
            throw new ContentGovernanceException($approval, $issues);
        }
    }

    /**
     * @return list<string>
     */
    public function readinessChecklist(ContentApproval $approval): array
    {
        $approvable = $this->approvable($approval);

        if (! $approvable instanceof Model) {
            return ['No linked public-content record. Launch-gate approvals may use the manual checklist only.'];
        }

        $policy = $this->policyFor($approvable);

        if ($policy === null) {
            return ['No automated governance policy is configured for '.class_basename($approvable).'. Use the manual checklist and source register.'];
        }

        $issues = $this->blockingIssues($approval);
        $required = implode(', ', $this->requiredFields($policy));
        $roles = $this->workflowRoleSummary();

        return [
            'Module: '.$this->labelFor($policy),
            'Review cadence: every '.(int) data_get($policy, 'review_cadence_days', 30).' days.',
            'Required publish fields: '.$required.'.',
            'Source policy: '.(string) data_get($policy, 'source_policy', 'Source policy not configured.'),
            'Workflow roles: '.$roles,
            'Editorial snapshot: capture a change summary and compare with the last published version before publishing.',
            $issues === []
                ? 'Publish readiness: Passed automated governance checks.'
                : 'Publish readiness: '.count($issues).' issue(s) must be resolved.',
            ...array_map(fn (string $issue): string => 'Issue: '.$issue, $issues),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function policyFor(Model $model): ?array
    {
        $modules = config('belzamora.governance.modules', []);

        if (! is_array($modules)) {
            return null;
        }

        foreach ($modules as $policy) {
            if (! is_array($policy)) {
                continue;
            }

            $class = data_get($policy, 'model');

            if (is_string($class) && $model instanceof $class) {
                return $policy;
            }
        }

        return null;
    }

    private function approvable(ContentApproval $approval): ?Model
    {
        $approval->loadMissing('approvable');

        return $approval->approvable instanceof Model ? $approval->approvable : null;
    }

    /**
     * @param  array<string, mixed>  $policy
     * @return list<string>
     */
    private function requiredFields(array $policy): array
    {
        $fields = data_get($policy, 'required_publish_fields', []);

        return is_array($fields)
            ? array_values(array_filter($fields, 'is_string'))
            : [];
    }

    private function isBlankField(Model $model, string $field): bool
    {
        $value = $this->fieldValue($model, $field);

        return blank($value);
    }

    private function fieldValue(Model $model, string $field): mixed
    {
        if (str_starts_with($field, 'metadata.')) {
            return data_get($model->getAttribute('metadata'), substr($field, strlen('metadata.')));
        }

        return $model->getAttribute($field);
    }

    /**
     * @param  array<string, mixed>  $policy
     * @return list<string>
     */
    private function dateIssues(Model $model, array $policy): array
    {
        $issues = [];
        $verifiedAt = $this->firstDate($model, ['verified_at', 'source_verified_at']);
        $reviewDueAt = $this->dateValue($model->getAttribute('review_due_at'));

        if ($verifiedAt instanceof Carbon && $verifiedAt->isFuture()) {
            $issues[] = $this->labelFor($policy).' has a verification date in the future.';
        }

        if ($reviewDueAt instanceof Carbon && $reviewDueAt->lte(now())) {
            $issues[] = $this->labelFor($policy).' review due date has already passed.';
        }

        return $issues;
    }

    /**
     * @return list<string>
     */
    private function sourceIssues(Model $model, array $policy): array
    {
        $issues = [];
        $requiresSource = (bool) data_get($policy, 'requires_source', false)
            || data_get($model->getAttribute('metadata'), 'source_required') === true;

        if ($requiresSource) {
            $hasSourceLabel = filled($model->getAttribute('source_label'))
                || filled($model->getAttribute('official_source_label'));
            $hasSourceUrl = filled($model->getAttribute('source_url'))
                || filled($model->getAttribute('official_source_url'));

            if (! $hasSourceLabel) {
                $issues[] = $this->labelFor($policy).' requires an official source label before publication.';
            }

            if (! $hasSourceUrl) {
                $issues[] = $this->labelFor($policy).' requires an official source URL before publication.';
            }
        }

        foreach (['source_url', 'official_source_url'] as $field) {
            $value = $model->getAttribute($field);

            if (is_string($value) && $value !== '' && filter_var($value, FILTER_VALIDATE_URL) === false) {
                $issues[] = $this->labelFor($policy).' has an invalid URL in '.$field.'.';
            }
        }

        return $issues;
    }

    /**
     * @return list<string>
     */
    private function approvalIssues(ContentApproval $approval): array
    {
        $issues = [];

        if (
            (bool) config('belzamora.governance.content_approval.change_summary_required', true)
            && blank($approval->change_summary)
        ) {
            $issues[] = 'A clear change summary is required before schedule or publish.';
        }

        $validUntil = $this->dateValue($approval->valid_until);

        if ($validUntil instanceof Carbon && $validUntil->lte(now())) {
            $issues[] = 'Approval end-of-validity date has already passed.';
        }

        return $issues;
    }

    /**
     * @param  array<string, mixed>  $policy
     * @return list<string>
     */
    private function uncertainStatementIssues(Model $model, array $policy): array
    {
        $metadata = $model->getAttribute('metadata');
        $flag = (string) data_get($policy, 'uncertain_statement_metadata_flag', 'contains_uncertain_public_statement');
        $note = (string) data_get($policy, 'uncertain_statement_metadata_note', 'uncertain_public_statement_note');

        if (! is_array($metadata) || data_get($metadata, $flag) !== true) {
            return [];
        }

        $hasSource = filled($model->getAttribute('source_url')) || filled($model->getAttribute('official_source_url'));
        $hasNote = filled(data_get($metadata, $note));

        return $hasSource || $hasNote
            ? []
            : [$this->labelFor($policy).' contains an uncertain public statement but has no source URL or reviewer note.'];
    }

    /**
     * @param  list<string>  $fields
     */
    private function firstDate(Model $model, array $fields): ?Carbon
    {
        foreach ($fields as $field) {
            $date = $this->dateValue($model->getAttribute($field));

            if ($date instanceof Carbon) {
                return $date;
            }
        }

        return null;
    }

    private function dateValue(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $policy
     */
    private function labelFor(array $policy): string
    {
        return (string) data_get($policy, 'label', 'Content');
    }

    private function workflowRoleSummary(): string
    {
        $roles = config('belzamora.governance.workflow_roles', []);

        if (! is_array($roles)) {
            return 'Workflow roles are not configured.';
        }

        return collect($roles)
            ->map(fn (mixed $values, string $action): string => $action.'='.count(Arr::wrap($values)).' role(s)')
            ->implode('; ');
    }
}
