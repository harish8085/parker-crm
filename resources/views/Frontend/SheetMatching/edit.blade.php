@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/import.css')}}">

@endsection
@section('body')
<h2>Edit Sheet Data</h2>
<form action="{{ route('sheet-matching.update',$data->id) }}" method="POST">
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
            <div class="col-sm-3 d-flex justify-content-end">
                <input class="input-file" type="file" accept=".xlsx" required name="xlsx_file" id="xlsx_file" style="display: none;" />
                <button type="button" class="sample-btn" id="uploadBtn">
                    <img class="sample-icon" src="{{asset('assets/images/import.svg')}}">Upload
                </button>
            </div>


        </div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="bank_id" id="bank_id">
                    <option value="" selected disabled>Select Bank</option>
                    @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}" @if($bank->id == $data->bank_id) selected @endif>{{ $bank->name }}</option>
                    @endforeach
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Group<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="group" id="group">
                    <option value="Secured" @if($data->group == 'Secured') selected @endif>Secured</option>
                    <option value="Unsecured" @if($data->group == 'Unsecured') selected @endif >Unsecured</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Product<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="product_id" id="product_id">
                    <option value="" selected disabled>Select Product</option>
                    @foreach ($products as $product)
                    <option value="{{ $product->id }}" @if($product->id == $data->product_id) selected @endif>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application ID<span class="required">*</span></label>
                <!-- <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter Application ID"> -->
                <select class="bank-detail-input form-select row_select" required name="app_id" id="app_id">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->app_id}}">{{$data->app_id}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case Location</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="case_location" id="case_location" placeholder="Enter Case Location"> -->
                <select class="bank-detail-input form-select row_select" name="case_location" id="case_location">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->case_location}}">{{$data->case_location}}</option>
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer Name</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="customer_name" id="customer_name" placeholder="Enter customer name" maxlength="80"> -->
                <select class="bank-detail-input form-select row_select" name="customer_name" id="customer_name">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->customer_name}}">{{$data->customer_name}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer's Firm Name</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="customer_firm_name" id="firm_name" placeholder="Enter customer firm name" maxlength="150"> -->
                <select class="bank-detail-input form-select row_select" name="customer_firm_name" id="customer_firm_name">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->customer_firm_name}}">{{$data->customer_firm_name}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disburse Amount<span class="required">*</span></label>
                <!-- <input class="bank-detail-input form-control" type="text" name="disbAmount" id="disbAmount" placeholder="Enter Disburse Amount" required> -->
                <select class="bank-detail-input form-select row_select" required name="disbAmount" id="disbAmount">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->disbAmount}}">{{$data->disbAmount}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Payout Amount</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="payout_amount" id="payout_amount" placeholder="Enter Payout Amount" required> -->
                <select class="bank-detail-input form-select row_select" name="payout_amount" id="payout_amount">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->payout_amount}}">{{$data->payout_amount}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Payout Rate</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="payout_rate" id="payout_rate" placeholder="Enter Payout Rate"> -->
                <select class="bank-detail-input form-select row_select" name="payout_rate" id="payout_rate">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->payout_rate}}">{{$data->payout_rate}}</option>
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">OTC/PDD Status<span class="required"></span></label>
                <!-- <input class="bank-detail-input form-control" type="text" name="otc_pdd_status" id="otc_pdd_status" placeholder="Enter OTC/PDD Status"> -->
                <select class="bank-detail-input form-select row_select" name="otc_pdd_status" id="otc_pdd_status">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->otc_pdd_status}}">{{$data->otc_pdd_status}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs ">
                <label class="bank-input-label">Any Subvention</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="subvention" id="subvention" placeholder="Enter Any Subvention"> -->
                <select class="bank-detail-input form-select row_select" name="subvention" id="subvention">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->subvention}}">{{$data->subvention}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">PF Taken</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="pf" id="pf" placeholder="Enter PF Taken"> -->
                <select class="bank-detail-input form-select row_select" name="pf" id="pf">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->pf}}">{{$data->pf}}</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">ROI</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="roi" id="roi" placeholder="Enter ROI"> -->
                <select class="bank-detail-input form-select row_select" name="roi" id="roi">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->roi}}">{{$data->roi}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Insurance</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="insurance" id="insurance">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->insurance}}">{{$data->insurance}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Date</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="date" id="date">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->date}}">{{$data->date}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Month</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="month" id="month">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->month}}">{{$data->month}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">PF%</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="pf_per" id="pf%">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->pf_per}}">{{$data->pf_per}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KLI</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kli" id="kli">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kli}}">{{$data->kli}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KLI Payout %</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kli_payout_per" id="kli_payout%">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kli_payout_per}}">{{$data->kli_payout_per}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KLI Payout</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kli_payout" id="kli_payout">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kli_payout}}">{{$data->kli_payout}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KGI</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kgi" id="kgi">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kgi}}">{{$data->kgi}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KGI Payout %</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kgi_payout_per" id="kgi_payout%">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kgi_payout_per}}">{{$data->kgi_payout_per}}</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">KGI Payout</label>
                <!-- <input class="bank-detail-input form-control" type="text" name="insurance" id="insurance" placeholder="Enter Insurance"> -->
                <select class="bank-detail-input form-select row_select" name="kgi_payout" id="kgi_payout">
                    <option value="" selected disabled>Select</option>
                    <option value="{{$data->kgi_payout}}">{{$data->kgi_payout}}</option>
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
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#uploadBtn').click(function() {
            $('#xlsx_file').click();
        });

        $('#xlsx_file').change(function() {
            var fileInput = $(this)[0].files[0];
            var formData = new FormData();
            formData.append('file', fileInput);

            $.ajax({
                url: '/getFileData',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    var select = $('.row_select');
                    select.empty().append($('<option>', {
                        value: '',
                        text: 'Select',
                        disabled: true,
                        selected: true
                    }));

                    $.each(response, function(key, value) {
                        select.append($('<option>', {
                            value: value,
                            text: value
                        }));
                    });
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
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
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }
    });
</script>
@endsection