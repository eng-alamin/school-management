<?php

namespace App\Livewire\Guardian\Grievance;

use App\Models\Grievance;
use Livewire\Component;
use Livewire\WithPagination;

class IndexComponent extends Component
{
    use WithPagination;

    public function render()
    {
        $grievances = Grievance::query()
            ->with('student')
            ->where('complainant_id', auth()->id())
            ->where('complainant_type', Grievance::TYPE_GUARDIAN)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.guardian.grievance.index-component', [
            'grievances' => $grievances,
        ]);
    }
}