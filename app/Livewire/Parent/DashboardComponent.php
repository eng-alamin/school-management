<?php

namespace App\Livewire\Parent;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DashboardComponent extends Component
{
    public $children;

    public function mount(): void
    {
        $guardianProfiles = Guardian::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->with([
                'institution',
                'students' => function ($query) {
                    $query->withoutGlobalScopes()
                        ->with(['class', 'section', 'institution']);
                },
            ])
            ->get();

        $this->children = $guardianProfiles
            ->flatMap(fn (Guardian $guardian) => $guardian->students)
            ->unique('id')
            ->values();
    }

    public function goToDashboard(int $studentId)
    {
        // Security fix: age eikhane kono ownership check chilo na — je
        // kono studentId diye call korle sheikhane login hoye jeto. Ekhon
        // shob Institution jure check kora hocche je ei studentId shotti-i
        // ei logged-in Guardian-er nijer sontan kina.
        $isOwnChild = Guardian::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->whereHas('students', function ($query) use ($studentId) {
                $query->withoutGlobalScopes()->where('students.id', $studentId);
            })
            ->exists();

        if (! $isOwnChild) {
            abort(403, 'Unauthorized access to student dashboard.');
        }

        $student = Student::withoutGlobalScopes()
            ->with('user')
            ->findOrFail($studentId);

        // Notun login student er nijer institution_id diye hocche, tai
        // porer shob page-e BelongsToInstitution scope automatically
        // shothik School er upor e apply hobe — alada session variable
        // set korar dorkar nai.
        Auth::login($student->user);

        request()->session()->regenerate();

        return $this->redirect(route('student.dashboard'));
    }

    public function render()
    {
        return view('livewire.parent.dashboard-component')
            ->layout('layouts.parent.app', [
                'title' => 'Dashboard | Monarchy School',
            ]);
    }
}