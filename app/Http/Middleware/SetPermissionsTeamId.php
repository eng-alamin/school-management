<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Database\Seeders\MinistryRolePermissionSeeder;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $teamId = $user->institution_id
                ?? MinistryRolePermissionSeeder::MINISTRY_TEAM_ID;

            app(PermissionRegistrar::class)->setPermissionsTeamId($teamId);
        }

        return $next($request);
    }
}