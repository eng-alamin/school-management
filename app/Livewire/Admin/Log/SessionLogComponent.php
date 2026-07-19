<?php

namespace App\Livewire\Admin\Log;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SessionLogComponent extends Component
{
    public string $currentSessionId = '';
    public ?int $institutionId = null;

    public function mount(): void
    {
        // Guard 1: শুধুমাত্র 'admin' role এই component access করতে পারবে।
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            throw new AccessDeniedHttpException('Unauthorized access to session log.');
        }

        // Guard 2: admin-এর অবশ্যই institution_id থাকতে হবে। এটাই সেই কলাম
        // যেটা দিয়ে বাকি সব query institution-scoped রাখা হবে।
        $institutionId = Auth::user()->institution_id;

        if (blank($institutionId)) {
            throw new AccessDeniedHttpException('Admin account is not linked to an institution.');
        }

        $this->institutionId    = $institutionId;
        $this->currentSessionId = session()->getId();
    }

    protected function getOnlineSessions(): \Illuminate\Support\Collection
    {
        // sessions টেবিলে institution_id কলাম নেই, তাই আগে এই institution-এর
        // user-দের id বের করে নিতে হবে, তারপর সেই user_id দিয়ে sessions ফিল্টার করতে হবে।
        // এভাবেই অন্য institution-এর session/user এখানে কখনো আসবে না।
        $institutionUsers = DB::table('users')
            ->where('institution_id', $this->institutionId)
            ->select('id', 'name', 'avatar', 'role')
            ->get()
            ->keyBy('id');

        if ($institutionUsers->isEmpty()) {
            return collect();
        }

        $sessions = DB::table('sessions')
            ->whereIn('user_id', $institutionUsers->keys())
            ->orderByDesc('last_activity')
            ->get();

        return $sessions->map(function ($session) use ($institutionUsers) {
            $user  = $institutionUsers->get($session->user_id);
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
        if ($sessionId === $this->currentSessionId) {
            return;
        }

        // নিজের institution-এর বাইরের কোনো session ভুলেও delete না হয়ে যায়,
        // তাই delete করার আগে session-টা এই institution-এর user-এরই কিনা যাচাই করা হচ্ছে।
        $belongsToInstitution = DB::table('sessions')
            ->where('id', $sessionId)
            ->whereIn('user_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('institution_id', $this->institutionId);
            })
            ->exists();

        if (! $belongsToInstitution) {
            $this->dispatch('toast', type: 'error', message: 'Session not found in your institution.');
            return;
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Session revoked successfully.');
    }

    public function revokeAllOther(): void
    {
        DB::table('sessions')
            ->where('id', '!=', $this->currentSessionId)
            ->whereIn('user_id', function ($query) {
                $query->select('id')
                    ->from('users')
                    ->where('institution_id', $this->institutionId);
            })
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'All other sessions revoked successfully.');
    }

    protected function detectBrowser(string $agent): string
    {
        return match (true) {
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
        return match (true) {
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
        return view('livewire.admin.log.session-log-component', [
            'sessions' => $this->getOnlineSessions(),
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Active Sessions | ' . setting('app_name', 'EMS'),
        ]);
    }
}