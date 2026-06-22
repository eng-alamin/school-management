<?php

namespace App\Livewire\SuperAdmin\Monitoring;

use Livewire\Component;

class PerformanceMetricsComponent extends Component
{
    public $memory;
    public $cpu;

    public function mount()
    {
        // Memory usage (safe)
        $this->memory = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';

        // CPU load (SAFE fallback)
        $this->cpu = $this->getCpuLoad();
    }

    private function getCpuLoad()
    {
        if (function_exists('sys_getloadavg')) {
            return sys_getloadavg()[0];
        }

        // Windows / unsupported server fallback
        return 'N/A';
    }

    public function render()
    {
        return view('livewire.super-admin.monitoring.performance-metrics-component')
            ->layout('layouts.superadmin.app', [
                'title' => 'Performance Metrics',
            ]);
    }
}