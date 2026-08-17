<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scopes Spatie laravel-permission's "teams" feature to the currently
 * authenticated user's institution.
 *
 * IMPORTANT:
 * - Only attach this middleware to route groups that deal with
 *   institution-scoped roles/permissions (e.g. Admin panel).
 * - Do NOT attach this to the Ministry panel route group — Ministry
 *   roles are global (institution_id = NULL) and must remain unscoped.
 * - Must run AFTER the 'auth' middleware so auth()->user() is available.
 */
class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->institution_id) {
            app(PermissionRegistrar::class)->setPermissionsTeamId($user->institution_id);
        }

        return $next($request);
    }
}