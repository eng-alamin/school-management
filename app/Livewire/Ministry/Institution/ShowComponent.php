<?php

namespace App\Livewire\Ministry\Institution;

use Livewire\Component;
use App\Models\Institution;
use App\Models\Student;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class ShowComponent extends Component
{
    public Institution $institution;

    public int $totalStudents  = 0;
    public int $totalTeachers  = 0;
    public int $activeTeachers = 0;

    public $branches;

    public string $divisionLabel = '';

    // Reject modal state
    public bool $showRejectModal = false;
    public string $rejectReason = '';

    // Suspend modal state
    public bool $showSuspendModal = false;
    public string $suspendReason = '';

    public function mount(Institution $institution): void
    {
        $this->institution = $institution;

        $this->totalStudents = Student::whereRelation('user', 'institution_id', $institution->id)
            ->whereRelation('user', 'is_active', true)
            ->count();

        $this->totalTeachers = Employee::whereRelation('user', 'institution_id', $institution->id)
            ->whereRelation('user', 'role', 'teacher')
            ->count();

        $this->activeTeachers = Employee::whereRelation('user', 'institution_id', $institution->id)
            ->whereRelation('user', 'role', 'teacher')
            ->whereRelation('user', 'is_active', true)
            ->count();

        $this->divisionLabel = Institution::DIVISIONS[$institution->division] ?? ($institution->division ?? '');

        $this->branches = $institution->branches()
            ->select('id', 'institution_id', 'name', 'code', 'is_main', 'is_active')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Verification Actions
    |--------------------------------------------------------------------------
    */

    public function verifyInstitution(): void
    {
        DB::beginTransaction();

        try {
            $this->institution->update([
                'verification_status' => Institution::VERIFICATION_VERIFIED,
                'verified_by'          => auth()->id(),
                'verified_at'          => now(),
                'verification_note'    => null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->institution)
                ->withProperties(['status' => Institution::VERIFICATION_VERIFIED])
                ->tap(function ($activity) {
                    $activity->institution_id = $this->institution->id;
                })
                ->log('institution_verified');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Institution verified successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openRejectModal(): void
    {
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $this->institution->update([
                'verification_status' => Institution::VERIFICATION_REJECTED,
                'verified_by'          => auth()->id(),
                'verified_at'          => now(),
                'verification_note'    => $this->rejectReason,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->institution)
                ->withProperties(['status' => Institution::VERIFICATION_REJECTED, 'reason' => $this->rejectReason])
                ->tap(function ($activity) {
                    $activity->institution_id = $this->institution->id;
                })
                ->log('institution_rejected');

            DB::commit();

            $this->showRejectModal = false;
            $this->dispatch('toast', type: 'success', message: 'Institution rejected.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function openSuspendModal(): void
    {
        $this->suspendReason = '';
        $this->showSuspendModal = true;
    }

    public function confirmSuspend(): void
    {
        $this->validate([
            'suspendReason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        DB::beginTransaction();

        try {
            $this->institution->update([
                'verification_status' => Institution::VERIFICATION_SUSPENDED,
                'verified_by'          => auth()->id(),
                'verified_at'          => now(),
                'verification_note'    => $this->suspendReason,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->institution)
                ->withProperties(['status' => Institution::VERIFICATION_SUSPENDED, 'reason' => $this->suspendReason])
                ->tap(function ($activity) {
                    $activity->institution_id = $this->institution->id;
                })
                ->log('institution_suspended');

            DB::commit();

            $this->showSuspendModal = false;
            $this->dispatch('toast', type: 'success', message: 'Institution suspended.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function reactivateInstitution(): void
    {
        DB::beginTransaction();

        try {
            $this->institution->update([
                'verification_status' => Institution::VERIFICATION_VERIFIED,
                'verified_by'          => auth()->id(),
                'verified_at'          => now(),
                'verification_note'    => null,
            ]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($this->institution)
                ->withProperties(['status' => Institution::VERIFICATION_VERIFIED])
                ->tap(function ($activity) {
                    $activity->institution_id = $this->institution->id;
                })
                ->log('institution_reactivated');

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Institution reactivated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.ministry.institution.show-component')
            ->layout('layouts.ministry.app', [
                'title' => 'Institution Details | ' . setting('app_name', 'EMS'),
            ]);
    }
}