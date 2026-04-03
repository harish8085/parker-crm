@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 15px;
        padding: 15px;
    }
    .detail-item label {
        font-weight: 600;
        color: #555;
        font-size: 13px;
        margin-bottom: 4px;
        display: block;
    }
    .detail-item .detail-value {
        font-size: 15px;
        color: #222;
    }
    .calc-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 15px;
    }
    .calc-row:last-child {
        border-bottom: none;
    }
    .calc-row .calc-label {
        font-weight: 600;
        color: #555;
    }
    .calc-row .calc-value {
        font-weight: 600;
        color: #222;
    }
    .calc-row.total {
        border-top: 2px solid #333;
        margin-top: 5px;
        padding-top: 12px;
    }
    .calc-row.total .calc-label,
    .calc-row.total .calc-value {
        font-size: 16px;
        color: #000;
    }
</style>
@endsection

@section('body')
<div class="card">
    <div class="settlement-header">
        <h3 class="settlement-heading">View Distribution</h3>
        <div class="settlement-btn-container">
            <a href="{{ url('/settlement?p=' . $settlement->user_id . '&tab=' . ($tab ?? 'pending') . '&settlement_type=' . ($settlementType ?? ($settlement->settlement_type ?? 'commission')) . '&detail=1') }}" style="text-decoration: none;">
                <button class="settlement-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
            </a>
        </div>
    </div>
</div>

<div class="bank-card">
    <div class="card-top-border">Distribution Details</div>
    <div class="detail-grid">
        <div class="detail-item">
            <label>Channel Partner</label>
            <div class="detail-value">{{ $channelUser->first_name ?? '' }} {{ $channelUser->last_name ?? '' }}</div>
        </div>
        <div class="detail-item">
            <label>Application No.</label>
            <div class="detail-value">
                @if($app)
                <a href="{{ url('/application/view/' . $app->id) }}" class="text-primary">{{ $app->app_id }}</a>
                @elseif(($contest->application_no ?? null))
                {{ $contest->application_no }}
                @else
                N/A
                @endif
            </div>
        </div>
        <div class="detail-item">
            <label>Customer Name</label>
            <div class="detail-value">{{ $contest->customer_name ?? ($app->customer_name ?? '-') }}</div>
        </div>
        <div class="detail-item">
            <label>Disbursement Amount</label>
            <div class="detail-value">₹ {{ number_format(($settlementType ?? 'commission') === 'contest' ? ($contest->loan_amt ?? 0) : ($app->disburse_amount ?? 0), 2) }}</div>
        </div>
        <div class="detail-item">
            <label>{{ ($settlementType ?? 'commission') === 'contest' ? 'Contest Amount' : 'Bank Payout Amount' }}</label>
            <div class="detail-value">₹ {{ number_format($payoutAmount ?? 0, 2) }}</div>
        </div>
        <div class="detail-item">
            <label>Submitted By</label>
            <div class="detail-value">
                {{ $submitter ? $submitter->first_name . ' ' . ($submitter->last_name ?? '') : '-' }}
            </div>
        </div>
        <div class="detail-item">
            <label>{{ ($settlementType ?? 'commission') === 'contest' ? 'Contest Receiving' : 'Company Receiving' }}</label>
            <div class="detail-value">{{ $companyReceiving ?? '-' }}</div>
        </div>
        <div class="detail-item">
            <label>Sharing Commission</label>
            <div class="detail-value">{{ $distribution->received_rate ?? '-' }}%</div>
        </div>
    </div>
</div>
<br>

<div class="bank-card">
    <div class="card-top-border">Calculation</div>
    <div style="padding: 15px; max-width: 450px;">
        <div class="calc-row">
            <span class="calc-label">{{ ($settlementType ?? 'commission') === 'contest' ? 'Channel Contest Amount' : 'Commission Amount' }}</span>
            <span class="calc-value">₹ {{ number_format($distribution->gross_amount ?? 0, 2) }}</span>
        </div>
        <div class="calc-row">
            <span class="calc-label">TDS ({{ $tdsPercentage }}%)</span>
            <span class="calc-value">₹ {{ number_format($distribution->tds ?? 0, 2) }}</span>
        </div>
        <div class="calc-row total">
            <span class="calc-label">{{ ($settlementType ?? 'commission') === 'contest' ? 'Net Value' : 'Net Payable' }}</span>
            <span class="calc-value">₹ {{ number_format($distribution->amount ?? 0, 2) }}</span>
        </div>
    </div>
</div>
@endsection
