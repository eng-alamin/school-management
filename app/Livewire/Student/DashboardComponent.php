<?php

namespace App\Livewire\Student;

use App\Models\AcademicClassAssign;
use App\Models\ExamSchedule;
use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        $student = auth()->user()->student;

        $assign = $this->getClassAssign($student);

        $subjectCount   = $assign?->details()->count() ?? 0;
        $upcomingExams  = $this->getUpcomingExams($assign);
        $recentInvoices = $this->getRecentInvoices($student);
        $totalDue       = $this->getTotalDue($student);

        return view('livewire.student.dashboard-component', [
            'student'        => $student,
            'assign'         => $assign,
            'subjectCount'   => $subjectCount,
            'upcomingExams'  => $upcomingExams,
            'recentInvoices' => $recentInvoices,
            'totalDue'       => $totalDue,
        ])->layout('layouts.student.app', [
            'title' => 'Dashboard | Monarchy School',
        ]);
    }

    private function getClassAssign($student): ?AcademicClassAssign
    {
        if (! $student?->class_id) {
            return null;
        }

        return AcademicClassAssign::with(['class', 'section'])
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->first();
    }

    private function getUpcomingExams(?AcademicClassAssign $assign)
    {
        if (! $assign) {
            return collect();
        }

        return ExamSchedule::with([
                'examSetup',
                'examSetupDetail.classAssignDetail.subject',
            ])
            ->whereHas('examSetup', fn ($q) => $q->where('academic_class_assign_id', $assign->id))
            ->where('exam_date', '>=', now()->toDateString())
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    private function getRecentInvoices($student)
    {
        if (! $student) {
            return collect();
        }

        return $student->feeInvoices()
            ->orderByDesc('invoice_date')
            ->limit(5)
            ->get();
    }

    private function getTotalDue($student): float
    {
        if (! $student) {
            return 0.0;
        }

        return (float) $student->feeInvoices()
            ->where('payment_status', '!=', 'paid')
            ->sum('due_amount');
    }
}