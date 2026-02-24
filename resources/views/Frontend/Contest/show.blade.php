@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>View Contest MIS</h2>

<div class="bank-card">
    <div class="card-top-border">Contest Details</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Application No.</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->application_no }}" disabled />
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
            <input class="bank-detail-input form-control" value="{{ $contestMis->location }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Disbursement Date</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->disbursement_date ? \Carbon\Carbon::parse($contestMis->disbursement_date)->format('Y-m-d') : '' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer Name</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->customer_name }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Loan Amount</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->loan_amt }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Contest Rate</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->contest_rate }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Contest Amount</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->contest_amt }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Sharing Contest Commission (%)</label>
            <input class="bank-detail-input form-control" value="{{ $contestMis->sharing_contest_commission ?? 50 }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Contest Workflow Status</label>
            <input class="bank-detail-input form-control" value="{{ ucwords(str_replace('-', ' ', $contestMis->status ?? 'pending')) }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Manual Payment Status</label>
            <input class="bank-detail-input form-control" value="{{ strtolower((string)($contestMis->payment_status ?? 'pending')) === 'completed' ? 'Payout Completed' : 'Payout Pending' }}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Commission Payout Status</label>
            <input class="bank-detail-input form-control" value="{{ $related['status_text'] ?? 'Commission Pending' }}" disabled />
        </div>
    </div>
</div>
@endsection
