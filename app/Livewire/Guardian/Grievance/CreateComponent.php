<?php

namespace App\Livewire\Guardian\Grievance;

use App\Models\Grievance;
use App\Models\Guardian;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateComponent extends Component
{
    public ?int $studentId = null;
    public string $category = '';
    public string $subject = '';
    public string $description = '';
    public bool $isAnonymous = false;

    protected function currentGuardian(): ?Guardian
    {
        return Guardian::where('user_id', auth()->id())->first();
    }

    public function getChildrenProperty()
    {
        $guardian = $this->currentGuardian();

        return $guardian ? $guardian->students()->with('institution')->get() : collect();
    }

    public function save()
    {
        $validated = $this->validate([
            'studentId' => ['required', 'integer'],
            'category' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'isAnonymous' => ['boolean'],
        ]);

        $guardian = $this->currentGuardian();

        if (!$guardian) {
            $this->dispatch('toast', type: 'error', message: 'Guardian profile not found.');
            return;
        }

        // IDOR guard: re-verify the selected student is actually this guardian's
        // own child via the pivot relation — never trust the submitted studentId directly.
        $student = $guardian->students()->where('students.id', $validated['studentId'])->first();

        if (!$student) {
            $this->dispatch('toast', type: 'error', message: 'Invalid student selection.');
            return;
        }

        DB::beginTransaction();
        try {
            Grievance::create([
                'institution_id' => $student->institution_id,
                'student_id' => $student->id,
                'complainant_type' => Grievance::TYPE_GUARDIAN,
                'complainant_id' => auth()->id(),
                'is_anonymous' => $validated['isAnonymous'],
                'category' => $validated['category'],
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'status' => Grievance::STATUS_SUBMITTED,
            ]);

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Your grievance has been submitted.');

            return redirect()->route('guardian.grievances.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.guardian.grievance.create-component');
    }
}