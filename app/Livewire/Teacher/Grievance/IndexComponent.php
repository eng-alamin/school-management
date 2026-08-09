<?php

namespace App\Livewire\Teacher\Grievance;

use App\Models\Grievance;
use Livewire\Component;
use Livewire\WithPagination;

class IndexComponent extends Component
{
    use WithPagination;

    public function render()
    {
        $grievances = Grievance::query()
            ->where('complainant_id', auth()->id())
            ->where('complainant_type', Grievance::TYPE_TEACHER)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.teacher.grievance.index-component', [
            'grievances' => $grievances,
        ]);
    }
}