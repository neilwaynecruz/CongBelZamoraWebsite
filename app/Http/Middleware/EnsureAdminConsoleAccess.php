<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminConsoleAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            $request->session()->put('url.intended', route('admin-console.dashboard'));

            return redirect()->route('admin-console.login');
        }

        abort_unless($user->isStaff() && $user->is_active === true, 403);

        if ($user->email_verified_at === null) {
            return redirect()->route('verification.notice');
        }

        if (
            (bool) config('belzamora.staff.require_2fa', false)
            && ! $user->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()
                ->route('security.edit')
                ->with('status', 'Staff accounts must enable two-factor authentication before using the District Office Command System.');
        }

        return $next($request);
    }
}
