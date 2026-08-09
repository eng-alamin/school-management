<?php

namespace App\Livewire\Teacher\Grievance;

use App\Models\Employee;
use App\Models\Grievance;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateComponent extends Component
{
    public string $category = '';
    public string $subject = '';
    public string $description = '';
    public bool $isAnonymous = false;

    protected function currentEmployee(): ?Employee
    {
        return Employee::where('user_id', auth()->id())->whereNull('deleted_at')->first();
    }

    public function save()
    {
        $validated = $this->validate([
            'category' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'isAnonymous' => ['boolean'],
        ]);

        $employee = $this->currentEmployee();

        if (!$employee) {
            $this->dispatch('toast', type: 'error', message: 'Employee profile not found.');
            return;
        }

        DB::beginTransaction();
        try {
            Grievance::create([
                'institution_id' => $employee->institution_id,
                'student_id' => null,
                'complainant_type' => Grievance::TYPE_TEACHER,
                'complainant_id' => auth()->id(),
                'is_anonymous' => $validated['isAnonymous'],
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'status' => Grievance::STATUS_SUBMITTED,
            ]);

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Your grievance has been submitted.');

            return redirect()->route('teacher.grievances.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.teacher.grievance.create-component');
    }
}