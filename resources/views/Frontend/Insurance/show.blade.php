@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>View Insurance MIS</h2>

<div class="bank-card">
    <div class="card-top-border">Insurance Details</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Application No.</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->application_no }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Channel Name</label>
            <input class="bank-detail-input form-control" value="{{ $related['channel_name'] ?? '-' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Parent Name</label>
            <input class="bank-detail-input form-control" value="{{ $related['parent_name'] ?? '-' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Location</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->location }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Disbursement Date</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->disbursement_date ? \Carbon\Carbon::parse($insuranceMis->disbursement_date)->format('Y-m-d') : '' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer Name</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->customer_name }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Loan Amount</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->loan_amt }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Insurance Rate</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->insurance_rate }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Insurance Amount</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->insurance_amt }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Sharing Insurance Commission (%)</label>
            <input class="bank-detail-input form-control" value="{{ $insuranceMis->sharing_insurance_commission ?? 50 }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Insurance Workflow Status</label>
            <input class="bank-detail-input form-control" value="{{ ucwords(str_replace('-', ' ', $insuranceMis->status ?? 'pending')) }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Manual Payment Status</label>
            <input class="bank-detail-input form-control" value="{{ strtolower((string)($insuranceMis->payment_status ?? 'pending')) === 'completed' ? 'Payout Completed' : 'Payout Pending' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Insurance Payout Status</label>
            <input class="bank-detail-input form-control" value="{{ $related['status_text'] ?? 'Insurance Pending' }}" disabled />
        </div>
    </div>
</div>
@endsection

