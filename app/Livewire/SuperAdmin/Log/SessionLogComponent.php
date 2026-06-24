<?php

namespace App\Livewire\SuperAdmin\Log;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionLogComponent extends Component
{
    public string $currentSessionId = '';

    public function mount(): void
    {
        $this->currentSessionId = session()->getId();
    }

    protected function getOnlineSessions(): \Illuminate\Support\Collection
    {
        return DB::table('sessions')
            ->whereNotNull('user_id')
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $user  = DB::table('users')->find($session->user_id);
                $agent = $session->user_agent ?? '';

                return (object) [
                    'id'            => $session->id,
                    'user_name'     => $user?->name ?? '—',
                    'user_avatar'   => $user?->avatar ?? null,
                    'user_role'     => $user?->role ?? '—',
                    'ip_address'    => $session->ip_address ?? '—',
                    'browser'       => $this->detectBrowser($agent),
                    'os'            => $this->detectOs($agent),
                    'device'        => $this->detectDevice($agent),
                    'last_activity' => Carbon::createFromTimestamp($session->last_activity),
                    'is_current'    => $session->id === $this->currentSessionId,
                ];
            });
    }

    public function revokeSession(string $sessionId): void
    {
        if ($sessionId === $this->currentSessionId) return;

        DB::table('sessions')
            ->where('id', $sessionId)
            ->delete();
    }

    public function revokeAllOther(): void
    {
        DB::table('sessions')
            ->where('id', '!=', $this->currentSessionId)
            ->delete();
    }

    protected function detectBrowser(string $agent): string
    {
        return match(true) {
            str_contains($agent, 'Edg')     => 'Microsoft Edge',
            str_contains($agent, 'OPR')     => 'Opera',
            str_contains($agent, 'Chrome')  => 'Chrome',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Safari')  => 'Safari',
            default                         => 'Unknown',
        };
    }

    protected function detectOs(string $agent): string
    {
        return match(true) {
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac')     => 'macOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'iPhone'),
            str_contains($agent, 'iPad')    => 'iOS',
            str_contains($agent, 'Linux')   => 'Linux',
            default                         => 'Unknown',
        };
    }

    protected function detectDevice(string $agent): string
    {
        return (str_contains($agent, 'Mobile')
            || str_contains($agent, 'Android')
            || str_contains($agent, 'iPhone'))
            ? 'Mobile' : 'Desktop';
    }

    public function render()
    {
        return view('livewire.super-admin.log.session-log-component', [
            'sessions' => $this->getOnlineSessions(),
        ])
        ->layout('layouts.superadmin.app', [
            'title' => 'Active Sessions | ' . setting('app_name', 'EMS'),
        ]);
    }
}