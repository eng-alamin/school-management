<?php

namespace App\Livewire\Admin\Log;

use App\Models\User;
use App\Models\Scopes\SchoolScope;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoginLogComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $role   = '';

    public string $currentSessionId = '';

    public string $activeTab = 'sessions';

    public function mount(): void
    {
        $this->currentSessionId = session()->getId();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRole(): void   { $this->resetPage(); }

    protected function getOnlineSessions(): \Illuminate\Support\Collection
    {
        $schoolUserIds = DB::table('users')
            ->where('school_id', Auth::user()->school_id)
            ->pluck('id');

        return DB::table('sessions')
            ->whereIn('user_id', $schoolUserIds)
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
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

    protected function getAllUsers()
    {
        return User::withoutGlobalScope(SchoolScope::class)
            ->where('school_id', Auth::user()->school_id)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
            }))
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderByDesc('last_login_at')
            ->paginate(15);
    }

    public function revokeSession(string $sessionId): void
    {
        if ($sessionId === $this->currentSessionId) return;

        DB::table('sessions')
            ->where('id', $sessionId)
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')
                  ->where('school_id', Auth::user()->school_id);
            })
            ->delete();
    }

    public function revokeAllOther(): void
    {
        DB::table('sessions')
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')
                  ->where('school_id', Auth::user()->school_id);
            })
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
        return view('livewire.admin.log.login-log-component', [
            'onlineSessions' => $this->getOnlineSessions(),
            'allUsers'       => $this->getAllUsers(),
        ])
        ->layout('layouts.admin.app', [
            'title' => 'School Settings | ' . institution()->name,
        ]);
    }
}