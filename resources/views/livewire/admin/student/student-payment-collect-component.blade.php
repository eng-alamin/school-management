<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">payments</span>Collect Payment</h5>
            <p>Invoice #{{ $createdInvoiceNo }} — {{ $invoice->student->name }}</p>
        </div>

        <div class="form-section" style="padding-top:40px">

            <div class="table-responsive mb-3">
                <table class="table-loader">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Student</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            @if($createdInvoiceDue > 0)
                                <th style="width:160px">Pay Amount</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#{{ $createdInvoiceNo }}</td>
                            <td>{{ $invoice->student->name }}</td>
                            <td>{{ number_format($invoice->total_amount, 2) }}</td>
                            <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                            <td>{{ number_format($createdInvoiceDue, 2) }}</td>
                            @if($createdInvoiceDue > 0)
                                <td>
                                    <input type="number" step="0.01" min="0" max="{{ $createdInvoiceDue }}"
                                        class="form-control form-control-sm"
                                        wire:model.live="payAmount">
                                    @error('payAmount')
                                        <div class="text-danger" style="font-size:11px">{{ $message }}</div>
                                    @enderror
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ══ BUG FIX: due_amount 0 hoye gele (fully paid) form field ar
                 confirm button dekhano hobe na — age eta dekhato kintu submit korle
                 confusing validation error asto (max:0, min:0.01) ══ --}}
            @if($createdInvoiceDue <= 0)
                <div class="alert alert-success d-flex align-items-center gap-2" style="border-radius:10px">
                    <span class="material-icons-round">check_circle</span>
                    <span>This invoice has been fully paid. No due amount remaining.</span>
                </div>
            @else
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
            @endif

        </div>

        <div class="form-footer">
            <button class="btn-outline" type="button" wire:click="skipPayment" wire:loading.attr="disabled" wire:target="skipPayment,confirmPayment">
                {{ $createdInvoiceDue <= 0 ? 'Back' : 'Skip / Later' }}
            </button>

            @if($createdInvoiceDue > 0)
                <button class="btn-pink" type="button" wire:click="confirmPayment" wire:loading.attr="disabled" wire:target="confirmPayment,skipPayment">
                    <span wire:loading.remove wire:target="confirmPayment" style="display: inline-flex;align-items: center;gap: 6px">
                        <span class="material-icons-round">save</span> Confirm Payment
                    </span>

                    <span wire:loading wire:target="confirmPayment">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                        Processing...
                    </span>
                </button>
            @endif
        </div>

    </div>
</div>

@push('styles')
    <style>
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .table-loader {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .table-loader thead th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            border-bottom: 1px solid rgba(0,0,0,.08);
        }
        .table-loader tbody td {
            padding: 9px 10px;
            vertical-align: middle;
            font-size: 13px;
        }
        .pay-total-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 10px;
            background: rgba(224,82,82,.06);
            border-radius: 6px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            Livewire.on('date-updated', function (event) {
                var data = Array.isArray(event) ? event[0] : event;
                if (!data || !data.date) return;

                var input = document.querySelector('input[type="date"][wire\\:model="paymentDate"]');
                if (input) {
                    input.value = data.date;
                }
            });

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