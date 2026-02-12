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
        <h3 class="settlement-heading">Edit Distribution - Sharing Commission</h3>
        <div class="settlement-btn-container">
            <a href="{{ url('/settlement?p=' . $settlement->user_id) }}" style="text-decoration: none;">
                <button class="settlement-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to List
                </button>
            </a>
        </div>
    </div>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Distribution Details (Read-Only) --}}
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
                @else
                N/A
                @endif
            </div>
        </div>
        <div class="detail-item">
            <label>Customer Name</label>
            <div class="detail-value">{{ $app->customer_name ?? '-' }}</div>
        </div>
        <div class="detail-item">
            <label>Disbursement Amount</label>
            <div class="detail-value">₹ {{ number_format($app->disburse_amount ?? 0, 2) }}</div>
        </div>
        <div class="detail-item">
            <label>Bank Payout Amount</label>
            <div class="detail-value">₹ {{ number_format($payoutAmount ?? 0, 2) }}</div>
        </div>
        <div class="detail-item">
            <label>Submitted By</label>
            <div class="detail-value">
                {{ $submitter ? $submitter->first_name . ' ' . ($submitter->last_name ?? '') : '-' }}
            </div>
        </div>
        <div class="detail-item">
            <label>Company Receiving</label>
            <div class="detail-value">{{ $app && $app->commission_rate ? $app->commission_rate . '%' : '-' }}</div>
        </div>
    </div>
</div>
<br>

{{-- Edit Sharing Commission --}}
<form action="{{ url('/settlement/distribution/update/' . $distribution->id) }}" method="POST" class="needs-validation" novalidate>
    @csrf

    <div class="bank-card">
        <div class="card-top-border">Update Sharing Commission</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Current Sharing Commission</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $distribution->received_rate ?? 0 }}%" disabled />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">New Sharing Commission (%)<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" step="0.01" min="0" max="100"
                       name="received_rate" id="received_rate"
                       value="{{ old('received_rate', $distribution->received_rate ?? 0) }}"
                       required />
            </div>
        </div>
    </div>
    <br>

    {{-- Live Calculation Preview --}}
    <div class="bank-card">
        <div class="card-top-border">Calculation Preview</div>
        <div style="padding: 15px; max-width: 450px;">
            <div class="calc-row">
                <span class="calc-label">Bank Payout Amount</span>
                <span class="calc-value" id="preview-payout">₹ {{ number_format($payoutAmount ?? 0, 2) }}</span>
            </div>
            <div class="calc-row">
                <span class="calc-label">Sharing Commission</span>
                <span class="calc-value" id="preview-rate">{{ $distribution->received_rate ?? 0 }}%</span>
            </div>
            <div class="calc-row">
                <span class="calc-label">Commission Amount</span>
                <span class="calc-value" id="preview-commission">₹ {{ number_format($distribution->gross_amount ?? 0, 2) }}</span>
            </div>
            <div class="calc-row">
                <span class="calc-label">TDS ({{ $tdsPercentage }}%)</span>
                <span class="calc-value" id="preview-tds">₹ {{ number_format($distribution->tds ?? 0, 2) }}</span>
            </div>
            <div class="calc-row total">
                <span class="calc-label">Net Payable</span>
                <span class="calc-value" id="preview-net">₹ {{ number_format($distribution->amount ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
    <br>

    <div class="save-btn-container">
        <a href="{{ url('/settlement?p=' . $settlement->user_id) }}" class="btn btn-secondary me-2" style="padding: 8px 24px;">Cancel</a>
        <button class="save-btn" type="submit" id="submitBtn">Update Distribution</button>
    </div>
</form>

@endsection

@section('script')
<script>
$(document).ready(function() {
    var payoutAmount = parseFloat('{{ $payoutAmount ?? 0 }}');
    var tdsPercentage = parseFloat('{{ $tdsPercentage }}');

    function recalculate() {
        var rate = parseFloat($('#received_rate').val()) || 0;
        var commission = (payoutAmount * rate) / 100;
        var tds = (commission * tdsPercentage) / 100;
        var net = commission - tds;

        $('#preview-rate').text(rate.toFixed(2) + '%');
        $('#preview-commission').text('₹ ' + commission.toFixed(2));
        $('#preview-tds').text('₹ ' + tds.toFixed(2));
        $('#preview-net').text('₹ ' + net.toFixed(2));
    }

    $('#received_rate').on('input change', function() {
        recalculate();
    });

    // Initial calculation
    recalculate();
});
</script>
@endsection
