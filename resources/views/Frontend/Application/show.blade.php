@php
$isChannel = DB::table('users')->where('id',$application->user_id)->value('user_type');
@endphp
@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection
@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/application') }}">Applications</a></li>
            <li class="breadcrumb-item active" aria-current="page">Application Detail</li>
        </ol>
    </nav>
</div>

<div class="bank-card">
    <div class="card-top-border">Basic Details</div>
    <div class="card-form">
        <!-- @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Select User Type<span class="required">*</span></label>
            <select class="bank-detail-input form-select" required name="user_type" id="user_type" disabled>
                <option value="" selected disabled>Select User Type</option>
                <option value="channel" @if($isChannel=='channel' ) selected @endif>Channel Partner</option>
                <option value="sales" @if($isChannel!='channel' ) selected @endif>Sales Person</option>
            </select>
        </div>
        <div class="bank-detail-inputs channel">
            <label class="bank-input-label" for="validationCustom01">Channel Partner<span class="required">*</span> </label>
            <select class="bank-detail-input form-select" required name="channel_sales_id" id="channel_sales_id" disabled>
                <option value="" selected disabled>Select Channel Partner</option>
                @foreach($channels as $channel)
                <option value="{{$channel->id}}" @if($channel->id == $application->user_id) selected @endif>{{$channel->first_name}}</option>
                @endforeach
            </select>
        </div>
        <div class="bank-detail-inputs sales">
            <label class="bank-input-label" for="validationCustom01">Sales Person<span class="required">*</span> </label>
            <select class="bank-detail-input form-select" required name="channel_sales_id" id="channel_sales_id" disabled>
                <option value="" selected disabled>Select Sales Person</option>
                @foreach($sales as $sale)
                <option value="{{$sale->id}}" @if($sale->id == $application->user_id) selected @endif>{{$sale->first_name}} {{$sale->last_name}}</option>
                @endforeach
            </select>
        </div>
        @endif -->
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Channel Partner </label>
            <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" value="{{$application->user->first_name}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Application Number/LAN No.<span class="required {{($application->app_id_is_matched?'text-success':'')}}">* ({{($application->app_id_is_matched?'Matched':'Unmatched')}})</span> </label>
            <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" value="{{$application->app_id}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Disbursment Date*<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="date" name="disbursement_date" id="disbursement_date" placeholder="Enter disbursment date" value="{{$application->disbursement_date}}" disabled />
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer Name<span class="required {{($application->customer_name_is_matched?'text-success':'')}}">* ({{($application->customer_name_is_matched?'Matched':'Unmatched')}})</span></label>
            <input class="bank-detail-input form-control" type="text" name="customer_name" id="customer_name" placeholder="Enter customer name" value="{{$application->customer_name}}" disabled>
        </div>

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Customer's Firm Name</label>
            <input class="bank-detail-input form-control" type="text" name="firm_name" id="firm_name" placeholder="Enter customer firm name" value="{{$application->firm_name}}" disabled>
        </div>

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Case State<span class="required">*</span></label>
            <select class="bank-detail-input form-select" name="case_state" id="case_state" disabled>
                <option value="" selected disabled>Select State</option>
                @foreach($states as $state)
                <option value="{{$state['state_code']}}" @if($state['state_code']==$application->case_state) selected @endif>{{$state['state']}}</option>
                @endforeach
            </select>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Case Location<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="case_location" id="case_location" value="{{$application->case_location}}" disabled>
        </div>


        <div class="bank-detail-inputs">
            <label class="bank-input-label">Select Bank<span class="required {{($application->bank_id_is_matched?'text-success':'')}}">* ({{($application->bank_id_is_matched?'Matched':'Unmatched')}})</span> </label>
            <select class="bank-detail-input form-select" required name="bank_id" id="bank_id" disabled>
                <option value="" selected disabled>Select Bank</option>
                @foreach($banks as $bank)
                <option value="{{$bank->id}}" @if($bank->id == $application->bank_id) selected @endif>{{$bank->name}}</option>
                @endforeach
            </select>
        </div>


        <div class="bank-detail-inputs">
            <label class="bank-input-label">Select Product<span class="required {{($application->product_id_is_matched?'text-success':'')}}">*({{($application->product_id_is_matched?'Matched':'Unmatched')}})</span></label>
            <select class="bank-detail-input form-select" required name="product_id" id="product_id" disabled>
                <option value="" selected disabled>Select Product</option>
                @foreach($products as $product)
                <option value="{{$product->id}}" @if($product->id == $application->product_id) selected @endif>{{$product->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Select Group<span class="required {{($application->group_is_matched?'text-success':'')}}">* ({{($application->group_is_matched?'Matched':'Unmatched')}})</span></label>
            <select class="bank-detail-input form-select" required name="group" id="group" disabled>
                <option value="Secured" @if($application->group =='Secured') selected @endif>Secured</option>
                <option value="Unsecured" @if($application->group =='Unsecured') selected @endif>Unsecured</option>
            </select>
        </div>
        <div class="bank-detail-inputs secured">
            <label class="bank-input-label">Fresh/BT<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="fresh_bt" id="fresh_bt" placeholder="Enter Fresh/BT" value="{{$application->fresh_or_bt}}" disabled>
        </div>
        <div class="bank-detail-inputs unsecured">
            <label class="bank-input-label">OTC/PDD Status<span class="required">*</span></label>
            <select class="bank-detail-input form-select" required name="otc_pdd" id="otc_pdd" disabled>
                <option value="" disabled selected>Select Option</option>
                <option value="Pending" @if($application->otc_or_pdd_status =='Pending') selected @endif>Pending</option>
                <option value="Clear" @if($application->otc_or_pdd_status =='Clear') selected @endif>Clear</option>
            </select>
        </div>

        <div class="bank-detail-inputs secured">
            <label class="bank-input-label">Any Subvention<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="number" name="any_subvention" id="any_subvention" placeholder="Enter Any Subvention" value="{{$application->any_subvention}}" disabled>
        </div>

        <div class="bank-detail-inputs unsecured">
            <label class="bank-input-label">PF Taken<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="number" name="pf_taken" id="pf_taken" placeholder="Enter PF Taken" value="{{$application->pf_taken}}" disabled>

        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Disburse Amount<span class="required {{($application->disburse_amount_is_matched?'text-success':'')}}">* ({{($application->disburse_amount_is_matched?'Matched':'Unmatched')}})</span></label>
            <input class="bank-detail-input form-control" type="number" name="disburse_amount" id="disburse_amount" placeholder="Enter Disburse Amount" value="{{$application->disburse_amount}}" disabled>
        </div>


        <div class="bank-detail-inputs">
            <label class="bank-input-label">Banker Name<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="text" name="banker_name" id="banker_name" placeholder="Enter Banker Name" value=" {{$application->banker_name}}" disabled>
        </div>


        <div class="bank-detail-inputs">
            <label class="bank-input-label">Banker Number<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="number" name="banker_number" id="banker_number" placeholder="Enter Banker Number" value="{{$application->banker_number}}" disabled>
        </div>

        <div class="bank-detail-inputs">
            <label class="bank-input-label">Banker Email<span class="required">*</span></label>
            <input class="bank-detail-input form-control" type="email" name="banker_email" id="banker_email" placeholder="Enter Banker Email" value="{{$application->banker_email}}" disabled>
        </div>



        @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Select Status<span class="required">*</span></label>
            <select class="bank-detail-input form-select" required name="status" id="status" disabled>
                <option value="pending" @if($application->status =='pending') selected @endif>Pending</option>
                <option value="rejected" @if($application->status =='rejected') selected @endif>Rejected</option>
                <option value="completed" @if($application->status =='completed') selected @endif>Completed</option>
            </select>
        </div>
        @endif


    </div>
</div>
<br>

{{-- Activity Logs Section - Visible to Admin, Maker, Checker --}}
@if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
<div class="bank-card">
    <div class="card-top-border">Activity Logs</div>
    <div class="card-form" style="padding: 20px;">
        <div id="showLogsLoading" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="text-muted ms-2">Loading activity logs...</span>
        </div>
        <div id="showLogsContent"></div>
        <div id="showLogsEmpty" class="text-center text-muted py-3" style="display:none;">
            No activity logs found for this application.
        </div>
    </div>
</div>
<style>
    .log-timeline { position: relative; padding-left: 30px; }
    .log-timeline::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 2px; background: #dee2e6; }
    .log-entry { position: relative; margin-bottom: 20px; padding: 12px 16px; background: #f8f9fa; border-radius: 8px; border-left: 3px solid #007bff; }
    .log-entry.created { border-left-color: #28a745; }
    .log-entry.approved { border-left-color: #007bff; }
    .log-entry.completed { border-left-color: #28a745; }
    .log-entry.rejected, .log-entry.checker_rejected { border-left-color: #dc3545; }
    .log-entry.deleted { border-left-color: #6c757d; }
    .log-entry.updated { border-left-color: #ffc107; }
    .log-entry.status_changed { border-left-color: #17a2b8; }
    .log-entry::before { content: ''; position: absolute; left: -25px; top: 16px; width: 10px; height: 10px; background: #007bff; border-radius: 50%; border: 2px solid #fff; }
    .log-entry .log-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
    .log-entry .log-user { font-weight: 600; font-size: 14px; }
    .log-entry .log-time { font-size: 12px; color: #6c757d; }
    .log-entry .log-desc { font-size: 13px; color: #333; }
    .log-entry .log-changes { font-size: 12px; color: #666; margin-top: 6px; padding-top: 6px; border-top: 1px solid #e9ecef; }
    .log-entry .log-changes .change-item { margin-bottom: 2px; }
    .log-entry .log-changes .old-val { text-decoration: line-through; color: #dc3545; }
    .log-entry .log-changes .new-val { color: #28a745; font-weight: 500; }
</style>
@endif
<br>


@endsection

@section('script')
<script>
    $(document).ready(function() {
        var user_type = `{{$isChannel}}`
        var group = `{{$application->group}}`

        if (user_type == 'channel') {
            $('.channel').show()
            $('.sales').hide()

        } else {
            $('.channel').hide()
            $('.sales').show()
        }
        if (group == 'Secured') {
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
        $('#user_type').change(function() {

            if ($(this).val() == 'channel') {
                $('.sales').hide()
                $('.channel').show()

            } else {
                $('.sales').show()
                $('.channel').hide()

            }
        })
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

            if (!$('#case_location').val()) {
                $('#case_location').removeClass('is-valid').addClass('is-invalid');
                $('#case_location').focus();
                isValid = false;
                return false;

            } else {
                $('#case_location').addClass('is-valid').removeClass('is-invalid');
            }

            if (!$('#case_state').val()) {
                $('#case_state').removeClass('is-valid').addClass('is-invalid');
                $('#case_state').focus();
                isValid = false;
                return false;

            } else {
                $('#case_state').addClass('is-valid').removeClass('is-invalid');
            }


            if (!$('#customer_name').val()) {
                $('#customer_name').removeClass('is-valid').addClass('is-invalid');
                $('#customer_name').focus();
                isValid = false;
                return false;
            } else {
                $('#customer_name').addClass('is-valid').removeClass('is-invalid');
            }


            if (!$('#bank_name').val()) {
                $('#bank_name').removeClass('is-valid').addClass('is-invalid');
                $('#bank_name').focus();
                $('.invalid-feedback').show()
                isValid = false;
                return false;
            } else {
                $('#bank_name').addClass('is-valid').removeClass('is-invalid');
                $('.invalid-feedback').hide()

            }


            if (!$('#product_name').val()) {
                $('#product_name').removeClass('is-valid').addClass('is-invalid');
                $('#product_name').focus();
                isValid = false;
                return false;

            } else {
                $('#product_name').addClass('is-valid').removeClass('is-invalid');
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
    });

    @if(in_array(auth()->user()->roles[0]->id, [1, 35, 36]))
    // Auto-load activity logs on show page
    $.ajax({
        url: '/application/{{ $application->id }}/logs',
        method: 'GET',
        success: function(response) {
            $('#showLogsLoading').hide();
            if (!response.logs || response.logs.length === 0) {
                $('#showLogsEmpty').show();
                return;
            }

            var html = '<div class="log-timeline">';
            response.logs.forEach(function(log) {
                html += '<div class="log-entry ' + log.action + '">';
                html += '<div class="log-header">';
                html += '<span class="log-user"><i class="fas fa-user"></i> ' + log.user_name + '</span>';
                html += '<span class="log-time"><i class="fas fa-clock"></i> ' + log.created_at + '</span>';
                html += '</div>';
                html += '<div class="log-desc">' + log.description + '</div>';

                if (log.changes && Object.keys(log.changes).length > 0) {
                    html += '<div class="log-changes">';
                    for (var field in log.changes) {
                        var change = log.changes[field];
                        var fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); });
                        html += '<div class="change-item">';
                        html += '<strong>' + fieldLabel + ':</strong> ';
                        html += '<span class="old-val">' + (change.old || '-') + '</span>';
                        html += ' &rarr; ';
                        html += '<span class="new-val">' + (change['new'] || '-') + '</span>';
                        html += '</div>';
                    }
                    html += '</div>';
                }

                html += '</div>';
            });
            html += '</div>';
            $('#showLogsContent').html(html);
        },
        error: function(xhr) {
            $('#showLogsLoading').hide();
            $('#showLogsContent').html('<div class="alert alert-danger">Failed to load activity logs.</div>');
        }
    });
    @endif
</script>
@endsection
