<?php

namespace App\Livewire\SuperAdmin\Monitoring;

use Livewire\Component;

class ErrorLogsComponent extends Component
{
    public $logs = [];

    public function mount()
    {
        $this->loadLogs();
    }

    public function loadLogs()
    {
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);

            $this->logs = array_slice(
                array_reverse(explode("\n", $content)),
                0,
                50
            );
        }
    }

    public function render()
    {
        return view('livewire.super-admin.monitoring.error-logs-component')
            ->layout('layouts.superadmin.app');
    }
}