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
        $guardianProfile = Guardian::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->whereHas('students', function ($query) use ($studentId) {
                $query->withoutGlobalScopes()->where('students.id', $studentId);
            })
            ->first();

        if (! $guardianProfile) {
            abort(403, 'Unauthorized access to student dashboard.');
        }

        $student = Student::withoutGlobalScopes()
            ->with('user')
            ->findOrFail($studentId);

        if (! $student->user) {
            abort(403, 'Student account has no linked user.');
        }

        // ✅ FIX: session data আগে সেট করে, তারপর regenerate, তারপর login।
        // এভাবে session fixation ঝুঁকি কমে এবং guardian_impersonation
        // regenerate-এর পরেও (migrate destroy=false হওয়ায়) নিরাপদভাবে বজায় থাকে।
        session([
            'guardian_impersonation' => [
                'guardian_user_id' => Auth::id(),
                'guardian_id' => $guardianProfile->id,
            ],
        ]);

        request()->session()->regenerate();

        Auth::login($student->user);

        return $this->redirect(route('student.dashboard'), navigate: false);
    }

    public function render()
    {
        return view('livewire.parent.dashboard-component')
            ->layout('layouts.parent.app', [
                'title' => 'Dashboard | Monarchy School',
            ]);
    }
}