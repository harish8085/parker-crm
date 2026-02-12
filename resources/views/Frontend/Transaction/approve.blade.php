@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<style>
    .approve-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 20px;
    }
    .summary-info {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    .summary-info-item {
        flex: 1;
        min-width: 180px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .summary-info-item .label {
        display: block;
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .summary-info-item .value {
        font-size: 20px;
        font-weight: 700;
    }
    .summary-info-item .value.text-success {
        color: #28a745 !important;
    }
    .bank-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
    }
    .remaining-display {
        font-size: 18px;
        font-weight: 700;
    }
    .remaining-display.text-danger {
        color: #dc3545 !important;
    }
    .remaining-display.text-success {
        color: #28a745 !important;
    }
</style>
@endsection
@section('body')

<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">Approve Transaction #{{ $transaction->id }}</h3>
        <div class="settlement-btn-container">
            <a href="{{ url('/transactions') }}" style="text-decoration: none;">
                <button class="settlement-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
            </a>
        </div>
    </div>

    <div class="p-4">
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Transaction Summary (Read-Only) -->
        <div class="approve-card">
            <h6 class="mb-3"><strong>Transaction Summary</strong></h6>
            <div class="summary-info">
                <div class="summary-info-item">
                    <span class="label">Commission Amount</span>
                    <span class="value">₹ {{ indianNumberFormat($transaction->gross_amount) }}</span>
                </div>
                <div class="summary-info-item">
                    <span class="label">TDS ({{ $tdsPercentage }}%)</span>
                    <span class="value">₹ {{ indianNumberFormat($transaction->tds_amount) }}</span>
                </div>
                <div class="summary-info-item">
                    <span class="label">Advance Deduction</span>
                    <span class="value text-danger">₹ {{ indianNumberFormat($transaction->advance_amount) }}</span>
                </div>
                <div class="summary-info-item">
                    <span class="label">Net Payable</span>
                    <span class="value text-success">₹ {{ indianNumberFormat($transaction->net_payable) }}</span>
                </div>
            </div>
        </div>

        <!-- Application-wise Breakdown -->
        <div class="approve-card">
            <h6 class="mb-3"><strong>Application-wise Breakdown</strong></h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Application No.</th>
                            <th>Customer Name</th>
                            <th>Disbursement Amount</th>
                            <th>Submitted By</th>
                            <th>Company Receiving</th>
                            <th>Sharing Commission</th>
                            <th>Commission Amount</th>
                            <th>TDS</th>
                            <th>Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $index => $item)
                        @php
                            $app = $item->settlementDistribution && $item->settlementDistribution->application_id
                                ? \App\Models\Application::find($item->settlementDistribution->application_id)
                                : null;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $app->app_id ?? 'N/A' }}</td>
                            <td>{{ $app->customer_name ?? '-' }}</td>
                            <td>₹ {{ indianNumberFormat($app->disburse_amount ?? 0) }}</td>
                            <td>
                                @if($app && $app->user_id)
                                    @php $submitter = \App\Models\User::find($app->user_id); @endphp
                                    {{ $submitter ? $submitter->first_name . ' ' . ($submitter->last_name ?? '') : '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $app && $app->commission_rate ? $app->commission_rate . '%' : '-' }}</td>
                            <td>{{ $item->settlementDistribution && $item->settlementDistribution->received_rate ? $item->settlementDistribution->received_rate . '%' : '-' }}</td>
                            <td>₹ {{ indianNumberFormat($item->gross_amount) }}</td>
                            <td>₹ {{ indianNumberFormat($item->tds) }}</td>
                            <td>₹ {{ indianNumberFormat($item->net_amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bank Account Allocation Form -->
        <div class="approve-card">
            <h6 class="mb-3"><strong>Bank Account Allocation</strong></h6>
            <p class="text-muted mb-3">
                Allocate the net payable amount of <strong class="text-success">₹ {{ indianNumberFormat($transaction->net_payable) }}</strong> across your bank accounts. 
                The total must exactly match the net payable amount.
            </p>

            <form action="{{ url('/transactions/approve/' . $transaction->id) }}" method="POST" id="approveForm">
                @csrf

                <div id="bankRows">
                    @if($bankAccounts->count() > 0)
                    <div class="bank-row" data-index="0">
                        <div class="row align-items-end">
                            <div class="col-lg-5 mb-2">
                                <label class="form-label">Bank Account</label>
                                <select class="form-select bank-select" name="bank_accounts[0][bank_account_id]" required>
                                    <option value="">Select Bank Account</option>
                                    @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }} ({{ $bank->holder_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-4 mb-2">
                                <label class="form-label">Amount (₹)</label>
                                <input type="number" class="form-control bank-amount" name="bank_accounts[0][amount]" step="0.01" min="0.01" required placeholder="Enter amount">
                            </div>
                            <div class="col-lg-3 mb-2">
                                <button type="button" class="btn btn-danger btn-sm remove-bank-row" style="display:none;">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No active bank accounts found. Please add a new bank account below.
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addNewBankModal">
                            <i class="fas fa-university"></i> Add New Bank Account
                        </button>
                    </div>
                    @endif
                </div>

                @if($bankAccounts->count() > 0)
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addBankRow">
                            <i class="fas fa-plus"></i> Add Another Bank Account
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#addNewBankModal">
                            <i class="fas fa-university"></i> Add New Bank Account
                        </button>
                    </div>
                    <div>
                        <span class="text-muted me-2">Remaining:</span>
                        <span class="remaining-display" id="remainingAmount">₹ {{ indianNumberFormat($transaction->net_payable) }}</span>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted">Total Allocated:</span>
                        <span class="fw-bold" id="totalAllocated">₹ 0.00</span>
                        <span class="text-muted ms-3">of</span>
                        <span class="fw-bold text-success">₹ {{ indianNumberFormat($transaction->net_payable) }}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-danger btn-lg" data-bs-toggle="modal" data-bs-target="#rejectTransactionModal">
                            <i class="fas fa-times-circle"></i> Reject
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-check-circle"></i> Submit & Approve
                        </button>
                    </div>
                </div>
                @endif
            </form>
        </div>
    </div>
</div>

<!-- Reject Transaction Modal -->
<div class="modal fade" id="rejectTransactionModal" tabindex="-1" aria-labelledby="rejectTransactionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectTransactionModalLabel"><i class="fas fa-times-circle text-danger"></i> Reject Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ url('/transactions/reject/' . $transaction->id) }}" method="POST" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Please provide a reason for rejecting this transaction. This will be visible to the checker.</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" id="rejectionReason" rows="4" required placeholder="Enter the reason for rejection (e.g., commission dispute, incorrect advance deduction, etc.)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="confirmRejectBtn">
                        <i class="fas fa-times-circle"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Add New Bank Account Modal -->
<div class="modal fade" id="addNewBankModal" tabindex="-1" aria-labelledby="addNewBankModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNewBankModalLabel"><i class="fas fa-university"></i> Add New Bank Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickBankForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" value="{{ $transaction->user_id }}">
                <div class="modal-body">
                    <div id="quickBankErrors" class="alert alert-danger d-none">
                        <ul class="mb-0" id="quickBankErrorList"></ul>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bank_name" required placeholder="Enter bank name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="holder_name" required placeholder="Enter holder name">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="account_number" required placeholder="Enter account number" pattern="[0-9]+" title="Only digits allowed">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="confirm_account_number" required placeholder="Re-enter account number" pattern="[0-9]+" title="Only digits allowed">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ifsc_code" required placeholder="Enter IFSC code">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="branch_name" required placeholder="Enter branch name">
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-3"><strong>Documents</strong></h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">PAN Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="pan_photo" required accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">JPEG, JPG, PNG (max 4MB)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Aadhar Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="aadhar_photo" required accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">JPEG, JPG, PNG (max 4MB)</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Passbook Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="passbook_photo" required accept="image/jpeg,image/jpg,image/png">
                            <small class="text-muted">JPEG, JPG, PNG (max 4MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="quickBankSubmitBtn">
                        <i class="fas fa-save"></i> Save Bank Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        var netPayable = {{ $transaction->net_payable }};
        var bankIndex = 1;

        function updateTotals() {
            var total = 0;
            $('.bank-amount').each(function() {
                var val = parseFloat($(this).val()) || 0;
                total += val;
            });

            total = Math.round(total * 100) / 100;
            var remaining = Math.round((netPayable - total) * 100) / 100;

            $('#totalAllocated').text('₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            $('#remainingAmount').text('₹ ' + remaining.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

            if (remaining < 0) {
                $('#remainingAmount').removeClass('text-success').addClass('text-danger');
                $('#submitBtn').prop('disabled', true);
            } else if (remaining === 0) {
                $('#remainingAmount').removeClass('text-danger').addClass('text-success');
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#remainingAmount').removeClass('text-danger text-success');
                $('#submitBtn').prop('disabled', true);
            }

            // Show/hide remove buttons
            var rowCount = $('.bank-row').length;
            if (rowCount > 1) {
                $('.remove-bank-row').show();
            } else {
                $('.remove-bank-row').hide();
            }
        }

        // Listen for amount changes
        $(document).on('input', '.bank-amount', function() {
            updateTotals();
        });

        // Add new bank row
        $('#addBankRow').click(function() {
            var bankOptions = '';
            @foreach($bankAccounts as $bank)
            bankOptions += '<option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }} ({{ $bank->holder_name }})</option>';
            @endforeach

            var newRow = `
                <div class="bank-row" data-index="${bankIndex}">
                    <div class="row align-items-end">
                        <div class="col-lg-5 mb-2">
                            <label class="form-label">Bank Account</label>
                            <select class="form-select bank-select" name="bank_accounts[${bankIndex}][bank_account_id]" required>
                                <option value="">Select Bank Account</option>
                                ${bankOptions}
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control bank-amount" name="bank_accounts[${bankIndex}][amount]" step="0.01" min="0.01" required placeholder="Enter amount">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <button type="button" class="btn btn-danger btn-sm remove-bank-row">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#bankRows').append(newRow);
            bankIndex++;
            updateTotals();
        });

        // Remove bank row
        $(document).on('click', '.remove-bank-row', function() {
            $(this).closest('.bank-row').remove();
            updateTotals();
        });

        // Form validation before submit
        $('#approveForm').on('submit', function(e) {
            var total = 0;
            $('.bank-amount').each(function() {
                total += parseFloat($(this).val()) || 0;
            });

            total = Math.round(total * 100) / 100;

            if (total !== Math.round(netPayable * 100) / 100) {
                e.preventDefault();
                alert('Total allocated amount (₹' + total.toFixed(2) + ') must exactly match net payable amount (₹' + netPayable.toFixed(2) + ').');
                return false;
            }

            // Check for duplicate bank accounts
            var selectedBanks = [];
            var hasDuplicates = false;
            $('.bank-select').each(function() {
                var val = $(this).val();
                if (val && selectedBanks.indexOf(val) !== -1) {
                    hasDuplicates = true;
                }
                if (val) {
                    selectedBanks.push(val);
                }
            });

            if (hasDuplicates) {
                e.preventDefault();
                alert('Please select different bank accounts for each allocation.');
                return false;
            }

            if (!confirm('Are you sure you want to approve this transaction with the selected bank allocations?')) {
                e.preventDefault();
                return false;
            }
        });

        updateTotals();

        // ---- Quick Add Bank Account (AJAX) ----
        // Keep a dynamic list of bank options so new rows also get newly added banks
        var dynamicBankOptions = [];
        @foreach($bankAccounts as $bank)
        dynamicBankOptions.push({ id: '{{ $bank->id }}', label: '{{ $bank->bank_name }} - {{ $bank->account_number }} ({{ $bank->holder_name }})' });
        @endforeach

        function buildBankOptionsHtml() {
            var html = '<option value="">Select Bank Account</option>';
            dynamicBankOptions.forEach(function(b) {
                html += '<option value="' + b.id + '">' + b.label + '</option>';
            });
            return html;
        }

        // Override addBankRow to use dynamic options
        $('#addBankRow').off('click').on('click', function() {
            var optionsHtml = buildBankOptionsHtml();
            var newRow = `
                <div class="bank-row" data-index="${bankIndex}">
                    <div class="row align-items-end">
                        <div class="col-lg-5 mb-2">
                            <label class="form-label">Bank Account</label>
                            <select class="form-select bank-select" name="bank_accounts[${bankIndex}][bank_account_id]" required>
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="col-lg-4 mb-2">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control bank-amount" name="bank_accounts[${bankIndex}][amount]" step="0.01" min="0.01" required placeholder="Enter amount">
                        </div>
                        <div class="col-lg-3 mb-2">
                            <button type="button" class="btn btn-danger btn-sm remove-bank-row">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#bankRows').append(newRow);
            bankIndex++;
            updateTotals();
        });

        // Quick Bank Form Submit
        $('#quickBankForm').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $('#quickBankSubmitBtn');
            var $errors = $('#quickBankErrors');
            var $errorList = $('#quickBankErrorList');

            // Client-side: check account numbers match
            var accNum = $form.find('[name="account_number"]').val();
            var confirmAccNum = $form.find('[name="confirm_account_number"]').val();
            if (accNum !== confirmAccNum) {
                $errors.removeClass('d-none');
                $errorList.html('<li>Account number and confirm account number must match.</li>');
                return;
            }

            // Disable button and show loading
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            $errors.addClass('d-none');
            $errorList.html('');

            var formData = new FormData(this);

            $.ajax({
                url: '{{ route("transactions.quick-add-bank") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        var bank = response.bank;
                        var optionText = bank.bank_name + ' - ' + bank.account_number + ' (' + bank.holder_name + ')';
                        var newOption = '<option value="' + bank.id + '">' + optionText + '</option>';

                        // Add to dynamic list
                        dynamicBankOptions.push({ id: bank.id.toString(), label: optionText });

                        // Append to all existing dropdowns
                        $('.bank-select').each(function() {
                            $(this).append(newOption);
                        });

                        // If there are no bank rows yet (was showing "no accounts" warning), create the first row
                        if ($('.bank-row').length === 0) {
                            var optionsHtml = buildBankOptionsHtml();
                            var firstRow = `
                                <div class="bank-row" data-index="0">
                                    <div class="row align-items-end">
                                        <div class="col-lg-5 mb-2">
                                            <label class="form-label">Bank Account</label>
                                            <select class="form-select bank-select" name="bank_accounts[0][bank_account_id]" required>
                                                ${optionsHtml}
                                            </select>
                                        </div>
                                        <div class="col-lg-4 mb-2">
                                            <label class="form-label">Amount (₹)</label>
                                            <input type="number" class="form-control bank-amount" name="bank_accounts[0][amount]" step="0.01" min="0.01" required placeholder="Enter amount">
                                        </div>
                                        <div class="col-lg-3 mb-2">
                                            <button type="button" class="btn btn-danger btn-sm remove-bank-row" style="display:none;">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#bankRows').html(firstRow);
                            // Select the newly added bank
                            $('.bank-select').first().val(bank.id);
                            bankIndex = 1;
                        }

                        // Close modal and reset form
                        $('#addNewBankModal').modal('hide');
                        $form[0].reset();

                        alert('Bank account added successfully! You can now select it from the dropdown.');
                    } else {
                        $errors.removeClass('d-none');
                        $errorList.html('<li>' + (response.message || 'Something went wrong.') + '</li>');
                    }
                },
                error: function(xhr) {
                    $errors.removeClass('d-none');
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = xhr.responseJSON.errors;
                        var html = '';
                        $.each(errors, function(key, msgs) {
                            msgs.forEach(function(msg) {
                                html += '<li>' + msg + '</li>';
                            });
                        });
                        $errorList.html(html);
                    } else {
                        $errorList.html('<li>' + (xhr.responseJSON?.message || 'Failed to add bank account. Please try again.') + '</li>');
                    }
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Bank Account');
                }
            });
        });

        // Reset errors when modal is closed
        $('#addNewBankModal').on('hidden.bs.modal', function() {
            $('#quickBankErrors').addClass('d-none');
            $('#quickBankErrorList').html('');
        });

        // Reject form validation
        $('#rejectForm').on('submit', function(e) {
            var reason = $('#rejectionReason').val().trim();
            if (!reason) {
                e.preventDefault();
                alert('Please provide a rejection reason.');
                return false;
            }
            if (!confirm('Are you sure you want to reject this transaction? This action will notify the checker.')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection
