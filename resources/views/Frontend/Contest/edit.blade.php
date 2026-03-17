@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>Edit Contest MIS</h2>

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('contest.update', $contestMis->id) }}" class="needs-validation">
    @csrf
    @method('PUT')
    <div class="bank-card">
        <div class="card-top-border">Contest Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application No.<span class="required">*</span></label>
                <input class="bank-detail-input form-control" name="application_no" value="{{ old('application_no', $contestMis->application_no) }}" required />
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
                <input class="bank-detail-input form-control" name="location" value="{{ old('location', $contestMis->location) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disbursement Date</label>
                <input type="date" class="bank-detail-input form-control" name="disbursement_date" value="{{ old('disbursement_date', $contestMis->disbursement_date ? \Carbon\Carbon::parse($contestMis->disbursement_date)->format('Y-m-d') : '') }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer Name</label>
                <input class="bank-detail-input form-control" name="customer_name" value="{{ old('customer_name', $contestMis->customer_name) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Loan Amount</label>
                <input type="number" step="0.01" class="bank-detail-input form-control" name="loan_amt" value="{{ old('loan_amt', $contestMis->loan_amt) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Contest Rate</label>
                <input type="number" step="0.0001" class="bank-detail-input form-control" name="contest_rate" value="{{ old('contest_rate', $contestMis->contest_rate) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Contest Amount</label>
                <input type="number" step="0.01" class="bank-detail-input form-control" name="contest_amt" value="{{ old('contest_amt', $contestMis->contest_amt) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Sharing Contest Commission (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="bank-detail-input form-control" name="sharing_contest_commission" value="{{ old('sharing_contest_commission', $contestMis->sharing_contest_commission ?? 50) }}" />
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ old('status', $contestMis->status ?? 'pending') }}">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Payment Status</label>
                <select class="bank-detail-input form-select" name="payment_status">
                    <option value="pending" @if(old('payment_status', $contestMis->payment_status ?? 'pending') === 'pending') selected @endif>Payout Pending</option>
                    <option value="completed" @if(old('payment_status', $contestMis->payment_status ?? 'pending') === 'completed') selected @endif>Payout Completed</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Contest Workflow Status</label>
                <input class="bank-detail-input form-control" value="{{ ucwords(str_replace('-', ' ', $contestMis->status ?? 'pending')) }}" disabled />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Commission Payout Status</label>
                <input class="bank-detail-input form-control" value="{{ $related['status_text'] ?? 'Commission Pending' }}" disabled />
            </div>
        </div>
    </div>

    <div class="save-btn-container">
        @php($currentRole = Auth::user()->roles[0]->id ?? 0)
        @php($isCommissionCompleted = in_array(strtolower(trim((string)($related['status_text'] ?? ''))), ['commission paid', 'completed']))

        @if($currentRole == 35)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $contestMis->status ?? 'pending' }}')">Update</button>
        @if($isCommissionCompleted)
        <button type="button" class="btn btn-primary" onclick="setStatusAndSubmit('approved')">Approve</button>
        @endif
        @elseif($currentRole == 36)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $contestMis->status ?? 'approved' }}')">Update</button>
        <button type="button" class="btn btn-success" onclick="setStatusAndSubmit('completed')">Complete</button>
        <button type="button" class="btn btn-danger" onclick="setStatusAndSubmit('rejected')">Reject</button>
        @elseif($currentRole == 1)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $contestMis->status ?? 'pending' }}')">Update</button>
        @if(($contestMis->status ?? 'pending') === 'pending' && $isCommissionCompleted)
        <button type="button" class="btn btn-primary" onclick="setStatusAndSubmit('approved')">Approve</button>
        @endif
        @if(($contestMis->status ?? 'pending') === 'approved')
        <button type="button" class="btn btn-success" onclick="setStatusAndSubmit('completed')">Complete</button>
        <button type="button" class="btn btn-danger" onclick="setStatusAndSubmit('rejected')">Reject</button>
        @endif
        @endif
        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ url('/contest') }}'">Cancel</button>
    </div>
</form>
<script>
    function setStatusAndSubmit(status) {
        document.getElementById('statusInput').value = status;
        document.querySelector('form.needs-validation').submit();
    }
</script>
@endsection
