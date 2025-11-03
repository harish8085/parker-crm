@php
$isChannel = DB::table('users')->where('id',$application->user_id)->value('user_type');
@endphp
@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection
@section('body')
<h2>Edit Application</h2>
<form class="needs-validation" action="{{url('/application/update/'.$application->id)}}" method="POST" novalidate>
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
                <label class="bank-input-label">Select User Type<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="user_type" id="user_type">
                    <option value="" selected disabled>Select User Type</option>
                    <option value="channel" @if($isChannel=='channel' ) selected @endif>Channel Partner</option>
                    <option value="sales" @if($isChannel!='channel' ) selected @endif>Sales Person</option>
                </select>
            </div>
            <div class="bank-detail-inputs channel">
                <label class="bank-input-label" for="validationCustom01">Channel Partner<span class="required">*</span> </label>
                <select class="bank-detail-input form-select" required name="channel_sales_id" id="channel_id">
                    <option value="" selected disabled>Select Channel Partner</option>
                    @foreach($channels as $channel)
                    <option value="{{$channel->id}}" @if($channel->id == $application->user_id) selected @endif>{{$channel->first_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs sales">
                <label class="bank-input-label" for="validationCustom01">Sales Person<span class="required">*</span> </label>
                <select class="bank-detail-input form-select" required name="channel_sales_id" id="sales_id">
                    <option value="" selected disabled>Select Sales Person</option>
                    @foreach($sales as $sale)
                    <option value="{{$sale->id}}" @if($sale->id == $application->user_id) selected @endif>{{$sale->first_name}} {{$sale->last_name}}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application Number/LAN No.
                    @if($application->bank_mis_id && $application->bankData)
                    <span class="required {{($application->app_id == $application->bankData->app_id?'text-success':'')}}">* ({{($application->bankData->app_id? $application->bankData->app_id:'')}})</span>
                    <i
                        class="fa fa-copy"
                        onclick="copyValue('{{@$application->bankData->app_id}}')"
                        title="Copy Application Number/LAN No"
                        style="cursor: pointer; font-size: 16px; color: gray;">
                    </i>
                    @endif

                </label>
                <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" value="{{$application->app_id}}" />
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disbursment Date<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="date" name="disbursement_date" id="disbursement_date" placeholder="Enter disbursment date" value="{{$application->disbursement_date}}" />
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer Name
                    @if($application->bank_mis_id&& $application->bankData)
                    <span class="required {{(strtolower($application->customer_name) == strtolower($application->bankData->customer_name)?'text-success':'')}}">* ({{($application->bankData->customer_name? $application->bankData->customer_name:'')}})</span>
                    <i
                        class="fa fa-copy"
                        onclick="copyValue('{{$application->bankData->customer_name}}')"
                        title="Copy Customer Name"
                        style="cursor: pointer; font-size: 16px; color: gray;">
                    </i>
                    @endif
                </label>
                <input class="bank-detail-input form-control" type="text" name="customer_name" id="customer_name" placeholder="Enter customer name" value="{{$application->customer_name}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Customer's Firm Name
                    @if($application->bank_mis_id&& $application->bankData)
                    <span class="required {{(strtolower($application->customer_firm_name) == strtolower($application->bankData->customer_firm_name)?'text-success':'')}}">* ({{($application->bankData->customer_firm_name? $application->bankData->customer_firm_name:'')}})</span>
                    <i
                        class="fa fa-copy"
                        onclick="copyValue('{{$application->bankData->customer_firm_name}}')"
                        title="Copy Customer Firm Name"
                        style="cursor: pointer; font-size: 16px; color: gray;">
                    </i>
                    @endif
                </label>
                <input class="bank-detail-input form-control" type="text" name="firm_name" id="firm_name" placeholder="Enter customer firm name" value="{{$application->firm_name}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case State
                </label>
                <select class="bank-detail-input form-select" name="case_state" id="case_state">
                    <option value="" selected disabled>Select States</option>
                    @foreach($states as $state)
                    <option value="{{$state['state_code']}}" @if($state['state_code']==$application->case_state) selected @endif>{{$state['state']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case Loaction
                    @if($application->bank_mis_id&& $application->bankData)
                    <span class="required {{(strtolower($application->case_location) == strtolower($application->bankData->case_location)?'text-success':'')}}">({{($application->bankData->case_location? $application->bankData->case_location:'')}})</span>
                    @endif
                </label>
                <select class="bank-detail-input form-select"  name="case_location" id="case_location">
                    <option value="" selected disabled>Select District</option>
                    @foreach($districts as $district)
                    <option value="{{$district}}" @if($district==$application->case_location) selected @endif>{{$district}}</option>
                    @endforeach
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Bank
                    @if($application->bank_mis_id && $application->bankData)
                    <span class="required {{(strtolower($application->bank_id) == strtolower($application->bankData->bank_id)?'text-success':'')}}">* ({{($application->bankData->bank_id? $application->bankData->bank->name:'')}})</span>
                    @endif
                </label>
                </label>
                <select class="bank-detail-input form-select" required name="bank_id" id="bank_id">
                    <option value="" selected disabled>Select Bank</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->id}}" @if($bank->id == $application->bank_id) selected @endif>{{$bank->name}}</option>
                    @endforeach
                </select>
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Product
                    @if($application->bank_mis_id && $application->bankData)
                    <span class="required {{(strtolower($application->product_id) == strtolower($application->bankData->product_id)?'text-success':'')}}">* ({{($application->bankData->product_id? $application->bankData->product->name:'')}})</span>
                    @endif
                </label>
                <select class="bank-detail-input form-select" required name="product_id" id="product_id">
                    <option value="" selected disabled>Select Product</option>
                    @foreach($bankProducts as $bankProduct)
                    <option value="{{$bankProduct->id}}" @if($bankProduct->id == $application->product_id) selected @endif>{{$bankProduct->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Group
                    @if($application->bank_mis_id && $application->bankData)
                    <span class="required {{(strtolower($application->group) == strtolower($application->bankData->group)?'text-success':'')}}">* ({{($application->bankData->group? $application->group:'')}})</span>
                    @endif
                </label>
                <select class="bank-detail-input form-select" required name="group" id="group">
                    <option value="Secured" @if($application->group =='Secured') selected @endif>Secured</option>
                    <option value="Unsecured" @if($application->group =='Unsecured') selected @endif>Unsecured</option>
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Fresh/BT</label>
                <select class="bank-detail-input form-select" required name="fresh_bt" id="fresh_bt">
                    <option value="" disabled selected>Select Option</option>
                    <option value="Fresh" @if($application->fresh_or_bt =='Fresh') selected @endif>Fresh</option>
                    <option value="BT" @if($application->fresh_or_bt =='BT') selected @endif>Balance Transfer</option>
                </select>
            </div>
            <div class="bank-detail-inputs secured">
                <label class="bank-input-label">OTC/PDD Status</label>
                <select class="bank-detail-input form-select" required name="otc_pdd" id="otc_pdd">
                    <option value="" disabled selected>Select Option</option>
                    <option value="Pending" @if($application->otc_or_pdd_status =='Pending') selected @endif>Pending</option>
                    <option value="Clear" @if($application->otc_or_pdd_status =='Clear') selected @endif>Clear</option>
                </select>
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Any Subvention</label>
                <input class="bank-detail-input form-control" type="number" name="any_subvention" id="any_subvention" placeholder="Enter Any Subvention" value="{{$application->any_subvention}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">PF Taken</label>
                <input class="bank-detail-input form-control" type="number" name="pf_taken" id="pf_taken" placeholder="Enter PF Taken" value="{{$application->pf_taken}}">

            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Disburse Amount
                    @if($application->bank_mis_id && $application->bankData)
                    <span class="required {{(strtolower($application->disburse_amount) == strtolower($application->bankData->disbAmount)?'text-success':'')}}">* ({{($application->bankData->disbAmount? $application->bankData->disbAmount:'')}})</span>
                    <i
                        class="fa fa-copy"
                        onclick="copyValue('{{$application->bankData->disbAmount}}')"
                        title="Copy Disburse Amount"
                        style="cursor: pointer; font-size: 16px; color: gray;">
                    </i>
                    @endif
                </label>
                <input class="bank-detail-input form-control" type="number" name="disburse_amount" id="disburse_amount" placeholder="Enter Disburse Amount" value="{{$application->disburse_amount}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Commission Rate
                    @if($application->bank_mis_id && $application->bankData) 
                    <span class="required {{(strtolower($application->commission_rate) == strtolower($application->bankData->payout_rate)?'text-success':'')}}">* ({{($application->bankData->payout_rate? $application->bankData->payout_rate:'')}})</span>
                    <i
                        class="fa fa-copy"
                        onclick="copyValue('{{$application->bankData->payout_rate}}')"
                        title="Copy Commission Rate"
                        style="cursor: pointer; font-size: 16px; color: gray;">
                    </i>
                    @endif
                </label>
                <input class="bank-detail-input form-control" type="number" name="commission_rate" id="commission_rate" placeholder="Enter Commission Rate" value="{{$application->commission_rate}}">
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Name
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 && Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                </label>
                <input class="bank-detail-input form-control" type="text" name="banker_name" id="banker_name" placeholder="Enter Banker Name" value=" {{$application->banker_name}}">
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Number
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 && Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                </label>
                <input class="bank-detail-input form-control" maxlength="10" type="number" name="banker_number" id="banker_number" placeholder="Enter Banker Number" value="{{$application->banker_number}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Email
                    @if(Auth::user()->roles[0]->pivot->role_id ==2 && Auth::user()->roles[0]->pivot->role_id==3)
                    <span class="required">*</span>
                    @endif</label>
                </label>
                <input class="bank-detail-input form-control" type="email" name="banker_email" id="banker_email" placeholder="Enter Banker Email" value="{{$application->banker_email}}">
            </div>



            @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Status<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="status" id="status">
                    <option value="pending" @if($application->status =='pending') selected @endif>Pending</option>
                    @if($application->status =='in-progress')
                    <option value="in-progress" @if($application->status =='in-progress') selected @endif>In progress</option>
                    <option value="completed" @if($application->status =='completed') selected @endif>Completed</option>
                    @endif
                    <option value="rejected" @if($application->status =='rejected') selected @endif>Rejected</option>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var user_type = `{{$isChannel}}`
        var group = `{{$application->group}}`

        if (user_type == 'channel') {
            $('.channel').show()
            $('.sales').hide()

        } else {
            $('.channel').hide()
            $('.sales').show()
        }
        if (group.toLowerCase() == 'secured') {
            $('.secured').show()
            $('.unsecured').hide()

        } else {
            $('.secured').hide()
            $('.unsecured').show()
        }
        $('#group').change(function() {

            if ($(this).val() == 'Secured') {
                $('.unsecured').hide()
                $('.secured').show()

            } else {
                $('.unsecured').show()
                $('.secured').hide()

            }
        })

        $('#disbursement_date').datepicker({
            format: 'yyyy-mm-dd', // Specify the date format
            autoclose: true, // Close the datepicker automatically after selection
            todayHighlight: true, // Highlight today's date
            endDate: new Date() // Set the end date to today, preventing future dates

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
                    $('#user_type').removeClass('is-valid').addClass('is-invalid');
                    $('#user_type').focus();
                    isValid = false;
                    return false;
                } else {
                    $('#user_type').addClass('is-valid').removeClass('is-invalid');
                }

                if ($('#user_type').val() == 'channel') {
                    if (!$('#channel_id').val()) {
                        $('#channel_id').removeClass('is-valid').addClass('is-invalid');
                        $('#channel_id').focus();
                        isValid = false;
                        return false;
                    } else {
                        $('#channel_id').addClass('is-valid').removeClass('is-invalid');
                    }
                } else {
                    if (!$('#sales_id').val()) {
                        $('#sales_id').removeClass('is-valid').addClass('is-invalid');
                        $('#sales_id').focus();
                        isValid = false;
                        return false;
                    } else {
                        $('#sales_id').addClass('is-valid').removeClass('is-invalid');
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
    
    function copyValue(selectedValue) {
            // Copy the selected value to the clipboard
            navigator.clipboard.writeText(selectedValue).then(() => {
                alert(`Copied`);
            }).catch(err => {
                console.error('Error copying text: ', err);
            });
        }

</script>
@endsection