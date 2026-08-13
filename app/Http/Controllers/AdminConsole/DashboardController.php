<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminDashboardMetricsService;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        AdminDashboardMetricsService $metrics,
        AdminConsoleNavigationService $navigation,
    ): Response {
        /** @var User $user */
        $user = $request->user();
        $counts = $metrics->getCachedCounts();
        $inquiryQueue = $metrics->getInquiryQueueCounts($user);

        $attentionItems = collect($metrics->getNeedsAttentionItems($user))
            ->take(8)
            ->map(function (array $item): array {
                $href = $this->attentionHref($item);

                return [
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'status' => $item['status'],
                    'severity' => $item['severity'],
                    'date' => $item['date'] instanceof DateTimeInterface
                        ? $item['date']->format(DateTimeInterface::ATOM)
                        : null,
                    'dateLabel' => $item['date'] instanceof DateTimeInterface
                        ? Carbon::instance($item['date'])->setTimezone('Asia/Manila')->format('M j, Y g:i A')
                        : null,
                    'href' => $href,
                    'usesLegacyFallback' => ! str_starts_with($href, url('/admin-console')),
                ];
            })
            ->values();

        $recentActivity = $user->canAuditRecords()
            ? AuditLog::query()
                ->with('actor:id,name')
                ->latest('occurred_at')
                ->limit(6)
                ->get(['id', 'actor_user_id', 'event', 'occurred_at'])
                ->map(fn (AuditLog $log): array => [
                    'id' => $log->id,
                    'event' => str($log->event)->replace('_', ' ')->headline()->toString(),
                    'actor' => $log->actor?->name ?? 'System',
                    'occurredAt' => $log->occurred_at->timezone('Asia/Manila')->format('M j, Y g:i A'),
                ])
                ->values()
            : collect();

        return Inertia::render('Admin/Dashboard', [
            'adminConsole' => $navigation->contextFor($user),
            'metrics' => [
                ...$counts,
                'unanswered_inquiries' => $metrics->getUnansweredInquiriesCount($user),
            ],
            'inquiryQueue' => $inquiryQueue,
            'attentionItems' => $attentionItems,
            'recentActivity' => $recentActivity,
            'serverTime' => now('Asia/Manila')->toIso8601String(),
            'office' => [
                'portalName' => config('belzamora.office.portal_name'),
                'district' => config('belzamora.office.district'),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function attentionHref(array $item): string
    {
        return match ($item['type']) {
            default => (string) ($item['url'] ?? route('admin-console.dashboard')),
        };
    }
}
