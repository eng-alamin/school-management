<?php

namespace App\Livewire\Accountant\OfficeAccounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OfficeDeposit;
use App\Models\OfficeExpense;
use Illuminate\Support\Facades\DB;

class TransactionComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 25;
    public string $sortField     = 'date';
    public string $sortDirection = 'asc';

    // Filter
    public string $filterType    = '';
    public string $filterAccount = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterAccount(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        // ── Deposits query ──
        $deposits = OfficeDeposit::select([
                'id', 'account_id', 'head_id', 'reference', 'description',
                'pay_via', 'amount', 'date', 'attachment',
                DB::raw("'Deposit' as type"),
                DB::raw('amount as cr'),
                DB::raw('0 as dr'),
            ])
            ->when($this->filterAccount, fn($q) => $q->where('account_id', $this->filterAccount));

        // ── Expenses query ──
        $expenses = OfficeExpense::select([
                'id', 'account_id', 'head_id', 'reference', 'description',
                'pay_via', 'amount', 'date', 'attachment',
                DB::raw("'Expense' as type"),
                DB::raw('0 as cr'),
                DB::raw('amount as dr'),
            ])
            ->when($this->filterAccount, fn($q) => $q->where('account_id', $this->filterAccount));

        // ── Union SQL (একবার build করা হচ্ছে) ──
        $unionSql      = "({$deposits->toSql()}) union ({$expenses->toSql()})";
        $unionBindings = array_merge($deposits->getBindings(), $expenses->getBindings());

        // ── Paginated transactions ──
        $transactions = DB::table(DB::raw("({$unionSql}) as transactions"))
            ->addBinding($unionBindings, 'where')
            ->when($this->search, fn($q) => $q
                ->where('reference',   'like', "%{$this->search}%")
                ->orWhere('pay_via',   'like', "%{$this->search}%")
                ->orWhere('amount',    'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")
            )
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // ── Running balance (আলাদা fresh query) ──
        $allRows = DB::table(DB::raw("({$unionSql}) as t"))
            ->addBinding($unionBindings, 'where')
            ->orderBy('date', 'asc')
            ->get();

        $runningBalance = 0;
        $balanceMap     = [];
        foreach ($allRows as $row) {
            $runningBalance      += ($row->cr - $row->dr);
            $balanceMap[$row->type . '_' . $row->id] = $runningBalance;
        }

        // ── Accounts for filter ──
        $accounts = \App\Models\OfficeAccount::all();

        return view('livewire.accountant.office-accounting.transaction-component')
            ->with('transactions', $transactions)
            ->with('balanceMap',   $balanceMap)
            ->with('accounts',     $accounts)
            ->layout('layouts.accountant.app', [
                'title' => 'Transactions | ' . institution()->name,
            ]);
    }
}