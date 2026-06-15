<?php

namespace App\Livewire\SuperAdmin\Monitoring;

use Livewire\Component;
use Illuminate\Support\Facades\Queue;

class QueueMonitorComponent extends Component
{
    public $queueSize = 0;

    public function mount()
    {
        $this->refreshQueue();
    }

    public function refreshQueue()
    {
        // Simple DB queue example
        $this->queueSize = \DB::table('jobs')->count();
    }

    public function render()
    {
        return view('livewire.super-admin.monitoring.queue-monitor-component')
            ->layout('layouts.superadmin.app');
    }
}