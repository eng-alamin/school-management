<?php

namespace App\Livewire\Admin\OfficeAccounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OfficeAccount;
use App\Models\OfficeDeposit;
use App\Models\OfficeExpense;
use Illuminate\Support\Facades\DB;

class TransactionComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 25;
    public string $sortField = 'date';
    public string $sortDirection = 'asc';

    // Filter
    public string $filterType = '';
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
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Build a UNION query joining account/head names to avoid N+1.
     * Global scopes are removed and replaced with explicit,
     * table-qualified filters to avoid "ambiguous column" SQL errors
     * once office_accounts / office_heads are joined (they share
     * institution_id and deleted_at column names with the base tables).
     */
    private function depositQuery()
    {
        return OfficeDeposit::withoutGlobalScopes()
            ->leftJoin('office_accounts', 'office_accounts.id', '=', 'office_deposits.account_id')
            ->leftJoin('office_heads', 'office_heads.id', '=', 'office_deposits.head_id')
            ->where('office_deposits.institution_id', institution()->id)
            ->whereNull('office_deposits.deleted_at')
            ->select([
                'office_deposits.id',
                'office_deposits.account_id',
                'office_accounts.name as account_name',
                'office_deposits.head_id',
                'office_heads.name as head_name',
                'office_deposits.voucher_no',
                'office_deposits.reference',
                'office_deposits.pay_via',
                'office_deposits.amount',
                'office_deposits.date',
                'office_deposits.attachment',
                DB::raw("'Deposit' as type"),
                DB::raw('office_deposits.amount as cr'),
                DB::raw('0 as dr'),
            ])
            ->when($this->filterAccount, fn ($q) => $q->where('office_deposits.account_id', $this->filterAccount));
    }

    private function expenseQuery()
    {
        return OfficeExpense::withoutGlobalScopes()
            ->leftJoin('office_accounts', 'office_accounts.id', '=', 'office_expenses.account_id')
            ->leftJoin('office_heads', 'office_heads.id', '=', 'office_expenses.head_id')
            ->where('office_expenses.institution_id', institution()->id)
            ->whereNull('office_expenses.deleted_at')
            ->select([
                'office_expenses.id',
                'office_expenses.account_id',
                'office_accounts.name as account_name',
                'office_expenses.head_id',
                'office_heads.name as head_name',
                'office_expenses.voucher_no',
                'office_expenses.reference',
                'office_expenses.pay_via',
                'office_expenses.amount',
                'office_expenses.date',
                'office_expenses.attachment',
                DB::raw("'Expense' as type"),
                DB::raw('0 as cr'),
                DB::raw('office_expenses.amount as dr'),
            ])
            ->when($this->filterAccount, fn ($q) => $q->where('office_expenses.account_id', $this->filterAccount));
    }

    public function render()
    {
        $deposits = $this->depositQuery();
        $expenses = $this->expenseQuery();

        $unionSql = "({$deposits->toSql()}) union ({$expenses->toSql()})";
        $unionBindings = array_merge($deposits->getBindings(), $expenses->getBindings());

        // ── Paginated transactions ──
        $transactions = DB::table(DB::raw("({$unionSql}) as transactions"))
            ->addBinding($unionBindings, 'where')
            ->when($this->search, fn ($q) => $q
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhere('pay_via', 'like', "%{$this->search}%")
                ->orWhere('voucher_no', 'like', "%{$this->search}%")
                ->orWhere('amount', 'like', "%{$this->search}%")
            )
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // ── Running balance: computed PER ACCOUNT, starting from each
        //    account's own opening_balance. Fetched fresh (unfiltered by
        //    search/type) so the ledger math stays correct regardless of
        //    what the user is currently filtering/searching for. ──
        $allRows = DB::table(DB::raw("({$unionSql}) as t"))
            ->addBinding($unionBindings, 'where')
            ->orderBy('account_id', 'asc')
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $openingBalances = OfficeAccount::pluck('opening_balance', 'id');
        $runningBalancePerAccount = [];
        $balanceMap = [];

        foreach ($allRows as $row) {
            if (! isset($runningBalancePerAccount[$row->account_id])) {
                $runningBalancePerAccount[$row->account_id] = (float) ($openingBalances[$row->account_id] ?? 0);
            }

            $runningBalancePerAccount[$row->account_id] += ($row->cr - $row->dr);
            $balanceMap[$row->type . '_' . $row->id] = $runningBalancePerAccount[$row->account_id];
        }

        $accounts = OfficeAccount::all();

        return view('livewire.admin.office-accounting.transaction-component')
            ->with('transactions', $transactions)
            ->with('balanceMap', $balanceMap)
            ->with('accounts', $accounts)
            ->layout('layouts.admin.app', [
                'title' => 'Transactions | ' . institution()->name,
            ]);
    }
}