@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>Edit Insurance MIS</h2>

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('insurance.update', $insuranceMis->id) }}" class="needs-validation">
    @csrf
    @method('PUT')
    <div class="bank-card">
        <div class="card-top-border">Insurance Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application No.<span class="required">*</span></label>
                <input class="bank-detail-input form-control" name="application_no" value="{{ old('application_no', $insuranceMis->application_no) }}" required />
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
                <input class="bank-detail-input form-control" name="location" value="{{ old('location', $insuranceMis->location) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disbursement Date</label>
                <input type="date" class="bank-detail-input form-control" name="disbursement_date" value="{{ old('disbursement_date', $insuranceMis->disbursement_date ? \Carbon\Carbon::parse($insuranceMis->disbursement_date)->format('Y-m-d') : '') }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer Name</label>
                <input class="bank-detail-input form-control" name="customer_name" value="{{ old('customer_name', $insuranceMis->customer_name) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Loan Amount</label>
                <input type="number" step="0.01" class="bank-detail-input form-control" name="loan_amt" value="{{ old('loan_amt', $insuranceMis->loan_amt) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Insurance Rate</label>
                <input type="number" step="0.0001" class="bank-detail-input form-control" name="insurance_rate" value="{{ old('insurance_rate', $insuranceMis->insurance_rate) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Insurance Amount</label>
                <input type="number" step="0.01" class="bank-detail-input form-control" name="insurance_amt" value="{{ old('insurance_amt', $insuranceMis->insurance_amt) }}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Sharing Insurance Commission (%)</label>
                <input type="number" step="0.01" min="0" max="100" class="bank-detail-input form-control" name="sharing_insurance_commission" value="{{ old('sharing_insurance_commission', $insuranceMis->sharing_insurance_commission ?? 50) }}" />
            </div>
            <input type="hidden" name="status" id="statusInput" value="{{ old('status', $insuranceMis->status ?? 'pending') }}">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Payment Status</label>
                <select class="bank-detail-input form-select" name="payment_status">
                    <option value="pending" @if(old('payment_status', $insuranceMis->payment_status ?? 'pending') === 'pending') selected @endif>Payout Pending</option>
                    <option value="completed" @if(old('payment_status', $insuranceMis->payment_status ?? 'pending') === 'completed') selected @endif>Payout Completed</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Insurance Workflow Status</label>
                <input class="bank-detail-input form-control" value="{{ ucwords(str_replace('-', ' ', $insuranceMis->status ?? 'pending')) }}" disabled />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Insurance Payout Status</label>
                <input class="bank-detail-input form-control" value="{{ $related['status_text'] ?? 'Insurance Pending' }}" disabled />
            </div>
        </div>
    </div>

    <div class="save-btn-container">
        @php($currentRole = Auth::user()->roles[0]->id ?? 0)
        @php($isInsuranceCompleted = in_array(strtolower(trim((string)($related['status_text'] ?? ''))), ['insurance paid', 'completed']))

        @if($currentRole == 35)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $insuranceMis->status ?? 'pending' }}')">Update</button>
        @if($isInsuranceCompleted)
        <button type="button" class="btn btn-primary" onclick="setStatusAndSubmit('approved')">Approve</button>
        @endif
        @elseif($currentRole == 36)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $insuranceMis->status ?? 'approved' }}')">Update</button>
        <button type="button" class="btn btn-success" onclick="setStatusAndSubmit('completed')">Complete</button>
        <button type="button" class="btn btn-danger" onclick="setStatusAndSubmit('rejected')">Reject</button>
        @elseif($currentRole == 1)
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $insuranceMis->status ?? 'pending' }}')">Update</button>
        @if(($insuranceMis->status ?? 'pending') === 'pending' && $isInsuranceCompleted)
        <button type="button" class="btn btn-primary" onclick="setStatusAndSubmit('approved')">Approve</button>
        @endif
        @if(($insuranceMis->status ?? 'pending') === 'approved')
        <button type="button" class="btn btn-success" onclick="setStatusAndSubmit('completed')">Complete</button>
        <button type="button" class="btn btn-danger" onclick="setStatusAndSubmit('rejected')">Reject</button>
        @endif
        @endif
        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ url('/insurance') }}'">Cancel</button>
    </div>
</form>
<script>
    function setStatusAndSubmit(status) {
        document.getElementById('statusInput').value = status;
        document.querySelector('form.needs-validation').submit();
    }
</script>
@endsection

