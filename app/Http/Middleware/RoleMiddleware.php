<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    protected $dashboards = [
        'admin'      => 'admin.dashboard',
        'teacher'    => 'teacher.dashboard',
        'accountant' => 'accountant.dashboard',
        'student'    => 'student.dashboard',
        'parent'     => 'parent.dashboard',
    ];

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'এই পেজে প্রবেশ করতে লগইন করুন।');
        }

        if (!$user->is_active) {
            Auth::logout();

            return redirect()->route('login')
                ->with('error', 'আপনার অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে।');
        }

        if (empty($roles)) {
            return $next($request);
        }

        $allowedRoles = [];

        foreach ($roles as $role) {
            $allowedRoles = array_merge(
                $allowedRoles,
                array_map('trim', explode('|', $role))
            );
        }

        if (!in_array($user->role, $allowedRoles)) {
            return redirect()->route(
                $this->dashboards[$user->role] ?? 'login'
            )->with('error', 'এই পেজে আপনার অ্যাক্সেস নেই।');
        }

        return $next($request);
    }
}