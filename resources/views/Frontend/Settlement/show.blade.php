@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection
@section('body')
<h2>View Settlement</h2>

<div class="bank-card">
    <div class="card-top-border">Settlement Details</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Application Number/LAN No.<span class="required text-primary cursor-pointer" onclick="window.location.href='/application/view/{{$application->id}}'">View</span> </label>
            <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" value="{{$application->app_id}}" disabled />
        </div>

        @if($settlement->status =='completed')
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Settlement Date</label>
            <input class="bank-detail-input form-control" type="date" name="settlement_date" id="settlement_date" placeholder="Enter settlement date" value="{{$settlement->settlement_date}}" disabled />
        </div>
        @endif
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Settlement Rate</label>
            <input class="bank-detail-input form-control" type="number" name="received_rate" id="received_rate" placeholder="Enter settlement rate" value="{{$settlement->received_rate}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">TDS Deduction</label>
            <input class="bank-detail-input form-control" type="number" name="tds" id="tds" placeholder="Enter tds amount" value="{{$settlement->tds}}" disabled readonly />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Settlement Amount</label>
            <input class="bank-detail-input form-control" type="number" name="amount" id="amount" placeholder="Enter  amount" value="{{$settlement->amount}}" disabled />
        </div>
        @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3 )
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Gross Amount</label>
            <input class="bank-detail-input form-control" type="text" value="{{$settlement->gross_amount}}" id="totalAmount" disabled />
        </div>
        @endif
    </div>
</div>
<br>


@if($settlement->status !='checker')
<div class="bank-card">
    <div class="card-top-border">Bank Details</div>
    <div id="data">
        @foreach($settlement_distributions as $key=>$settlement_distribution)
        <div class="card-form" id="bankTemplate">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="bank[]" disabled>
                    <option value="" selected disabled>Select Bank Account</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}" @if($settlement_distribution->bank_account_id ==$bank->id) selected @endif>{{$bank->holder_name}}({{$bank->account_number}})</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Amount Recieve<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" name="recieve_amount[]" value="{{$settlement_distribution->amount}}" placeholder="Enter amount have to recieved" disabled />

            </div>
        </div>
        @endforeach

    </div>
</div>
@endif
@endsection