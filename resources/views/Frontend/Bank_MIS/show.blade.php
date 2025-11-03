@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection
@section('body')
<h2>View Bank MIS</h2>

<div class="bank-card">
    <div class="card-top-border">Basic Details</div>
    <div class="card-form">
        @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)

        <div class="bank-detail-inputs channel">
            <label class="bank-input-label" for="validationCustom01">Bank Name<span class="required">*</span> </label>
            <select class="bank-detail-input form-select" required name="bank_name" id="bank_name" disabled>
                <option value="" selected disabled>Select Bank Name</option>
                @foreach($bank as $bank)
                <option value="{{$bank->id}}" selected>{{$bank->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="bank-detail-inputs channel">
            <label class="bank-input-label" for="validationCustom01">Product Name<span class="required">*</span> </label>
            <select class="bank-detail-input form-select" required name="product_name" id="product_name" disabled>
                <option value="" selected disabled>Select Product Name</option>
                @foreach($product as $product)
                <option value="{{$product->id}}" selected>{{$product->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="bank-detail-inputs channel">
            <label class="bank-input-label" for="validationCustom01">Group<span class="required">*</span> </label>
            <select class="bank-detail-input form-select" required name="group" id="group" disabled>
                <option value="" selected disabled>Select Group</option>

                <option value="{{$bank_mis->group}}" selected>{{$bank_mis->group}}</option>

            </select>
        </div>
        @endif
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer Name<span class="required ">* </span> </label>
            <input class="bank-detail-input form-control" type="text" name="customer_name" id="customer_name" placeholder="Enter Customer Name" value="{{$bank_mis->customer_name}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer Firm Name<span class="required ">* </span> </label>
            <input class="bank-detail-input form-control" type="text" name="customer_firm_name" id="customer_firm_name" placeholder="Enter Customer Firm Name" value="{{$bank_mis->customer_firm_name}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Location<span class="required ">* </span> </label>
            <input class="bank-detail-input form-control" type="text" name="location" id="location" placeholder="Enter Location" value="{{$bank_mis->location}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Case Location<span class="required ">* </span> </label>
            <input class="bank-detail-input form-control" type="text" name="case_location" id="case_location" placeholder="Enter Case Location" value="{{$bank_mis->case_location}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Disburse Amount<span class="required ">*</span></label>
            <input class="bank-detail-input form-control" type="number" name="disburse_amount" id="disburse_amount" placeholder="Enter Disburse Amount" value="{{$bank_mis->disburse_amount}}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Payout Amount<span class="required ">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="payout_amount" id="payout_amount" placeholder="Enter Payout Amount" value="{{$bank_mis->payout_amount}}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Payout Rate<span class="required ">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="payout_rate" id="payout_rate" placeholder="Enter Payout Rate" value="{{$bank_mis->payout_rate}}" disabled>
        </div>
        <div class="bank-detail-inputs unsecured">
            <label class="bank-input-label">PF Taken<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="pf_taken" id="pf_taken" placeholder="Enter PF Taken" value="{{$bank_mis->pf_taken}}" disabled>

        </div>
        <div class="bank-detail-inputs secured">
            <label class="bank-input-label">Any Subvention<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="any_subvention" id="any_subvention" placeholder="Enter Any Subvention" value="{{$bank_mis->any_subvention}}" disabled>
        </div>
        <div class="bank-detail-inputs secured">
            <label class="bank-input-label">ROI<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="roi" id="roi" placeholder="Enter ROI" value="{{$bank_mis->roi}}" disabled>
        </div>
        <div class="bank-detail-inputs secured">
            <label class="bank-input-label">Insurance<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance" value="{{$bank_mis->insurance}}" disabled>
        </div>

        <div class="bank-detail-inputs unsecured">
            <label class="bank-input-label">OTC/PDD Status<span class="required">*</span></label>
            <select class="bank-detail-input form-select" required name="otc_pdd" id="otc_pdd" disabled>
                <option value="" disabled selected>Select Option</option>
                <option value="Pending" @if($bank_mis->otc_or_pdd_status =='Pending') selected @endif>Pending</option>
                <option value="Clear" @if($bank_mis->otc_or_pdd_status =='Clear') selected @endif>Clear</option>
            </select>
        </div>







    </div>
</div>
<br>


@endsection

@section('script')
<script>

</script>
@endsection