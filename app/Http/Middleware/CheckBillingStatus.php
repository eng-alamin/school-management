<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;
use Illuminate\Http\Request;

class CheckBillingStatus
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->school_id) {
            return $next($request);
        }

        $overdueInvoice = Invoice::where('school_id', $user->school_id)
            ->where('status', 'overdue')
            ->first();

        if ($overdueInvoice) {
            if (
                !$request->routeIs('billing.show') &&
                !$request->routeIs('billing.pay') &&
                !$request->routeIs('billing.payment.*') &&
                !$request->routeIs('logout')
            ) {
                return redirect()->route('billing.show')
                    ->with('error', 'আপনার মাসিক বিল পরিশোধ করুন। বিল পরিশোধ না করা পর্যন্ত সিস্টেম ব্যবহার করতে পারবেন না।');
            }
        }

        // if ($overdueInvoice) {
        //     // Super admin/owner কে billing page এ redirect করো, বাকি সব block
        //     if (!$request->routeIs('billing.*') && !$request->routeIs('logout')) {
        //         return redirect()->route('billing.show')
        //             ->with('error', 'আপনার মাসিক বিল পরিশোধ করুন। বিল পরিশোধ না করা পর্যন্ত সিস্টেম ব্যবহার করতে পারবেন না।');
        //     }
        // }

        return $next($request);
    }
}