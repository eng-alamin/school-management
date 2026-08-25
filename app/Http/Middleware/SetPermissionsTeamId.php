<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Database\Seeders\MinistryRolePermissionSeeder;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Scopes Spatie laravel-permission's "teams" feature to the currently
 * authenticated user's institution — OR to the Ministry global sentinel.
 *
 * IMPORTANT — attach this middleware to EVERY authenticated route group
 * (Admin panel AND Ministry panel). Do not skip it for Ministry routes:
 *
 * Institution users  -> institution_id = <real id>   -> team id = that id
 * Ministry users      -> institution_id = NULL         -> team id = GLOBAL_TEAM_ID (0)
 *
 * WHY THIS MATTERS: MinistryRolePermissionSeeder seeds Ministry roles
 * under model_has_roles.institution_id = 0 (NOT NULL — MySQL forbids NULL
 * in a composite PRIMARY KEY column). If this middleware left the team id
 * unset (null) for Ministry users, Spatie's hasRole()/can() checks would
 * silently look for institution_id IS NULL rows that don't exist, and
 * every Ministry authorization check would silently fail — no error, the
 * user would just appear to have no roles/permissions at all.
 *
 * Must run AFTER the 'auth' middleware so auth()->user() is available.
 */
class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user) {
            $teamId = $user->institution_id
                ?? MinistryRolePermissionSeeder::GLOBAL_TEAM_ID;

            app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
        }

        return $next($request);
    }
}