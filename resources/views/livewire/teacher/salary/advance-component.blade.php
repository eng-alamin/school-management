<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5>Salary Advance</h5>
            <p>View your salary advance history and outstanding balances.</p>
        </div>

        <div class="card-header border-0">
            <div class="card-toolbar justify-content-end">
                @if($advances->total() > 10)
                    <div class="col-md-2">
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Advance Date</th>
                            <th>Amount</th>
                            <th>Installment</th>
                            <th>Remaining</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($advances as $i => $advance)
                            <tr wire:key="adv-{{ $advance->id }}">
                                <td class="text-muted">{{ $advances->firstItem() + $i }}</td>
                                <td>{{ $advance->advance_date->format('d M, Y') }}</td>
                                <td>${{ number_format($advance->amount, 2) }}</td>
                                <td>
                                    @if($advance->installment_amount)
                                        ${{ number_format($advance->installment_amount, 2) }} / month
                                    @else
                                        <span class="text-muted">Full (next payment)</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-weight:600;{{ $advance->remaining_amount > 0 ? 'color:#d97706' : 'color:#16a34a' }}">
                                        ${{ number_format($advance->remaining_amount, 2) }}
                                    </span>
                                </td>
                                <td>{{ $advance->reason ?? '—' }}</td>
                                <td>
                                    @if($advance->status === 'active')
                                        <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">Active</span>
                                    @else
                                        <span class="badge" style="background:#dcfce7;color:#166534;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">Settled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                    No salary advances found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $advances->firstItem() ?? 0 }}–{{ $advances->lastItem() ?? 0 }} of {{ $advances->total() }}</small>
            {{ $advances->links('vendor.pagination.custom') }}
        </div>

    </div>

</div>