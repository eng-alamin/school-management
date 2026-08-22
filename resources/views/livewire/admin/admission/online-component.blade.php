<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllAdmissions">
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">how_to_reg</span>
                Online Admissions
            </h5>
            <p id="cardHeaderSubtitle">Review, approve or reject student admission applications.</p>
        </div>

        {{-- ===== TOOLBAR (search + live filter) ===== --}}
        <div class="card-header border-0">
            <div class="card-toolbar">

                {{-- Search --}}
                <div class="card-toolbar-title">
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search name, mobile, guardian..."
                            class="tb-search"/>
                    </div>
                </div>

                {{-- Class filter --}}
                <div>
                    <select wire:model.live="filterClass" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Classes</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Session filter --}}
                <div>
                    <select wire:model.live="filterSession" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Sessions</option>
                        @foreach ($sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status filter --}}
                <div>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm" style="min-width:140px">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                {{-- Per page --}}
                @if($admissions->total() > 10)
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="perPage">
                            <option value="10">10 / page</option>
                            <option value="25">25 / page</option>
                            <option value="50">50 / page</option>
                        </select>
                    </div>
                @endif

            </div>
        </div>

        {{-- ===== TABLE ===== --}}
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Application No</th>
                            <th>Type</th>
                            <th>Class</th>
                            <th>Session</th>
                            <th>Mobile</th>
                            <th>Guardian</th>
                            <th>Applied On</th>
                            <th>Status</th>
                            <th class="no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admissions as $admission)
                        <tr wire:key="admission-row-{{ $admission->id }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $admission->photo ? asset('storage/' . $admission->photo) : asset('assets/img/boy.jpg') }}"
                                        style="width:36px;height:36px;border-radius:8px;object-fit:cover;" alt="">
                                    <span class="fw-500">{{ $admission->applicant_name }}</span>
                                </div>
                            </td>
                            <td>{{ $admission->application_no }}</td>
                            <td>{{ $admission->is_new ? 'New' : 'Existing' }}</td>
                            <td>{{ $admission->appliedClass?->name ?? '—' }}</td>
                            <td>{{ $admission->appliedSession?->name ?? '—' }}</td>
                            <td>{{ $admission->mobile }}</td>
                            <td>{{ $admission->guardian_name }}</td>
                            <td>{{ $admission->created_at?->format('d M Y') }}</td>
                            <td>
                                @if($admission->status === 'pending')
                                    <span class="badge" style="background:#fef3c7;color:#92400e;">Pending</span>
                                @elseif($admission->status === 'approved')
                                    <span class="badge" style="background:#dcfce7;color:#166534;">Approved</span>
                                @else
                                    <span class="badge" style="background:#fee2e2;color:#991b1b;">Rejected</span>
                                @endif
                            </td>
                            <td class="no-print">
                                <div class="d-flex gap-1">
                                    <button class="act-btn view" title="View" wire:click="viewRecord({{ $admission->id }})">
                                        <span class="material-icons-round">visibility</span>
                                    </button>

                                    @if($admission->status === 'pending')
                                        <button class="act-btn edit" title="Approve"
                                            wire:click="confirmApproveRecord({{ $admission->id }})"
                                            wire:loading.attr="disabled" wire:target="confirmApproveRecord({{ $admission->id }})">
                                            <span class="material-icons-round">check_circle</span>
                                        </button>
                                        <button class="act-btn delete" title="Reject"
                                            wire:click="confirmRejectRecord({{ $admission->id }})">
                                            <span class="material-icons-round">cancel</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <span class="material-icons-round d-block mb-2" style="font-size:2.5rem;opacity:.2">how_to_reg</span>
                                No admission applications found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $admissions->firstItem() ?? 0 }}–{{ $admissions->lastItem() ?? 0 }} of {{ $admissions->total() }}</small>
            {{ $admissions->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- ===== VIEW MODAL (Approve korar age full data dekhar jonno) ===== --}}
    @if($showViewModal && $viewAdmission)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span class="material-icons-round align-middle me-1" style="font-size:18px">how_to_reg</span>
                            Admission Application Details
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="d-flex align-items-center gap-3 mb-4">
                            <img src="{{ $viewAdmission->photo ? asset('storage/' . $viewAdmission->photo) : asset('assets/img/boy.jpg') }}"
                                style="width:64px;height:64px;border-radius:12px;object-fit:cover;" alt="">
                            <div>
                                <h5 class="mb-0">{{ $viewAdmission->applicant_name }}</h5>
                                <small class="text-muted">Applied for {{ $viewAdmission->appliedClass?->name ?? '—' }} — {{ $viewAdmission->appliedSession?->name ?? '—' }}</small>
                            </div>
                        </div>

                        <h6 class="fw-bold text-danger">Student Information</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><small class="text-muted d-block">Gender</small>{{ ucfirst($viewAdmission->gender ?? '—') }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Blood Group</small>{{ $viewAdmission->blood_group ?? '—' }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Date of Birth</small>{{ $viewAdmission->dob ?? '—' }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Religion</small>{{ ucfirst($viewAdmission->religion ?? '—') }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Mobile</small>{{ $viewAdmission->mobile }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Email</small>{{ $viewAdmission->email ?? '—' }}</div>
                            <div class="col-md-6"><small class="text-muted d-block">Present Address</small>{{ $viewAdmission->present_address ?? '—' }}</div>
                            <div class="col-md-6"><small class="text-muted d-block">Permanent Address</small>{{ $viewAdmission->permanent_address ?? '—' }}</div>
                        </div>

                        <h6 class="fw-bold text-danger">Guardian Information</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4"><small class="text-muted d-block">Guardian Name</small>{{ $viewAdmission->guardian_name }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Relation</small>{{ $viewAdmission->guardian_relation }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Occupation</small>{{ $viewAdmission->guardian_occupation ?? '—' }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Father's Name</small>{{ $viewAdmission->father_name ?? '—' }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Mother's Name</small>{{ $viewAdmission->mother_name ?? '—' }}</div>
                            <div class="col-md-4"><small class="text-muted d-block">Guardian Mobile</small>{{ $viewAdmission->guardian_mobile ?? '—' }}</div>
                            <div class="col-md-6"><small class="text-muted d-block">Guardian Email</small>{{ $viewAdmission->guardian_email ?? '—' }}</div>
                            <div class="col-md-6"><small class="text-muted d-block">Guardian Address</small>{{ $viewAdmission->guardian_address ?? '—' }}</div>
                        </div>

                        @if($viewAdmission->previous_institution || $viewAdmission->qualification)
                            <h6 class="fw-bold text-danger">Previous Institution</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-md-6"><small class="text-muted d-block">Institution</small>{{ $viewAdmission->previous_institution ?? '—' }}</div>
                                <div class="col-md-6"><small class="text-muted d-block">Qualification</small>{{ $viewAdmission->qualification ?? '—' }}</div>
                            </div>
                        @endif

                        @if($viewAdmission->status === 'rejected' && $viewAdmission->rejection_reason)
                            <div class="alert alert-danger mt-2 mb-0">
                                <strong>Rejection Reason:</strong> {{ $viewAdmission->rejection_reason }}
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-light" wire:click="closeViewModal">Close</button>

                        @if($viewAdmission->status === 'pending')
                            <button class="btn btn-danger" wire:click="confirmRejectRecord({{ $viewAdmission->id }})">
                                <span class="material-icons-round align-middle" style="font-size:16px">cancel</span> Reject
                            </button>
                            <button class="btn btn-success" wire:click="confirmApproveRecord({{ $viewAdmission->id }})">
                                <span class="material-icons-round align-middle" style="font-size:16px">check_circle</span> Approve
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== REJECT CONFIRM (with reason input) ===== --}}
    @if($confirmReject)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h6 class="fw-700 mb-0">Reject Application</h6>
                        <button type="button" class="btn-close" wire:click="$set('confirmReject', false)"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <p class="text-muted small">
                            Reject korar karon likhun — guardian ebong student (jodi email thake) ke email e ei karon ta jabe.
                        </p>
                        <textarea wire:model="rejectReason" rows="4" class="form-control @error('rejectReason') is-invalid @enderror"
                            placeholder="e.g. Seat not available for the applied class, documents incomplete..."></textarea>
                        @error('rejectReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmReject', false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="rejectRecord" wire:loading.attr="disabled" wire:target="rejectRecord">
                            <span wire:loading wire:target="rejectRecord" class="spinner-border spinner-border-sm me-1"></span>
                            Reject & Notify
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════ STEP 1: FEE CONFIRMATION MODAL (New Student only) ══════════════ --}}
    @if($showFeeModal)
        <div class="modal-dark-overlay" wire:click.self="closeFeeModal">
            <div class="modal-box">
                <div class="modal-box-header">
                    <h5>
                        <span class="material-icons-round" style="font-size:20px;vertical-align:middle;margin-right:6px">receipt_long</span>
                        Confirm Fee for Invoice
                    </h5>
                    <button type="button" class="modal-box-close" wire:click="closeFeeModal">&times;</button>
                </div>

                <div class="modal-box-body">
                    @if(empty($feeItems))
                        <p class="text-muted mb-0">No fee setup found for this class. Student will be approved without any invoice.</p>
                    @else
                        @foreach($feeItems as $key => $item)
                            <label class="fee-item-row">
                                <span class="fee-item-left">
                                    <input type="checkbox" wire:model.live="selectedFees.{{ $key }}">
                                    <span>{{ $item['label'] }}</span>
                                </span>
                                <span class="fee-item-amount">{{ number_format($item['amount'], 2) }}</span>
                            </label>
                        @endforeach

                        <div class="fee-item-row fee-total-row">
                            <span>TOTAL</span>
                            <span>{{ number_format($this->feeModalTotal, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="modal-box-footer">
                    <button type="button" class="btn-outline" wire:click="closeFeeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="generateInvoiceAndApprove" wire:loading.attr="disabled" wire:target="generateInvoiceAndApprove">
                        <span wire:loading.remove wire:target="generateInvoiceAndApprove">Confirm &amp; Generate Invoice</span>
                        <span wire:loading wire:target="generateInvoiceAndApprove">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════ STEP 2: PAYMENT COLLECT MODAL ══════════════ --}}
    @if($showPaymentModal)
        <div class="modal-dark-overlay" wire:click.self="closePaymentModal">
            <div class="modal-box" style="max-width:640px">

                <div class="modal-box-header">
                    <h5 class="mb-0">Payment Collect</h5>
                    <button type="button" class="modal-box-close" wire:click="closePaymentModal">&times;</button>
                </div>

                <div class="modal-box-body">

                    <div class="table-responsive mb-3">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Due Amount</th>
                                    <th style="width:160px">Pay Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#{{ $createdInvoiceNo }}</td>
                                    <td>{{ number_format($createdInvoiceDue, 2) }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="{{ $createdInvoiceDue }}"
                                            class="form-control form-control-sm"
                                            wire:model.live="payAmount">
                                        @error('payAmount')
                                            <div class="text-danger" style="font-size:11px">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" wire:model="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="bank">Bank</option>
                                <option value="cheque">Cheque</option>
                            </select>
                            @error('paymentMethod') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" wire:model="paymentDate">
                            @error('paymentDate') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Office Account (Optional)</label>
                            <select class="form-select" wire:model="officeAccountId">
                                <option value="">-- Select --</option>
                                @foreach($officeAccounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                            @error('officeAccountId') <div class="text-danger" style="font-size:11px">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" rows="2" wire:model="paymentRemarks"></textarea>
                        </div>
                    </div>

                    <div class="pay-total-row mt-3">
                        <span>Total Pay Amount :</span>
                        <span class="fw-bold">{{ number_format($payAmount, 2) }}</span>
                    </div>

                </div>

                <div class="modal-box-footer">
                    <button type="button" class="btn-outline" wire:click="skipPayment">Skip / Later</button>
                    <button type="button" class="btn btn-primary" wire:click="confirmPayment" wire:loading.attr="disabled" wire:target="confirmPayment">
                        <span wire:loading.remove wire:target="confirmPayment">Confirm Payment</span>
                        <span wire:loading wire:target="confirmPayment">Processing...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {

        // ── Payment confirm howar por Invoice notun tab e print korar jonno ──
        Livewire.on('open-invoice-print', function (event) {
            var data = Array.isArray(event) ? event[0] : event;

            var printWindow = window.open('', '_blank', 'width=800,height=650');
            if (!printWindow) {
                alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
                return;
            }

            var itemRows = '';
            (data.items || []).forEach(function (item) {
                var feeLabel = item.feeTypeName + (item.monthLabel ? ' — ' + item.monthLabel : '');
                itemRows += `
                    <tr>
                        <td>${feeLabel}</td>
                        <td>${item.amount}</td>
                        <td>${item.discount}</td>
                        <td>${item.fine}</td>
                    </tr>
                `;
            });

            var html = `
                <html>
                    <head>
                        <title>Fee Invoice #${data.invoiceNo}</title>
                        <style>
                            * { box-sizing: border-box; }
                            body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                            h4 { margin: 0 0 4px 0; }
                            p { margin: 2px 0; color: #555; font-size: 13px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
                            th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 13px; }
                            thead th { background: #f5f5f5; }
                            .total-row td { font-weight: bold; background: #fafafa; }
                        </style>
                    </head>
                    <body>
                        <h4>Fee Invoice</h4>
                        <p>Invoice No: #${data.invoiceNo}</p>
                        <p>Student: ${data.studentName}</p>
                        <p>Payment Date: ${data.paymentDate}</p>

                        <table>
                            <thead>
                                <tr>
                                    <th>Fees Type</th>
                                    <th>Amount</th>
                                    <th>Discount</th>
                                    <th>Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemRows}
                            </tbody>
                        </table>

                        <table>
                            <thead>
                                <tr><th>Total Amount</th><th>Paid Amount</th><th>Due Amount</th></tr>
                            </thead>
                            <tbody>
                                <tr class="total-row">
                                    <td>${data.totalAmount}</td>
                                    <td>${data.paidAmount}</td>
                                    <td>${data.dueAmount}</td>
                                </tr>
                            </tbody>
                        </table>
                    </body>
                </html>
            `;

            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();

            setTimeout(() => {
                printWindow.print();
            }, 250);
        });

    });
</script>
@endpush