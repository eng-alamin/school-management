<?php

namespace App\Livewire\SuperAdmin\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginlogComponent extends Component
{
    public $user;
    public $sessions;
    public $currentSessionId;

    public function mount()
    {
        $this->user           = Auth::user();
        $this->currentSessionId = session()->getId();
        $this->loadSessions();
    }

    public function loadSessions()
    {
        $this->sessions = DB::table('sessions')
            ->where('user_id', $this->user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $agent = $session->user_agent ?? '';

                // Browser detect
                $browser = 'Unknown Browser';
                if (str_contains($agent, 'Edg'))        $browser = 'Microsoft Edge';
                elseif (str_contains($agent, 'OPR'))    $browser = 'Opera';
                elseif (str_contains($agent, 'Chrome'))  $browser = 'Chrome';
                elseif (str_contains($agent, 'Firefox')) $browser = 'Firefox';
                elseif (str_contains($agent, 'Safari'))  $browser = 'Safari';

                // OS detect
                $os = 'Unknown OS';
                if (str_contains($agent, 'Windows'))     $os = 'Windows';
                elseif (str_contains($agent, 'Mac'))     $os = 'macOS';
                elseif (str_contains($agent, 'Linux'))   $os = 'Linux';
                elseif (str_contains($agent, 'Android')) $os = 'Android';
                elseif (str_contains($agent, 'iPhone') || str_contains($agent, 'iPad')) $os = 'iOS';

                // Device type
                $isMobile = str_contains($agent, 'Mobile') || str_contains($agent, 'Android') || str_contains($agent, 'iPhone');
                $device   = $isMobile ? 'Mobile' : 'Desktop';

                return (object) [
                    'id'            => $session->id,
                    'ip_address'    => $session->ip_address ?? '—',
                    'browser'       => $browser,
                    'os'            => $os,
                    'device'        => $device,
                    'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
                    'is_current'    => $session->id === $this->currentSessionId,
                ];
            });
    }

    public function revokeSession(string $sessionId)
    {
        // Cannot revoke current session
        if ($sessionId === $this->currentSessionId) {
            return;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $this->user->id)
            ->delete();

        $this->loadSessions();
    }

    public function revokeAllOther()
    {
        DB::table('sessions')
            ->where('user_id', $this->user->id)
            ->where('id', '!=', $this->currentSessionId)
            ->delete();

        $this->loadSessions();
    }

    public function render()
    {
        return view('livewire.super-admin.profile.loginlog-component')
            ->with(['user' => $this->user, 'sessions' => $this->sessions])
            ->layout('layouts.superadmin.app', [
                'title' => 'Login Logs | ' . setting('app_name', 'EMS'),
            ]);
    }
}