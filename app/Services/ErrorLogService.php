<?php

namespace App\Services;

use App\Models\SystemErrorLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class ErrorLogService
{
    /**
     * Log a throwable to system_error_logs with full context.
     *
     * @param  Throwable  $e
     * @param  array  $context  Extra context: institution_id, branch_id, panel, ids, payload etc.
     * @param  string|null  $component  Livewire component class name (use static::class)
     */
    public static function log(Throwable $e, array $context = [], ?string $component = null): void
    {
        try {
            $user = Auth::user();

            $institutionId = $context['institution_id'] ?? ($user?->institution_id ?? null);
            $branchId      = $context['branch_id'] ?? ($user?->branch_id ?? null);

            // Sensitive keys strip kore rakhi, jate password/token log e chole na jay
            $safeContext = collect($context)
                ->except(['password', 'password_confirmation', 'token', 'api_key', 'secret'])
                ->toArray();

            SystemErrorLog::create([
                'institution_id'  => $institutionId,
                'branch_id'       => $branchId,
                'user_id'         => $user?->id,
                'user_role'       => method_exists($user ?? null, 'getRoleNames')
                    ? $user->getRoleNames()->first()
                    : null,
                'panel'           => $context['panel'] ?? null,
                'component'       => $component,
                'exception_class' => get_class($e),
                'message'         => mb_substr($e->getMessage(), 0, 60000),
                'file'            => $e->getFile(),
                'line'            => $e->getLine(),
                'trace'           => mb_substr($e->getTraceAsString(), 0, 60000),
                'context'         => $safeContext,
                'url'             => app()->runningInConsole() ? null : Request::fullUrl(),
                'method'          => app()->runningInConsole() ? 'CLI' : Request::method(),
                'ip'              => app()->runningInConsole() ? null : Request::ip(),
                'status'          => SystemErrorLog::STATUS_NEW,
            ]);
        } catch (Throwable $inner) {
            // Logging nijei fail korle jeno infinite loop na hoy,
            // tai eta shudhu Laravel-er nijer log file e jabe.
            logger()->error('ErrorLogService failed to write log: ' . $inner->getMessage());
        }
    }
}