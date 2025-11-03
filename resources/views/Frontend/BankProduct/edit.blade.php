@extends('Layout.app')
@section('style')
    <link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/import.css')}}">

@endsection
@section('body')
    <h2>Edit Bank Product</h2>
    <form action="{{url('/bank/update/product/' . $bank->id)}}" method="POST">
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
                        @foreach($banks as $b)
                            <option value="{{$b->id}}" @if($b->id == $bank->bank_id) selected @endif >{{$b->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Product Name<span class="required">*</span></label>
                    <!-- <input class="bank-detail-input form-control" type="text" name="disbAmount" id="disbAmount" placeholder="Enter Disburse Amount" required> -->
                    <select class="bank-detail-input form-select row_select" required name="product_id" id="product_id">
                        <option value="" disabled>Select</option>
                        @foreach($products as $b)
                            <option value="{{$b->id}}" @if($b->id == $bank->product_id) selected @endif>{{$b->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Auto Generate LAN<span class="required">*</span></label>
                    <!-- <input class="bank-detail-input form-control" type="text" name="disbAmount" id="disbAmount" placeholder="Enter Disburse Amount" required> -->
                    <select class="bank-detail-input form-select row_select" required name="auto_generate_lan" id="auto_generate_lan">
                        <option value="" selected disabled>Select Type</option>
                        <option value="1">True</option>
                        <option value="0">False</option>
                    </select>
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
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#uploadBtn').click(function () {
                $('#xlsx_file').click();
            });

            $('#xlsx_file').change(function () {
                var fileInput = $(this)[0].files[0];
                var formData = new FormData();
                formData.append('file', fileInput);

                $.ajax({
                    url: '/getFileData',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        var select = $('.row_select');
                        select.empty().append($('<option>', {
                            value: '',
                            text: 'Select',
                            disabled: true,
                            selected: true
                        }));

                        $.each(response, function (key, value) {
                            select.append($('<option>', {
                                value: value,
                                text: value
                            }));
                        });
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });
            });

            $('#bank_id,#group').change(function () {
                if ($('#bank_id').val() && $('#group').val()) {
                    performAjaxRequest('/getProduct', 'POST', {
                        bank_id: $('#bank_id').val(),
                        group: $('#group').val(),
                    }, function (response) {
                        var select = $('#product_id')
                        select.empty().append($('<option>', {
                            value: '',
                            text: 'Select Product',
                            disabled: true,
                            selected: true
                        }));

                        $.each(response, function (key, value) {
                            select.append($('<option>', {
                                value: value.id,
                                text: value.name
                            }));
                        });
                    });
                }
            });

            $('#submitBtn').click(function (event) {
                // Prevent default form submission
                event.preventDefault();

                // Perform form validation
                var isValid = true;


                if (!$('#app_id').val()) {
                    $('#app_id').removeClass('is-valid').addClass('is-invalid');
                    $('#app_id').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#app_id').addClass('is-valid').removeClass('is-invalid');
                }


                if (!$('#disbursement_date').val()) {
                    $('#disbursement_date').removeClass('is-valid').addClass('is-invalid');
                    $('#disbursement_date').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#disbursement_date').addClass('is-valid').removeClass('is-invalid');
                }

                if (!$('#customer_name').val()) {
                    $('#customer_name').removeClass('is-valid').addClass('is-invalid');
                    $('#customer_name').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#customer_name').addClass('is-valid').removeClass('is-invalid');
                }

                if (!$('#case_state').val()) {
                    $('#case_state').removeClass('is-valid').addClass('is-invalid');
                    $('#case_state').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#case_state').addClass('is-valid').removeClass('is-invalid');
                }

                if (!$('#case_location').val()) {
                    $('#case_location').removeClass('is-valid').addClass('is-invalid');
                    $('#case_location').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#case_location').addClass('is-valid').removeClass('is-invalid');
                }


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


                if (!$('#product_id').val()) {
                    $('#product_id').removeClass('is-valid').addClass('is-invalid');
                    $('#product_id').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#product_id').addClass('is-valid').removeClass('is-invalid');
                }

                if (!$('#group').val()) {
                    $('#group').removeClass('is-valid').addClass('is-invalid');
                    $('#group').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#group').addClass('is-valid').removeClass('is-invalid');
                }

                if ($('#group').val() == 'Secured') {
                    if (!$('#fresh_bt').val()) {
                        $('#fresh_bt').removeClass('is-valid').addClass('is-invalid');
                        $('#fresh_bt').focus();
                        isValid = false;
                        return false;

                    } else {
                        $('#fresh_bt').addClass('is-valid').removeClass('is-invalid');
                    }


                    if (!$('#any_subvention').val()) {
                        $('#any_subvention').removeClass('is-valid').addClass('is-invalid');
                        $('#any_subvention').focus();
                        isValid = false;
                        return false;

                    } else {
                        $('#any_subvention').addClass('is-valid').removeClass('is-invalid');
                    }
                } else {
                    if (!$('#otc_pdd').val()) {
                        $('#otc_pdd').removeClass('is-valid').addClass('is-invalid');
                        $('#otc_pdd').focus();
                        isValid = false;
                        return false;

                    } else {
                        $('#otc_pdd').addClass('is-valid').removeClass('is-invalid');
                    }



                    if (!$('#pf_taken').val()) {
                        $('#pf_taken').removeClass('is-valid').addClass('is-invalid');
                        $('#pf_taken').focus();
                        isValid = false;
                        return false;

                    } else {
                        $('#pf_taken').addClass('is-valid').removeClass('is-invalid');
                    }
                }



                if (!$('#disburse_amount').val()) {
                    $('#disburse_amount').removeClass('is-valid').addClass('is-invalid');
                    $('#disburse_amount').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#disburse_amount').addClass('is-valid').removeClass('is-invalid');
                }
                if (!$('#banker_name').val()) {
                    $('#banker_name').removeClass('is-valid').addClass('is-invalid');
                    $('#banker_name').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#banker_name').addClass('is-valid').removeClass('is-invalid');
                }

                if (!$('#banker_number').val()) {
                    $('#banker_number').removeClass('is-valid').addClass('is-invalid');
                    $('#banker_number').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#banker_number').addClass('is-valid').removeClass('is-invalid');
                }


                if (!$('#banker_email').val()) {
                    $('#banker_email').removeClass('is-valid').addClass('is-invalid');
                    $('#banker_email').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#banker_email').addClass('is-valid').removeClass('is-invalid');
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
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    </script>
@endsection