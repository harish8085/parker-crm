@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">

@endsection
@section('body')
<h2>Add Bank Payout</h2>
<form class="needs-validation" action="{{ url('bank-payout/update',$payout->id) }}" method="POST">
    @csrf
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="bank-card">
        <div class="row m-0 card-top-border">
            <div class="col-sm-9">
                Basic Details
            </div>

        </div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="bank_id" id="bank_id">
                    <option value="" selected disabled>Select Bank</option>
                    @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}" @if($payout->bank_id ==$bank->id) selected @endif>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Group<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="group" id="group">
                    <option value="Secured" @if($payout->group =='Secured') selected @endif>Secured</option>
                    <option value="Unsecured" @if($payout->group =='Unsecured') selected @endif>Unsecured</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Product<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="product_id" id="product_id">
                    <option value="" selected disabled>Select Product</option>
                    @foreach ($products as $product)
                    <option value="{{ $product->id }}" @if($payout->product_id ==$product->id) selected @endif>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="bank-detail-inputs">

                <label class="bank-input-label">Payout Rate <small>(in %)</small><span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" min=0 name="payout_rate" id="payout_rate" value="{{$payout->rate}}" placeholder="Enter payout rate">
            </div>



        </div>
    </div>
    <br>
    <center>
        <div class="col-sm-12">
            <div class="loader-1">
                <div class="loader-div">
                    <div class="loader"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-success mt-2" id="submitBtn">Save</button>
        </div>
    </center>

</form>
@endsection
@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#bank_id,#group').change(function() {
            if ($('#bank_id').val() && $('#group').val()) {
                performAjaxRequest('/getProduct', 'POST', {
                    bank_id: $('#bank_id').val(),
                    group: $('#group').val(),
                }, function(response) {
                    var select = $('#product_id')
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select Product',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value.id,
                            text: value.name
                        }));
                    });
                });
            }
        });

        $('#submitBtn').click(function(event) {


            // Prevent default form submission
            event.preventDefault();

            // Perform form validation
            var isValid = true;


            if (!$('#bank_id').val()) {
                $('#bank_id').removeClass('is-valid').addClass('is-invalid');
                $('#bank_id').focus();
                isValid = false;
                return false;
            } else {
                $('#bank_id').addClass('is-valid').removeClass('is-invalid');
            }


            if (!$('#product_id').val()) {
                $('#product_id').removeClass('is-valid').addClass('is-invalid');
                $('#product_id').focus();
                isValid = false;
                return false;

            } else {
                $('#product_id').addClass('is-valid').removeClass('is-invalid');
            }

            if (!$('#payout_rate').val()) {
                $('#payout_rate').removeClass('is-valid').addClass('is-invalid');
                $('#payout_rate').focus();
                isValid = false;
                return false;
            } else {
                $('#payout_rate').addClass('is-valid').removeClass('is-invalid');
            }
            // If form is valid, submit the form
            if (isValid) {
                $('#submitBtn').hide()
                $('.loader-1').show()
                $('.needs-validation').submit();
            }
        });

        function performAjaxRequest(url, type, data, successCallback) {
            $.ajax({
                url: url,
                type: type,
                data: data,
                success: successCallback,
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
</script>
@endsection