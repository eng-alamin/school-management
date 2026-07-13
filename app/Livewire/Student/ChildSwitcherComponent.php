<?php

namespace App\Livewire\Student;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChildSwitcherComponent extends Component
{
    public bool $isImpersonating = false;
    public $siblings = [];
    public ?int $currentStudentId = null;

    public function mount(): void
    {
        $impersonation = session('guardian_impersonation');

        // ✅ FIX 1: impersonation session-টা বর্তমান authenticated user-এর সাথে bind কিনা যাচাই করুন
        if (! $impersonation || ! $this->isValidImpersonation($impersonation)) {
            $this->isImpersonating = false;
            session()->forget('guardian_impersonation'); // stale/invalid হলে সাথে সাথে মুছে ফেলুন
            return;
        }

        $this->isImpersonating = true;
        $this->currentStudentId = auth()->user()->student?->id;

        $guardianProfile = Guardian::withoutGlobalScopes()
            ->with(['students' => function ($query) {
                $query->withoutGlobalScopes()->with(['class', 'section', 'institution']);
            }])
            ->find($impersonation['guardian_id']);

        $this->siblings = $guardianProfile?->students ?? collect();
    }

    /**
     * ✅ যাচাই করুন যে বর্তমান authenticated user সত্যিই এই guardian-এর
     * impersonation chain-এর একটি বৈধ সন্তান হিসেবে লগইন আছে।
     */
    protected function isValidImpersonation(array $impersonation): bool
    {
        $currentUserId = Auth::id();

        if (! $currentUserId) {
            return false;
        }

        return Guardian::withoutGlobalScopes()
            ->where('id', $impersonation['guardian_id'])
            ->where('user_id', $impersonation['guardian_user_id'])
            ->whereHas('students.user', function ($query) use ($currentUserId) {
                $query->where('users.id', $currentUserId);
            })
            ->exists();
    }

    public function switchChild(int $studentId)
    {
        try {
            $impersonation = session('guardian_impersonation');

            // ✅ FIX 2: প্রতিটা sensitive action-এর আগে validity যাচাই
            if (! $impersonation || ! $this->isValidImpersonation($impersonation)) {
                Log::warning('Invalid/stale impersonation attempt on switchChild', [
                    'user_id' => Auth::id(),
                ]);
                session()->forget('guardian_impersonation');
                session()->flash('error', 'Session expired. Please login again.');
                return $this->redirect(route('login'));
            }

            $isOwnChild = Guardian::withoutGlobalScopes()
                ->where('id', $impersonation['guardian_id'])
                ->where('user_id', $impersonation['guardian_user_id'])
                ->whereHas('students', function ($query) use ($studentId) {
                    $query->withoutGlobalScopes()->where('students.id', $studentId);
                })
                ->exists();

            if (! $isOwnChild) {
                Log::warning('Unauthorized child switch attempt', [
                    'guardian_user_id' => $impersonation['guardian_user_id'],
                    'attempted_student_id' => $studentId,
                    'actual_current_user_id' => Auth::id(),
                ]);
                session()->flash('error', 'Unauthorized access to that student.');
                return;
            }

            $student = Student::withoutGlobalScopes()
                ->with('user')
                ->find($studentId);

            if (! $student || ! $student->user) {
                session()->flash('error', 'Student account not found or has no linked user.');
                return;
            }

            request()->session()->regenerate();
            Auth::guard('web')->login($student->user);

            return $this->redirect(route('student.dashboard'), navigate: false);
        } catch (\Throwable $e) {
            Log::error('switchChild failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Something went wrong while switching child. Please try again.');
        }
    }

    public function backToParent()
    {
        try {
            $impersonation = session('guardian_impersonation');

            // ✅ FIX 3: এখানেও validity যাচাই আবশ্যক
            if (! $impersonation || ! $this->isValidImpersonation($impersonation)) {
                Log::warning('Invalid/stale impersonation attempt on backToParent', [
                    'user_id' => Auth::id(),
                ]);
                session()->forget('guardian_impersonation');
                session()->flash('error', 'Session expired. Please login again.');
                return $this->redirect(route('login'));
            }

            $guardianUser = User::find($impersonation['guardian_user_id']);

            if (! $guardianUser) {
                session()->flash('error', 'Parent account not found.');
                return;
            }

            session()->forget('guardian_impersonation');
            request()->session()->regenerate();
            Auth::guard('web')->login($guardianUser);

            return $this->redirect(route('parent.dashboard'), navigate: false);
        } catch (\Throwable $e) {
            Log::error('backToParent failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Something went wrong while returning to parent account. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.student.child-switcher-component');
    }
}