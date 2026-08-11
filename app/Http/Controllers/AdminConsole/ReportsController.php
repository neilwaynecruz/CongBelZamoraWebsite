<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Reports\ReportsIndexRequest;
use App\Http\Resources\AdminConsole\AdminReportResource;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminReportsMetricsService;
use Inertia\Inertia;
use Inertia\Response;

class ReportsController extends Controller
{
    public function __invoke(
        ReportsIndexRequest $request,
        AdminReportsMetricsService $metrics,
        AdminConsoleNavigationService $navigation,
    ): Response {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Reports/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'reports' => (new AdminReportResource($user, $metrics, $request->days()))->toArray($request),
            'filters' => [
                'days' => $request->days(),
            ],
            'periodOptions' => [
                ['value' => 7, 'label' => 'Last 7 days'],
                ['value' => 30, 'label' => 'Last 30 days'],
                ['value' => 90, 'label' => 'Last 90 days'],
            ],
        ]);
    }
}
