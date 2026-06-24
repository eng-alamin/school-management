<?php

namespace App\Livewire\SuperAdmin\Monitoring;

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class ServerStatusComponent extends Component
{
    public $status;

    public function mount()
    {
        $this->status = [
            'app' => 'running',
            'database' => $this->checkDB(),
            'cache' => Cache::store('file') ? 'ok' : 'fail',
            'storage' => is_writable(storage_path()) ? 'ok' : 'fail',
        ];
    }

    private function checkDB()
    {
        try {
            \DB::connection()->getPdo();
            return 'ok';
        } catch (\Exception $e) {
            return 'fail';
        }
    }

    public function render()
    {
        return view('livewire.super-admin.monitoring.server-status-component')
            ->layout('layouts.superadmin.app', [
                'title' => 'Server Status | ' . setting('app_name', 'EMS'),
            ]);
    }
}