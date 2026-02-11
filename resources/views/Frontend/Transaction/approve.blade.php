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
                    <span class="label">Gross Amount</span>
                    <span class="value">₹ {{ indianNumberFormat($transaction->gross_amount) }}</span>
                </div>
                <div class="summary-info-item">
                    <span class="label">TDS (2%)</span>
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
                            <th>Gross Amount</th>
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
                        <i class="fas fa-exclamation-triangle"></i> No active bank accounts found. Please add a bank account first from your profile.
                    </div>
                    @endif
                </div>

                @if($bankAccounts->count() > 0)
                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addBankRow">
                        <i class="fas fa-plus"></i> Add Another Bank Account
                    </button>
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
                    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                        <i class="fas fa-check-circle"></i> Submit & Approve
                    </button>
                </div>
                @endif
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
    });
</script>
@endsection
