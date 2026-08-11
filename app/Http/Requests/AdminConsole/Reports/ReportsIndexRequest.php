<?php

namespace App\Http\Requests\AdminConsole\Reports;

use App\Models\User;
use App\Services\AdminReportsMetricsService;
use Illuminate\Foundation\Http\FormRequest;

class ReportsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user instanceof User) {
            return false;
        }

        /** @var AdminReportsMetricsService $metrics */
        $metrics = app(AdminReportsMetricsService::class);

        return $metrics->canViewInquiryReports($user)
            || $metrics->canViewPublishReports($user)
            || $metrics->canViewStaleReports($user)
            || $metrics->canViewProjectReports($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'days' => ['nullable', 'integer', 'in:7,30,90'],
        ];
    }

    public function days(): int
    {
        return (int) ($this->validated('days') ?? 30);
    }
}
