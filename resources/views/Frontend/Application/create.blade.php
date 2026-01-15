@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
@endsection
@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/application') }}">Applications</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Application</li>
        </ol>
    </nav>
</div>



<form class="needs-validation" action="{{url('/application/create')}}" method="POST" novalidate>
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
        <div class="card-top-border">Basic Details</div>
        <div class="card-form">
            @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select User Type</label>
                <select class="bank-detail-input form-select select" required name="user_type" id="user_type">
                    <option value="" selected disabled>Select User Type</option>
                    <option value="channel">Channel Partner</option>
                    <option value="sales">Sales Person</option>
                </select>
            </div>
            <div class="bank-detail-inputs channel">
                <label class="bank-input-label" for="validationCustom01">Channel Partner</label>
                <select class="bank-detail-input form-select select" required name="channel_sales_id" id="channel_id">
                    <option value="" selected disabled>Select Channel Partner</option>
                    @foreach($channels as $channel)
                    <option value="{{$channel->id}}">{{$channel->first_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs sales">
                <label class="bank-input-label" for="validationCustom01">Sales Person<span class="required">*</span> </label>
                <select class="bank-detail-input form-select select" required name="channel_sales_id" id="sales_id">
                    <option value="" selected disabled>Select Sales Person</option>
                    @foreach($sales as $sale)
                    <option value="{{$sale->id}}">{{$sale->first_name}} {{$sale->last_name}}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application Number/LAN No.<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" maxlength="50" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disbursment Date<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="date" name="disbursement_date" id="disbursement_date" placeholder="Enter disbursment date" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer Name<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="customer_name" id="customer_name" placeholder="Enter customer name" maxlength="80">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer's Firm Name</label>
                <input class="bank-detail-input form-control" type="text" name="firm_name" id="firm_name" placeholder="Enter customer firm name" maxlength="150">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case State</label>
                <select class="bank-detail-input form-select select" required name="case_state" id="case_state">
                    <option value="" selected disabled>Select States</option>
                    @foreach($states as $state)
                    <option value="{{$state['state_code']}}">{{$state['state']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case Loaction</label>
                <select class="bank-detail-input form-select select" required name="case_location" id="case_location">
                    <option value="" selected disabled>Select District</option>
                </select>
            </div>
            <!-- <div class="bank-detail-inputs">
                <label class="bank-input-label">Case Loaction<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="case_location" id="case_location" placeholder="Enter case location">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case State<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="case_state" id="case_state" placeholder="Enter case state">
            </div> -->
            


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="bank_id" id="bank_id">
                    <option value="" selected disabled>Select Bank</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}">{{$bank->name}}</option>
                    @endforeach
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Group<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="group" id="group">
                    <option value="Secured" selected>Secured</option>
                    <option value="Unsecured">Unsecured</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Product<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="product_id" id="product_id">
                    <option value="" selected disabled>Select Product</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Fresh/BT<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="fresh_bt" id="fresh_bt">
                    <option value="" disabled selected>Select Option</option>
                    <option value="Fresh" selected>Fresh</option>
                    <option value="BT">Balance Transfer</option>
                    <option value="Tranche">Tranche</option>
                    <option value="TP">Top Up</option>
                </select>
            </div>
            <div class="bank-detail-inputs secured">
                <label class="bank-input-label">OTC/PDD Status<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="otc_pdd" id="otc_pdd">
                    <option value="" disabled selected>Select Option</option>
                    <option value="Pending">Pending</option>
                    <option value="Clear">Clear</option>
                </select>
            </div>

            <div class="bank-detail-inputs ">
                <label class="bank-input-label">Any Subvention<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" min=0 name="any_subvention" id="any_subvention" placeholder="Enter Any Subvention">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">PF Taken</label>
                <input class="bank-detail-input form-control" type="number" name="pf_taken" id="pf_taken" placeholder="Enter PF Taken">

            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disburse Amount<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" name="disburse_amount" id="disburse_amount" placeholder="Enter Disburse Amount">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Commission Rate</label>
                <input class="bank-detail-input form-control" type="number" name="commission_rate" id="commission_rate" placeholder="Enter Commission Rate">
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Name
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 || Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                <input class="bank-detail-input form-control" type="text" name="banker_name" id="banker_name" placeholder="Enter Banker Name">
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker number
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 || Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                </label>
                <input class="bank-detail-input form-control" type="number" maxlength="10" name="banker_number" id="banker_number" placeholder="Enter Banker Number">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker email
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 || Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                </label>
                <input class="bank-detail-input form-control" type="email" name="banker_email" id="banker_email" placeholder="Enter Banker Email">
            </div>


            @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Status<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="status" id="status">
                    <option value="pending" selected>Pending</option>
                    <option value="rejected">Rejected</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            @endif
        </div>
    </div>
    <br>
    <div class="save-btn-container">
        <button class="save-btn" id="submitBtn">Save</button>
    </div>
</form>

@endsection

@section('script')

<!-- select search -->
<script>
    $(document).ready(function() {
        // Initialize Select2 for all select elements
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('.unsecured').hide()
        $('.channel').hide()
        $('.sales').hide()
        $('#group').change(function() {
            if ($(this).val() == 'Secured') {
                $('.unsecured').hide()
                $('.secured').show()

            } else {
                $('.unsecured').show()
                $('.secured').hide()

            }
        })

        $('#case_state').change(function() {
            var stateId = $(this).val();
            $.ajax({
                url: '/getDistrict/' + stateId,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    $('#case_location').html('')
                    $('#case_location').append('<option value="" selected disabled>Select District</option>')
                    $('#case_location').val('')
                    $('#case_location').append(response)
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });

        });
        // });
        $('#user_type').change(function() {

            if ($(this).val() == 'channel') {
                $('.sales').hide()
                $('.channel').show()

            } else {
                $('.sales').show()
                $('.channel').hide()

            }
        })


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
            var roleId = `{{Auth::user()->roles[0]->pivot->role_id}}`;
            if (roleId == 1) {
                if (!$('#user_type').val()) {
                    $('#user_type').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                    $('#user_type').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#user_type').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                }

                if ($('#user_type').val() == 'channel') {
                    if (!$('#channel_id').val()) {
                        $('#channel_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                        $('#channel_id').focus();
                        isValid = false;
                        return false;
                    } else {
                        $('#channel_id').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }
                } else {
                    if (!$('#sales_id').val()) {
                        $('#sales_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                        $('#sales_id').focus();
                        isValid = false;
                        return false;
                    } else {
                        $('#sales_id').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                    }
                }
            }


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

           


            if (!$('#bank_id').val()) {
                $('#bank_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                $('#bank_id').focus();
                $('.invalid-feedback').show()
                isValid = false;
                return false;
            } else {
                $('#bank_id').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
                $('.invalid-feedback').hide()

            }


            if (!$('#product_id').val()) {
                $('#product_id').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                $('#product_id').focus();
                isValid = false;
                return false;

            } else {
                $('#product_id').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
            }

            if (!$('#group').val()) {
                $('#group').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                $('#group').focus();
                isValid = false;
                return false;

            } else {
                $('#group').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
            }

            if ($('#group').val() == 'Secured') {
                if (!$('#fresh_bt').val()) {
                    $('#fresh_bt').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                    $('#fresh_bt').focus();
                    isValid = false;
                    return false;

                } else {
                    $('#fresh_bt').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
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

            var roleId = `{{Auth::user()->roles[0]->pivot->role_id}}`;
            if (roleId == 2 || roleId == 3) {
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