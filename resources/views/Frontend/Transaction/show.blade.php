@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<style>
    .invoice-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 30px;
        margin-bottom: 20px;
    }
    .invoice-header {
        border-bottom: 2px solid #333;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .invoice-header h4 {
        margin: 0;
        font-weight: 700;
    }
    .invoice-meta {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .invoice-meta-item {
        flex: 1;
        min-width: 150px;
    }
    .invoice-meta-item label {
        display: block;
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .invoice-meta-item span {
        font-size: 16px;
        font-weight: 600;
    }
    .summary-table {
        width: 100%;
        max-width: 400px;
        margin-left: auto;
    }
    .summary-table td {
        padding: 8px 12px;
    }
    .summary-table .label-td {
        text-align: right;
        color: #666;
    }
    .summary-table .value-td {
        text-align: right;
        font-weight: 600;
    }
    .summary-table .total-row td {
        border-top: 2px solid #333;
        font-size: 18px;
        font-weight: 700;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-badge.approved {
        background: #cce5ff;
        color: #004085;
    }
    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.rejected {
        background: #f8d7da;
        color: #721c24;
    }
    .status-badge.cancelled {
        background: #e2e3e5;
        color: #383d41;
    }
    .dist-table th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .dist-table td {
        font-size: 14px;
    }
</style>
@endsection
@section('body')

<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">Transaction Detail</h3>
        <div class="settlement-btn-container">
            <a href="{{ url('/transactions') }}" style="text-decoration: none;">
                <button class="settlement-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
            </a>
        </div>
    </div>

    <div class="p-4">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Invoice Header -->
        <div class="invoice-card">
            <div class="invoice-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4>Transaction Invoice {{ $transaction->transaction_id ?? '#'.$transaction->id }}</h4>
                    <span class="status-badge {{ $transaction->status }}">{{ ucwords($transaction->status) }}</span>
                </div>
            </div>

            <!-- Transaction Meta -->
            <div class="invoice-meta mb-4">
                <div class="invoice-meta-item">
                    <label>Channel</label>
                    <span>{{ $channelUser ? $channelUser->first_name . ' ' . $channelUser->last_name : 'N/A' }}</span>
                </div>
                <div class="invoice-meta-item">
                    <label>Created Date</label>
                    <span>{{ $transaction->created_at->format('d M Y, h:i A') }}</span>
                </div>
                @if($transaction->approved_at)
                <div class="invoice-meta-item">
                    <label>Approved Date</label>
                    <span>{{ $transaction->approved_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
                @if($transaction->completed_at)
                <div class="invoice-meta-item">
                    <label>Completed Date</label>
                    <span>{{ $transaction->completed_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
                @if($transaction->rejected_at)
                <div class="invoice-meta-item">
                    <label>Rejected Date</label>
                    <span>{{ $transaction->rejected_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
            </div>

            @if(in_array($transaction->status, ['rejected', 'cancelled']) && $transaction->rejection_reason)
            <div class="alert alert-danger mb-4">
                <h6 class="alert-heading"><i class="fas fa-times-circle"></i> Transaction Rejected</h6>
                <p class="mb-0"><strong>Reason:</strong> {{ $transaction->rejection_reason }}</p>
                @if($transaction->rejected_at)
                <small class="text-muted">Rejected on {{ $transaction->rejected_at->format('d M Y, h:i A') }}</small>
                @endif
            </div>
            @endif

            <!-- Application-wise Distribution -->
            <h6 class="mb-3"><strong>Application-wise Breakdown</strong></h6>
            @php $isContest = (($transaction->settlement->settlement_type ?? 'commission') === 'contest'); @endphp
            <div class="table-responsive">
                <table class="table table-bordered dist-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Application No.</th>
                            <th>Customer Name</th>
                            <th>Disbursement Amount</th>
                            <th>Submitted By</th>
                            <th>{{ $isContest ? 'Contest Receiving' : 'Company Receiving' }}</th>
                            <th>{{ $isContest ? 'Channel Sharing' : 'Sharing Commission' }}</th>
                            <th>{{ $isContest ? 'Channel Contest Rate' : 'Channel Commission' }}</th>
                            <th>{{ $isContest ? 'Channel Contest Amount' : 'Commission Amount' }}</th>
                            <th>TDS</th>
                            <th>Advance</th>
                            <th>{{ $isContest ? 'Net Value' : 'Net Amount' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->items as $index => $item)
                        @php
                            $dist = $item->settlementDistribution;
                            $app = $dist && $dist->application_id
                                ? \App\Models\Application::find($dist->application_id)
                                : null;
                            $contest = null;
                            if ($isContest && $dist && !empty($dist->contest_mis_id)) {
                                $contest = \Illuminate\Support\Facades\DB::table('contest_mis')->where('id', $dist->contest_mis_id)->first();
                            } elseif ($isContest && $app && !empty($app->app_id)) {
                                $contest = \Illuminate\Support\Facades\DB::table('contest_mis')->where('application_no', $app->app_id)->orderByDesc('id')->first();
                            }
                            $baseRate = $isContest ? (float) ($contest->contest_rate ?? 0) : (float) ($app->commission_rate ?? 0);
                            $sharingRate = (float) ($dist->received_rate ?? 0);
                            $channelRate = ($baseRate > 0 && $sharingRate > 0) ? round($baseRate * ($sharingRate / 100), 2) : null;
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
                            <td>{{ $baseRate > 0 ? $baseRate . '%' : '-' }}</td>
                            <td>{{ $sharingRate > 0 ? $sharingRate . '%' : '-' }}</td>
                            <td>{{ $channelRate !== null ? $channelRate . '%' : '-' }}</td>
                            <td>₹ {{ indianNumberFormat($item->gross_amount) }}</td>
                            <td>₹ {{ indianNumberFormat($item->tds) }}</td>
                            <td>
                                @if($item->advance_amount > 0)
                                    <span class="text-danger">₹ {{ indianNumberFormat($item->advance_amount) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>₹ {{ indianNumberFormat($item->net_amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <table class="summary-table">
                <tr>
                    <td class="label-td">Commission Amount:</td>
                    <td class="value-td">₹ {{ indianNumberFormat($transaction->gross_amount) }}</td>
                </tr>
                <tr>
                    <td class="label-td">TDS ({{ $tdsPercentage }}%):</td>
                    <td class="value-td">₹ {{ indianNumberFormat($transaction->tds_amount) }}</td>
                </tr>
                @if($transaction->advance_amount > 0)
                <tr>
                    <td class="label-td">Advance Deduction:</td>
                    <td class="value-td text-danger">- ₹ {{ indianNumberFormat($transaction->advance_amount) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td class="label-td">Net Payable:</td>
                    <td class="value-td text-success">₹ {{ indianNumberFormat($transaction->net_payable) }}</td>
                </tr>
            </table>
        </div>

        <!-- Bank Allocation Details (if approved/completed) -->
        @if($transaction->bankAllocations->count() > 0)
        <div class="invoice-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><strong>Bank Account Allocations</strong></h6>
                @php $roleId = auth()->user()->roles[0]->id; @endphp
                @if($roleId == 36 && in_array($transaction->status, ['approved', 'completed']))
                <div class="d-flex gap-2">
                    <a href="{{ url('/transactions/' . $transaction->id . '/export-allocations') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-excel"></i> Export Allocations
                    </a>
                    @if($transaction->status === 'approved')
                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadUTRModal">
                        <i class="fas fa-upload"></i> Upload UTR
                    </button>
                    @endif
                </div>
                @endif
            </div>
            <div class="table-responsive">
                <table class="table table-bordered dist-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Holder</th>
                            <th>Bank Name</th>
                            <th>Account Number</th>
                            <th>IFSC Code</th>
                            <th>PAN Number</th>
                            <th>Aadhar Number</th>
                            <th>Amount</th>
                            <th>UTR Number</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction->bankAllocations as $index => $allocation)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $allocation->bankAccount->holder_name ?? 'N/A' }}</td>
                            <td>{{ $allocation->bankAccount->bank_name ?? 'N/A' }}</td>
                            <td>{{ $allocation->bankAccount->account_number ?? 'N/A' }}</td>
                            <td>{{ $allocation->bankAccount->ifsc_code ?? 'N/A' }}</td>
                            <td>{{ $allocation->bankAccount->pan_number ?? '-' }}</td>
                            <td>{{ $allocation->bankAccount->aadhar_number ?? '-' }}</td>
                            <td>₹ {{ indianNumberFormat($allocation->amount) }}</td>
                            <td>{{ $allocation->utr_number ?? '-' }}</td>
                            <td>{{ $allocation->payment_date ? $allocation->payment_date->format('d-m-Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Upload UTR Modal -->
        @if(isset($roleId) && $roleId == 36 && $transaction->status === 'approved')
        <div class="modal fade" id="uploadUTRModal" tabindex="-1" aria-labelledby="uploadUTRModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadUTRModalLabel">Upload UTR Numbers</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ url('/transactions/' . $transaction->id . '/import-utr') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                Download the allocations Excel first using <strong>"Export Allocations"</strong>, fill in the <strong>UTR Number</strong> column, then upload the file here.
                            </p>
                            <div class="mb-3">
                                <label for="utr_file" class="form-label">Select Excel File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="utr_file" name="utr_file" accept=".xlsx,.xls,.csv" required>
                                <small class="text-muted">Accepted formats: .xlsx, .xls, .csv (max 5MB)</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Upload & Update UTR
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <!-- Action buttons based on role -->
        <div class="d-flex justify-content-end gap-2 mt-3">
            @php if(!isset($roleId)) $roleId = auth()->user()->roles[0]->id; @endphp

            @if(in_array($roleId, [2, 3]) && $transaction->status === 'pending')
                <a href="{{ url('/transactions/approve/' . $transaction->id) }}" class="btn btn-primary">
                    <i class="fas fa-check-circle"></i> Approve & Add Bank Details
                </a>
            @endif

            @if($roleId == 36 && $transaction->status === 'approved')
                <button class="btn btn-success complete-transaction-btn" data-id="{{ $transaction->id }}">
                    <i class="fas fa-check"></i> Mark as Completed
                </button>
            @endif

            @if($roleId == 36 && $transaction->status === 'rejected')
                <form action="{{ url('/transactions/reprocess/' . $transaction->id) }}" method="POST" class="d-inline" id="reprocessForm">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-redo"></i> Reprocess
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function() {
        // Complete transaction button
        // Reprocess confirmation
        $('#reprocessForm').on('submit', function(e) {
            if (!confirm('Are you sure you want to reprocess this transaction? The current transaction will be cancelled and distributions will be unlinked for reprocessing.')) {
                e.preventDefault();
                return false;
            }
        });

        // Complete transaction button
        $('.complete-transaction-btn').click(function() {
            var transactionId = $(this).data('id');
            if (!confirm('Are you sure you want to mark this transaction as completed?')) {
                return;
            }

            $.ajax({
                url: '/transactions/complete/' + transactionId,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert(response.success);
                    window.location.reload();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.error : 'Error completing transaction.';
                    alert(msg);
                }
            });
        });
    });
</script>
@endsection
