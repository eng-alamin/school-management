<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeInvoice;

class InvoiceViewComponent extends Component
{
    public Student $student;
    public FeeInvoice $invoice;

    public string $routePrefix = '';

    public function mount(Student $student, FeeInvoice $invoice): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        // ------------------------------------------------------------
        // IDOR Guard: URL এর {invoice} অবশ্যই {student}-এর নিজের হতে হবে।
        // নাহলে student ID/invoice ID বদলে অন্যের invoice দেখা সম্ভব হতো।
        // ------------------------------------------------------------
        abort_unless($invoice->student_id === $student->id, 404);

        $this->student = $student->load([
            'session',
            'class',
            'section',
            'group',
        ]);

        $this->invoice = $invoice->load([
            'items.feeSetup.feeType',
        ]);
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function render()
    {
        return view('livewire.admin.student.invoice-view-component')
            ->layout('layouts.admin.app', [
                'title' => 'Invoice #' . $this->invoice->invoice_no . ' | ' . institution()->name,
            ]);
    }
}