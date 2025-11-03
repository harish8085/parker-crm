@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">

@endsection
@section('body')
<h2>Edit Bank Target</h2>
<form action="{{url('/bank-target/update/'.$data->id)}}" method="POST">
    @method('PUT')
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
                <label class="bank-input-label">Bank Name<span class="required">*</span></label>
                <!-- <input class="bank-detail-input form-control" type="text" name="disbAmount" id="disbAmount" placeholder="Enter Disburse Amount" required> -->
                <select class="bank-detail-input form-select row_select" required name="bank_id" id="bank_id">
                    <option value="" disabled>Select</option>
                    <option value="{{$data->bank_id}}" selected>{{$data->bank->name}}</option>
                    @foreach($banks as $b)
                    <option value="{{$b->id}}">{{$b->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Target Amount<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="targetAmount" id="targetAmount" value="{{$data->target_amount}}" placeholder="Enter Target Amount" required>
            </div>


        </div>
    </div>
    <br>
    <center>
        <div class="col-sm-12">
            <button type="submit" class="btn btn-success mt-2">Save</button>
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

        $('#submitBtn').click(function(event) {
            // Prevent default form submission
            event.preventDefault();

            // Perform form validation
            var isValid = true;

            if (!$('#bank_id').val()) {
                $('#bank_id').removeClass('is-valid').addClass('is-invalid');
                $('#bank_id').focus();
                $('.invalid-feedback').show()
                isValid = false;
                return false;
            } else {
                $('#bank_id').addClass('is-valid').removeClass('is-invalid');
                $('.invalid-feedback').hide()

            }


            if (!$('#targetAmount').val()) {
                $('#targetAmount').removeClass('is-valid').addClass('is-invalid');
                $('#targetAmount').focus();
                isValid = false;
                return false;

            } else {
                $('#targetAmount').addClass('is-valid').removeClass('is-invalid');
            }


            if (!$('#banker_number').val()) {
                $('#banker_number').removeClass('is-valid').addClass('is-invalid');
                $('#banker_number').focus();
                isValid = false;
                return false;
            } else {
                $('#banker_number').addClass('is-valid').removeClass('is-invalid');
            }
            // If form is valid, submit the form
            if (isValid) {
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