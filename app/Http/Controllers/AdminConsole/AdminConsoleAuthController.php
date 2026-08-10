<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class AdminConsoleAuthController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User) {
            abort_unless($user->isStaff() && $user->is_active === true, 403);

            return redirect()->route('admin-console.dashboard');
        }

        $request->session()->put('url.intended', route('admin-console.dashboard'));

        return Inertia::render('Admin/Auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'forgotPasswordUrl' => route('password.request'),
            'loginUrl' => route('login.store'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
            'status' => $request->session()->get('status'),
            'office' => [
                'portalName' => config('belzamora.office.portal_name'),
                'representativeName' => config('belzamora.office.representative_name'),
                'district' => config('belzamora.office.district'),
            ],
        ]);
    }
}
