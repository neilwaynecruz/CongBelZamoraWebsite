<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __invoke(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canManageStaffUsers(), 403);

        return Inertia::render('Admin/Settings/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'settings' => [
                'staffSecurity' => [
                    'twoFactorRequired' => (bool) config('belzamora.staff.require_2fa', false),
                    'databaseNotifications' => (bool) config('belzamora.staff.database_notifications', true),
                ],
                'adminConsole' => [
                    'enabled' => (bool) config('belzamora.admin_console.enabled', true),
                    'filamentFallbackEnabled' => (bool) config('belzamora.admin_console.filament_fallback_enabled', true),
                    'modules' => config('belzamora.admin_console.modules', []),
                ],
                'privacy' => [
                    'inquiryRetentionDays' => (int) config('belzamora.privacy.inquiry_retention_days'),
                ],
                'media' => [
                    'maxUploadKilobytes' => (int) config('belzamora.media.max_upload_kilobytes'),
                    'allowedMimetypes' => config('belzamora.media.allowed_mimetypes', []),
                    'newsDirectory' => (string) config('belzamora.media.news_directory'),
                    'projectEvidenceDirectory' => (string) config('belzamora.media.project_evidence_directory'),
                ],
                'cache' => [
                    'publicTtlSeconds' => (int) config('belzamora.cache.public_ttl_seconds'),
                ],
                'jobs' => [
                    'tries' => (int) config('belzamora.jobs.default_tries'),
                    'backoffSeconds' => (int) config('belzamora.jobs.default_backoff_seconds'),
                    'timeoutSeconds' => (int) config('belzamora.jobs.default_timeout_seconds'),
                ],
                'environment' => [
                    'appEnv' => app()->environment(),
                    'debugEnabled' => (bool) config('app.debug'),
                    'queueConnection' => (string) config('queue.default'),
                    'cacheStore' => (string) config('cache.default'),
                    'filesystemDisk' => (string) config('filesystems.default'),
                    'mailMailer' => (string) config('mail.default'),
                ],
            ],
            'writeAccess' => [
                'enabled' => false,
                'reason' => 'Settings are read-only until a dedicated approval workflow for configuration changes is approved.',
            ],
            'filamentFallbackUrl' => (bool) config('belzamora.admin_console.filament_fallback_enabled', true)
                ? url('/admin')
                : null,
        ]);
    }
}
