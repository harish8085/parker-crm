@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<style>
    .summary-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 25px;
        margin-bottom: 20px;
    }
    .summary-items {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 15px;
    }
    .summary-item {
        flex: 1;
        min-width: 160px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
    }
    .summary-item .label {
        display: block;
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    .summary-item .value {
        font-size: 18px;
        font-weight: 700;
    }
    .txn-table th {
        background: #f8f9fa;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .txn-table td {
        font-size: 14px;
        vertical-align: middle;
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-badge.completed {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.approved {
        background: #cce5ff;
        color: #004085;
    }
    .status-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
</style>
@endsection
@section('body')

<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">Settlement Summary</h3>
        <div class="settlement-btn-container">
            <a href="{{ url('/settlement') }}" style="text-decoration: none;">
                <button class="settlement-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
            </a>
        </div>
    </div>

    <div class="p-4">
        <!-- Channel Summary -->
        <div class="summary-card">
            <h5 class="mb-0"><strong>{{ $channelUser->first_name }} {{ $channelUser->last_name }}</strong></h5>
            <div class="summary-items">
                <div class="summary-item">
                    <span class="label">Commission Amount</span>
                    <span class="value">₹ {{ indianNumberFormat($totalCommission) }}</span>
                </div>
                <div class="summary-item">
                    <span class="label">TDS ({{ $tdsPercentage }}%)</span>
                    <span class="value">₹ {{ indianNumberFormat($totalTds) }}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Net Payable</span>
                    <span class="value text-success">₹ {{ indianNumberFormat($totalNetPayable) }}</span>
                </div>
                <div class="summary-item">
                    <span class="label">Total Transactions</span>
                    <span class="value">{{ $transactions->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="summary-card">
            <h6 class="mb-3"><strong>Transactions</strong></h6>
            @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered txn-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Transaction ID</th>
                            <th>Commission Amount</th>
                            <th>TDS ({{ $tdsPercentage }}%)</th>
                            <th>Advance Deduction</th>
                            <th>Net Payable</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $index => $txn)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>#{{ $txn->id }}</td>
                            <td>₹ {{ indianNumberFormat($txn->gross_amount) }}</td>
                            <td>₹ {{ indianNumberFormat($txn->tds_amount) }}</td>
                            <td>
                                @if($txn->advance_amount > 0)
                                    <span class="text-danger">₹ {{ indianNumberFormat($txn->advance_amount) }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="fw-bold text-success">₹ {{ indianNumberFormat($txn->net_payable) }}</td>
                            <td><span class="status-badge {{ $txn->status }}">{{ ucwords($txn->status) }}</span></td>
                            <td>{{ $txn->created_at ? $txn->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <a href="{{ url('/transactions/view/' . $txn->id) }}" class="btn btn-sm btn-outline-primary" title="View Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle"></i> No transactions found for this settlement.
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
