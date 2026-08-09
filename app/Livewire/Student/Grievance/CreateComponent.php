<?php

namespace App\Livewire\Student\Grievance;

use App\Models\Grievance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateComponent extends Component
{
    public string $category = '';
    public string $subject = '';
    public string $description = '';
    public bool $isAnonymous = false;

    // Re-fetched each request — never trust a stored public property for identity resolution
    protected function currentStudent(): ?Student
    {
        return Student::where('user_id', auth()->id())->first();
    }

    public function save()
    {
        $validated = $this->validate([
            'category' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'isAnonymous' => ['boolean'],
        ]);

        $student = $this->currentStudent();

        if (!$student) {
            $this->dispatch('toast', type: 'error', message: 'Student profile not found.');
            return;
        }

        DB::beginTransaction();
        try {
            Grievance::create([
                'institution_id' => $student->institution_id,
                'student_id' => null,
                'complainant_type' => Grievance::TYPE_STUDENT,
                'complainant_id' => auth()->id(),
                'is_anonymous' => $validated['isAnonymous'],
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'status' => Grievance::STATUS_SUBMITTED,
            ]);

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Your grievance has been submitted.');

            return redirect()->route('student.grievances.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.student.grievance.create-component');
    }
}