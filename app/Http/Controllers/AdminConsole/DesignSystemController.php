<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DesignSystemController extends Controller
{
    public function __invoke(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user->hasAdminRole(AdminRole::SuperAdministrator), 403);

        return Inertia::render('Admin/DesignSystem', [
            'adminConsole' => $navigation->contextFor($user),
        ]);
    }
}
