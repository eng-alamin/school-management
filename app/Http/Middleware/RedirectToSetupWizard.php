<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToSetupWizard
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (
            $user
            && $user->isAdmin()
            && $user->institution
            && !$user->institution->setup_completed
            && !$request->routeIs('admin.*')
        ) {
            return redirect()->route('admin.setup-wizard.index');
        }

        return $next($request);
    }
}