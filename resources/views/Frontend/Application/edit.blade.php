@php
$isChannel = DB::table('users')->where('id',$application->user_id)->value('user_type');
$roleId = $effectiveRoleId ?? (Auth::user()->roles[0]->pivot->role_id ?? Auth::user()->roles[0]->id ?? 0);
@endphp
@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection
@section('body')
<div class="breadcrumb-container d-flex justify-content-between align-items-center mb-3 mt-5" style="margin-bottom: 24px;">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white px-0 py-2 " style="margin-bottom:0;">
                <li class="breadcrumb-item"><a href="{{ url('/application') }}">Applications</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Application</li>
            </ol>

        </nav>
    </div>
    <div>
        <a href="{{ url('/application') }}" class="btn btn-secondary">Back</a>
    </div>

</div>

@if(in_array($roleId, [2, 3, 37]) && $application->status !== 'pending')
<div class="alert alert-warning">
    <i class="fas fa-lock"></i> This application is no longer in Pending status and cannot be edited.
    <a href="{{ url('/application') }}" class="btn btn-sm btn-secondary ms-3">Back to List</a>
</div>
@else
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
            @php
                $userTypeLabel = $selectedUserType === 'associate'
                    ? 'Associate Partner'
                    : ($selectedUserType === 'sales' ? 'Sales Person' : 'Channel Partner');

                $channelDisplayName = $application->user
                    ? trim(($application->user->first_name ?? '') . ' ' . ($application->user->last_name ?? ''))
                    : '-';

                $parentChannelDisplayName = $application->parentChannel
                    ? trim(($application->parentChannel->first_name ?? '') . ' ' . ($application->parentChannel->last_name ?? ''))
                    : '-';
            @endphp

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select User Type<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" value="{{ $userTypeLabel }}" disabled />
                <input type="hidden" name="user_type" id="user_type" value="{{ $selectedUserType }}">
            </div>

            @if($selectedUserType === 'associate')
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Parent Channel Partner<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" value="{{ $parentChannelDisplayName }}" disabled />
                <input type="hidden" name="channel_id" id="channel_id" value="{{ $application->parent_channel_id }}">
                <input type="hidden" name="associate_channel_id" id="associate_channel_id" value="{{ $application->parent_channel_id }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Associate Name<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelDisplayName }}" disabled />
                <input type="hidden" name="associate_id" id="associate_id" value="{{ $application->user_id }}">
            </div>
            @elseif($selectedUserType === 'sales')
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Sales Person<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelDisplayName }}" disabled />
                <input type="hidden" name="sales_id" id="sales_id" value="{{ $application->user_id }}">
                <input type="hidden" name="channel_sales_id" id="channel_sales_id" value="{{ $application->user_id }}">
            </div>
            @else
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Channel Partner<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelDisplayName }}" disabled />
                <input type="hidden" name="channel_id" id="channel_id" value="{{ $application->user_id }}">
                <input type="hidden" name="associate_channel_id" id="associate_channel_id" value="">
                <input type="hidden" name="associate_id" id="associate_id" value="">
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
                <input class="bank-detail-input form-control" type="text" name="disbursement_date" id="disbursement_date" placeholder="Enter disbursment date" value="{{ $application->disbursement_date ? \Carbon\Carbon::parse($application->disbursement_date)->format('d-m-Y') : '' }}" autocomplete="off" />
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
                <label class="bank-input-label">Customer Phone</label>
                <input class="bank-detail-input form-control" type="text" name="customer_phone" id="customer_phone" placeholder="Enter customer phone number" value="{{$application->customer_phone}}" maxlength="20">
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
                @if($application->bank_mis_id&& $application->bankData)
                <span class="required {{(strtolower((string) $application->case_state) == strtolower((string) $application->bankData->case_state)?'text-success':'')}}">({{($application->bankData->case_state? $application->bankData->case_state:'')}})</span>
                @endif
                </label>
                <select class="bank-detail-input form-select" name="case_state" id="case_state">
                    <option value="" selected disabled>Select State</option>
                    @foreach($states as $state)
                    <option value="{{$state['state_code']}}" @if($state['state_code']==$application->case_state) selected @endif>{{$state['state']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Case Location
                    @if($application->bank_mis_id&& $application->bankData)
                    <span class="required {{(strtolower($application->case_location) == strtolower($application->bankData->case_location)?'text-success':'')}}">({{($application->bankData->case_location? $application->bankData->case_location:'')}})</span>
                    @endif
                </label>
                <input class="bank-detail-input form-control" type="text" name="case_location" id="case_location" placeholder="Enter case location" value="{{$application->case_location}}">
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

            @if($roleId != 37)
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
            @endif

            @if(in_array($roleId, [1, 2, 35, 36]))
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Sharing Commission <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" step="0.01" name="sharing_commission" id="sharing_commission" placeholder="Enter Sharing Commission" value="{{$application->sharing_commission}}">
            </div>
            @else
            <input type="hidden" name="sharing_commission" id="sharing_commission" value="{{$application->sharing_commission}}">
            @endif

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Name
                    @if(in_array($roleId, [2, 3]))
                    <span class="required">*</span>
                    @endif</label>
                <input class="bank-detail-input form-control" type="text" name="banker_name" id="banker_name" placeholder="Enter Banker Name" value=" {{$application->banker_name}}">
            </div>


            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Number
                    @if(in_array($roleId, [2, 3]))
                    <span class="required">*</span>
                    @endif</label>
                <input class="bank-detail-input form-control" maxlength="10" type="number" name="banker_number" id="banker_number" placeholder="Enter Banker Number" value="{{$application->banker_number}}">
            </div>

            <div class="bank-detail-inputs">
                <label class="bank-input-label">Banker Email
                    @if(in_array($roleId, [2, 3]))
                    <span class="required">*</span>
                    @endif</label>
                <input class="bank-detail-input form-control" type="email" name="banker_email" id="banker_email" placeholder="Enter Banker Email" value="{{$application->banker_email}}">
            </div>



            {{-- Current Status display (read-only, visible to all users) --}}
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Current Status</label>
                <div style="padding: 8px 0;">
                    @php
                    $statusColorMap = [
                    'pending' => 'warning',
                    'approved' => 'primary',
                    'completed' => 'success',
                    'rejected' => 'danger',
                    ];
                    $badgeColor = $statusColorMap[$application->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $badgeColor }}" style="font-size: 14px; padding: 6px 14px;">{{ ucfirst($application->status) }}</span>
                </div>
            </div>

            {{-- Hidden status field - set by action buttons via JS --}}
            <input type="hidden" name="status" id="statusInput" value="{{ $application->status }}">
            <input type="hidden" name="rejection_reason" id="rejectionReasonInput" value="">

        </div>
    </div>
    <br>

    {{-- Action buttons based on role --}}
    <div class="save-btn-container">
        @php $currentRole = $roleId; @endphp

        @if($currentRole == 35)
        {{-- Maker: Save, Approve, Reject --}}
        <button type="button" class="btn btn-success" id="saveBtn" onclick="setStatusAndSubmit('{{ $application->status }}')">
            <i class="fas fa-save"></i> Save
        </button>
        <button type="button" class="btn btn-primary" id="approveBtn" onclick="setStatusAndSubmit('approved')">
            <i class="fas fa-check-circle"></i> Approve
        </button>
        <button type="button" class="btn btn-danger" id="rejectBtn" onclick="setStatusAndSubmit('rejected')">
            <i class="fas fa-times-circle"></i> Reject
        </button>
        @elseif($currentRole == 36)
        {{-- Checker: Save, Complete, Reject (with reason modal) --}}
        <button type="button" class="btn btn-secondary" id="saveBtn" onclick="setStatusAndSubmit('{{ $application->status }}')">
            <i class="fas fa-save"></i> Save
        </button>
        <button type="button" class="btn btn-success" id="completeBtn" onclick="setStatusAndSubmit('completed')">
            <i class="fas fa-check"></i> Mark as Completed
        </button>
        <button type="button" class="btn btn-danger" id="checkerRejectBtn" data-bs-toggle="modal" data-bs-target="#checkerRejectModal">
            <i class="fas fa-times-circle"></i> Reject
        </button>
        @elseif($currentRole == 1)
        {{-- Admin: Save, Approve, Reject --}}
        <button type="button" class="btn btn-secondary" onclick="setStatusAndSubmit('{{ $application->status }}')">
            <i class="fas fa-save"></i> Save
        </button>
        <button type="button" class="btn btn-primary" onclick="setStatusAndSubmit('approved')">
            <i class="fas fa-check-circle"></i> Approve
        </button>
        <button type="button" class="btn btn-danger" onclick="setStatusAndSubmit('rejected')">
            <i class="fas fa-times-circle"></i> Reject
        </button>
        @else
        {{-- Channel/Sales/Associate: Save only --}}
        <button class="btn btn-primary" id="submitBtn">Save</button>
        @endif
        <button class="btn btn-secondary" onclick="window.location.href='{{ url('/application') }}'; return false;">Cancel</button>
    </div>

    {{-- Checker Reject Reason Modal --}}
    @if($roleId == 36)
    <div class="modal fade" id="checkerRejectModal" tabindex="-1" aria-labelledby="checkerRejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkerRejectModalLabel">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">This application will be sent back to the Maker for review.</p>
                    <div class="mb-3">
                        <label for="checkerRejectReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="checkerRejectReason" rows="3" placeholder="Enter reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="submitCheckerReject()">
                        <i class="fas fa-times-circle"></i> Confirm Reject
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</form>
@endif

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    function validateApplicationForm(status) {
        var isValid = true;
        var roleId = Number(`{{$roleId}}`);

        // Roles that can choose the target user
        if (roleId != 3 && roleId != 37) {
            if (!$('#user_type').val()) {
                $('#user_type').removeClass('is-valid').addClass('is-invalid');
                $('#user_type').focus();
                return false;
            } else {
                $('#user_type').addClass('is-valid').removeClass('is-invalid');
            }
            if ($('#user_type').val() == 'channel') {
                if (!$('#channel_id').val()) {
                    $('#channel_id').removeClass('is-valid').addClass('is-invalid');
                    $('#channel_id').focus();
                    return false;
                } else {
                    $('#channel_id').addClass('is-valid').removeClass('is-invalid');
                }
            } else if ($('#user_type').val() == 'associate') {
                if (!$('#associate_channel_id').val()) {
                    $('#associate_channel_id').removeClass('is-valid').addClass('is-invalid');
                    $('#associate_channel_id').focus();
                    return false;
                } else {
                    $('#associate_channel_id').addClass('is-valid').removeClass('is-invalid');
                }
                if (!$('#associate_id').val()) {
                    $('#associate_id').removeClass('is-valid').addClass('is-invalid');
                    $('#associate_id').focus();
                    return false;
                } else {
                    $('#associate_id').addClass('is-valid').removeClass('is-invalid');
                }
            }
        }

        // Common validations
        if (!$('#app_id').val()) {
            $('#app_id').removeClass('is-valid').addClass('is-invalid');
            $('#app_id').focus();
            return false;
        } else {
            $('#app_id').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#disbursement_date').val()) {
            $('#disbursement_date').removeClass('is-valid').addClass('is-invalid');
            $('#disbursement_date').focus();
            return false;
        } else {
            $('#disbursement_date').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#customer_name').val()) {
            $('#customer_name').removeClass('is-valid').addClass('is-invalid');
            $('#customer_name').focus();
            return false;
        } else {
            $('#customer_name').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#bank_id').val()) {
            $('#bank_id').removeClass('is-valid').addClass('is-invalid');
            $('#bank_id').focus();
            return false;
        } else {
            $('#bank_id').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#product_id').val()) {
            $('#product_id').removeClass('is-valid').addClass('is-invalid');
            $('#product_id').focus();
            return false;
        } else {
            $('#product_id').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#group').val()) {
            $('#group').removeClass('is-valid').addClass('is-invalid');
            $('#group').focus();
            return false;
        } else {
            $('#group').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#disburse_amount').val()) {
            $('#disburse_amount').removeClass('is-valid').addClass('is-invalid');
            $('#disburse_amount').focus();
            return false;
        } else {
            $('#disburse_amount').addClass('is-valid').removeClass('is-invalid');
        }

        // Commission rate required when completing (if field is visible for role)
        if (status === 'completed' && $('#commission_rate').length) {
            if (!$('#commission_rate').val() || $('#commission_rate').val().trim() === '') {
                alert('Commission Rate field cannot be empty when completing an application!');
                $('#commission_rate').removeClass('is-valid').addClass('is-invalid');
                $('#commission_rate').focus();
                return false;
            } else {
                $('#commission_rate').addClass('is-valid').removeClass('is-invalid');
            }
        }

        // Sharing commission required before approve/complete
        if ((status === 'approved' || status === 'completed') && $('#sharing_commission').length) {
            const sharingVal = ($('#sharing_commission').val() || '').trim();
            if (!sharingVal) {
                alert('Sharing Commission is required before approving or completing the application!');
                $('#sharing_commission').removeClass('is-valid').addClass('is-invalid');
                $('#sharing_commission').focus();
                return false;
            } else {
                $('#sharing_commission').addClass('is-valid').removeClass('is-invalid');
            }
        }

        // Channel/Sales: validate banker fields
        if (roleId == 2 || roleId == 3) {
            if (!$('#banker_name').val()) {
                $('#banker_name').removeClass('is-valid').addClass('is-invalid');
                $('#banker_name').focus();
                return false;
            } else {
                $('#banker_name').addClass('is-valid').removeClass('is-invalid');
            }
            if (!$('#banker_number').val()) {
                $('#banker_number').removeClass('is-valid').addClass('is-invalid');
                $('#banker_number').focus();
                return false;
            } else {
                $('#banker_number').addClass('is-valid').removeClass('is-invalid');
            }
            if (!$('#banker_email').val()) {
                $('#banker_email').removeClass('is-valid').addClass('is-invalid');
                $('#banker_email').focus();
                return false;
            } else {
                $('#banker_email').addClass('is-valid').removeClass('is-invalid');
            }
        }

        return true;
    }

    function setStatusAndSubmit(status) {
        if (!validateApplicationForm(status)) {
            return;
        }
        document.getElementById('statusInput').value = status;
        document.querySelector('form.needs-validation').submit();
    }

    function submitCheckerReject() {
        var reason = document.getElementById('checkerRejectReason').value.trim();
        if (!reason) {
            alert('Please enter a rejection reason.');
            return;
        }
        if (!validateApplicationForm('rejected')) {
            return;
        }
        document.getElementById('statusInput').value = 'rejected';
        document.getElementById('rejectionReasonInput').value = reason;
        var modal = bootstrap.Modal.getInstance(document.getElementById('checkerRejectModal'));
        if (modal) modal.hide();
        document.querySelector('form.needs-validation').submit();
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var group = `{{$application->group}}`
        var roleId = Number(`{{$roleId}}`);
        var authUserId = Number(`{{Auth::id()}}`);
        var applicationUserId = Number(`{{$application->user_id}}`);

        $('.channel').hide()
        $('.associate-channel').hide()
        $('.associate').hide()

        function loadAssociates(channelId, selectedAssociateId = null) {
            if (!$('#associate_id').is('select')) {
                return;
            }
            $('#associate_id').html('<option value="" selected disabled>Select Associate Partner</option>');
            if (!channelId) {
                return;
            }
            $.ajax({
                url: '/application/channel/' + channelId + '/associates',
                type: 'GET',
                success: function(response) {
                    $.each(response, function(_, associate) {
                        var emp = associate.emp_id ? associate.emp_id : associate.id;
                        var selected = Number(selectedAssociateId) === Number(associate.id) ? 'selected' : '';
                        $('#associate_id').append('<option value="' + associate.id + '" ' + selected + '>' + associate.name + ' (' + emp + ')</option>');
                    });
                }
            });
        }

        function handleUserTypeVisibility() {
            $('.channel, .associate-channel, .associate').hide();
            if ($('#user_type').val() == 'channel') {
                $('.channel').show();
            } else if ($('#user_type').val() == 'associate') {
                $('.associate-channel').show();
                $('.associate').show();
                if (roleId === 2) {
                    $('#associate_channel_id').val(authUserId);
                }
                loadAssociates($('#associate_channel_id').val(), applicationUserId);
            }
        }

        $('#associate_channel_id').change(function() {
            loadAssociates($(this).val(), null);
        });

        handleUserTypeVisibility()
        if ($('#user_type').val() == 'associate') {
            if (roleId === 2 && !$('#associate_channel_id').val()) {
                $('#associate_channel_id').val(authUserId);
            }
            loadAssociates($('#associate_channel_id').val(), applicationUserId);
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
            format: 'dd-mm-yyyy', // Specify the date format
            autoclose: true, // Close the datepicker automatically after selection
            todayHighlight: true, // Highlight today's date
            endDate: new Date() // Set the end date to today, preventing future dates

        })
        // case_state is now free-text; no district lookup needed.
        $('#user_type').change(handleUserTypeVisibility)


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
            event.preventDefault();
            var statusVal = $('#statusInput').val();
            if (!validateApplicationForm(statusVal)) {
                return false;
            }
            if ($('#user_type').val() === 'associate') {
                $('#channel_id').val($('#associate_channel_id').val());
            }
            $('.needs-validation').submit();
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
