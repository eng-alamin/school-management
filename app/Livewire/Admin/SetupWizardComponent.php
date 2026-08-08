<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Institution;
use Illuminate\Support\Facades\Auth;

class SetupWizardComponent extends Component
{
    /**
     * Presentation metadata for each setup step: category, titles, and route.
     * Lives here (not on the Model) because it's UI/display concern, not
     * business state — Institution model only tracks progress (done/not done).
     */
    public const STEP_META = [
        Institution::STEP_EMPLOYEE => [
            'category' => 'Employee',
            'title_bn' => 'কর্মচারী তৈরি করুন',
            'title_en' => 'Create Employee',
            'route'    => 'admin.employee.add',
        ],
        Institution::STEP_SESSION => [
            'category' => 'Academic',
            'title_bn' => 'সেশন - চেক ও আপডেট করুন',
            'title_en' => 'Session - Check & update',
            'route'    => 'admin.academic.sessions',
        ],
        Institution::STEP_CLASS_SETUP => [
            'category' => 'Academic',
            'title_bn' => 'ক্লাস সেটআপ - চেক ও আপডেট করুন',
            'title_en' => 'Class Setup - Check & update',
            'route'    => 'admin.academic.classes',
        ],
        Institution::STEP_CLASS_ASSIGN => [
            'category' => 'Academic',
            'title_bn' => 'ক্লাস অ্যাসাইন - চেক ও আপডেট করুন',
            'title_en' => 'Class Assign - Check & update',
            'route'    => 'admin.academic.class-assign',
        ],
        Institution::STEP_CLASS_SCHEDULE => [
            'category' => 'Academic',
            'title_bn' => 'ক্লাস শিডিউল - নতুন সময়সূচী যোগ করুন এবং ফিল্টার দ্বারা দেখুন',
            'title_en' => 'Class Schedules - Add new schedules and view by filters',
            'route'    => 'admin.academic.class-schedule.list',
        ],
        Institution::STEP_FEE_SETUP => [
            'category' => 'Student Accounting',
            'title_bn' => 'ফি সেটআপ করুন',
            'title_en' => 'Fee Setup',
            'route'    => 'admin.student-accounting.fee.setups',
        ],
        Institution::STEP_STUDENT => [
            'category' => 'Student',
            'title_bn' => 'শিক্ষার্থীর তৈরি করুন',
            'title_en' => 'Create Student',
            'route'    => 'admin.student.list',
        ],
        Institution::STEP_PARENT => [
            'category' => 'Parent',
            'title_bn' => 'অভিভাবকের তৈরি করুন',
            'title_en' => 'Create Parent',
            'route'    => 'admin.parent.list',
        ],
    ];

    public array $steps = [];

    public function mount(): void
    {
        $this->steps = self::STEP_META;
    }

    public function markComplete(string $stepKey): void
    {
        if (!array_key_exists($stepKey, self::STEP_META)) {
            $this->dispatch('toast', type: 'error', message: 'Invalid step.');
            return;
        }

        $institution = Auth::user()->institution;
        $institution->markStepComplete($stepKey);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($institution)
            ->withProperties(['icon' => 'check_circle', 'type' => 'setup_wizard_step', 'step' => $stepKey])
            ->log("Setup wizard step completed: {$stepKey}");

        $this->dispatch('toast', type: 'success', message: 'Step marked as complete!');
    }

    public function markIncomplete(string $stepKey): void
    {
        if (!array_key_exists($stepKey, self::STEP_META)) {
            $this->dispatch('toast', type: 'error', message: 'Invalid step.');
            return;
        }

        $institution = Auth::user()->institution;
        $institution->markStepIncomplete($stepKey);

        $this->dispatch('toast', type: 'info', message: 'Step marked as incomplete.');
    }

    public function skipWizard()
    {
        $institution = Auth::user()->institution;
        $institution->skipSetupWizard();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($institution)
            ->withProperties(['icon' => 'skip_next', 'type' => 'setup_wizard_skipped'])
            ->log('Setup wizard skipped');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Setup wizard skipped. Apni jekono somoy Settings theke abar dekhte parben.');
    }

    public function finishWizard()
    {
        $institution = Auth::user()->institution;
        $institution->setup_completed = true;
        $institution->save();

        activity()
            ->causedBy(Auth::user())
            ->performedOn($institution)
            ->withProperties(['icon' => 'flag', 'type' => 'setup_wizard_finished'])
            ->log('Setup wizard finished');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Setup complete! Welcome to your dashboard.');
    }

    public function render()
    {
        $institution = Auth::user()->institution;

        // Group steps by category, preserving definition order
        $grouped = [];
        foreach ($this->steps as $key => $meta) {
            $grouped[$meta['category']][$key] = $meta;
        }

        return view('livewire.admin.setup-wizard-component', [
            'institution'     => $institution,
            'groupedSteps'    => $grouped,
            'progressPercent' => $institution->setupProgressPercent(),
            'locale'          => app()->getLocale(), // server-side, session-driven
        ])->layout('layouts.admin.app', ['title' => 'Setup Wizard']);
    }
}