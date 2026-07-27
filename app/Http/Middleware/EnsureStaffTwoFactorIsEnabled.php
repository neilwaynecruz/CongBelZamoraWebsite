<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffTwoFactorIsEnabled
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('belzamora.staff.require_2fa', false)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof User || ! $user->isStaff()) {
            return $next($request);
        }

        if ($user->hasEnabledTwoFactorAuthentication()) {
            return $next($request);
        }

        return redirect()
            ->route('security.edit')
            ->with('status', 'Staff accounts must enable two-factor authentication before using the admin panel.');
    }
}
