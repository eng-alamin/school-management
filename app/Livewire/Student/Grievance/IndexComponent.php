<?php

namespace App\Livewire\Student\Grievance;

use App\Models\Grievance;
use Livewire\Component;
use Livewire\WithPagination;

class IndexComponent extends Component
{
    use WithPagination;

    public function render()
    {
        // Scoped strictly to the logged-in user's own submissions — IDOR-safe
        // by complainant_id, not just institution_id.
        $grievances = Grievance::query()
            ->where('complainant_id', auth()->id())
            ->where('complainant_type', Grievance::TYPE_STUDENT)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.student.grievance.index-component', [
            'grievances' => $grievances,
        ]);
    }
}