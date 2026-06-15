<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'billing:check-overdue';
    protected $description = 'Mark pending invoices as overdue if past due date';

    public function handle()
    {
        $count = Invoice::where('status', 'pending')
            ->whereDate('due_date', '<', now())
            ->update(['status' => 'overdue']);

        $this->info("{$count} invoice(s) marked as overdue.");
    }
}