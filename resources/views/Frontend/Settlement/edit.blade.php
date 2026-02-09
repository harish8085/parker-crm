@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
@endsection
@section('body')
<h2>Update Settlement</h2>
<form class="needs-validation" action="{{url('/settlement/update/'.$settlement->id)}}" method="POST" novalidate>
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

    {{-- Settlement Summary --}}
    <div class="bank-card">
        <div class="card-top-border">Settlement Summary - {{ $channelUser->first_name ?? '' }} {{ $channelUser->last_name ?? '' }}</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Channel Partner</label>
                <input class="bank-detail-input form-control" type="text" value="{{ $channelUser->first_name ?? '' }} {{ $channelUser->last_name ?? '' }}" disabled />
            </div>
            <div class="bank-detail-inputs" style="display: none;">
                <label class="bank-input-label">Settlement Date</label>
                <input class="bank-detail-input form-control" type="date" name="settlement_date" id="settlement_date" value="{{$settlement->settlement_date}}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Total Settlement Amount</label>
                <input class="bank-detail-input form-control" type="number" name="amount" id="amount" placeholder="Enter amount" value="{{$settlement->amount}}" @if($settlement->status =='completed') readonly @endif/>
            </div>
            @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3)
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Total Gross Amount</label>
                <input class="bank-detail-input form-control" type="text" value="{{$settlement->gross_amount}}" id="totalAmount" disabled />
            </div>
            @endif

            @if($settlement->status =='checker')
            <input type="hidden" value="bankPending" id="status" name="status" />
            @elseif($settlement->status =='bankPending')
            <input type="hidden" value="pending" id="status" name="status" />
            @else
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Select Status<span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="status" id="status" @if($settlement->status =='completed') disabled @endif>
                    <option value="pending" @if($settlement->status =='pending') selected @endif>Pending</option>
                    <option value="rejected" @if($settlement->status =='rejected') selected @endif>Rejected</option>
                    <option value="completed" @if($settlement->status =='completed') selected @endif>Completed</option>
                </select>
            </div>
            @endif
        </div>
    </div>
    <br>

    {{-- Application-wise Distribution Breakdown --}}
    <div class="bank-card">
        <div class="card-top-border">Application-wise Distribution</div>
        <div class="table-responsive p-3">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="table-header">Sr. No.</th>
                        <th class="table-header">Application No.</th>
                        <th class="table-header">Customer Name</th>
                        <th class="table-header">Rate %</th>
                        <th class="table-header">Gross Amount</th>
                        <th class="table-header">TDS (2%)</th>
                        <th class="table-header">Net Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settlement_distributions as $key => $dist)
                    @php
                        $app = \App\Models\Application::find($dist->application_id);
                    @endphp
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>
                            @if($app)
                            <a href="{{ url('/application/view/' . $app->id) }}" class="text-primary">{{ $app->app_id }}</a>
                            @else
                            N/A
                            @endif
                        </td>
                        <td>{{ $app->customer_name ?? '-' }}</td>
                        <td>{{ $dist->received_rate ?? '-' }}%</td>
                        <td>₹ {{ number_format($dist->gross_amount ?? 0, 2) }}</td>
                        <td>₹ {{ number_format($dist->tds ?? 0, 2) }}</td>
                        <td>₹ {{ number_format($dist->amount ?? 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold; background-color: #f5f5f5;">
                        <td colspan="4" class="text-end">Totals:</td>
                        <td>₹ {{ number_format($settlement_distributions->sum('gross_amount'), 2) }}</td>
                        <td>₹ {{ number_format($settlement_distributions->sum('tds'), 2) }}</td>
                        <td>₹ {{ number_format($settlement_distributions->sum('amount'), 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <br>

    {{-- Bank Details Section --}}
    @if($settlement->status !='checker')
    <div class="bank-card">
        <div class="card-top-border">Bank Details</div>
        @if($settlement->status =='bankPending' || $settlement->status =='pending')
        <div class="add-more-btn" id="add-more-section">
            <button class="add-p-btn1 add-more1" type="button" data-bs-toggle="modal" data-bs-target="#myModal">Add Bank</button>
        </div>
        @endif
        <div id="data">
            @php
                $uniqueBankDistributions = $settlement_distributions->unique('bank_account_id')->filter(fn($d) => $d->bank_account_id);
            @endphp
            @if($uniqueBankDistributions->isNotEmpty())
            @foreach($uniqueBankDistributions as $key => $dist)
            <div class="bank-detail-row card-form" id="bankTemplate">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                    <select class="bank-detail-input form-select" required name="bank[]">
                        <option value="" selected disabled>Select Bank Account</option>
                        @foreach($banks as $bank)
                        <option value="{{$bank->id}}" @if($dist->bank_account_id ==$bank->id) selected @endif>{{$bank->holder_name}}({{$bank->account_number}})</option>
                        @endforeach
                    </select>
                </div>

                @if($settlement->status =='completed' && $dist->utr_number)
                <div class="bank-detail-inputs utrnumber">
                    <label class="bank-input-label">UTR Number</label>
                    <input class="bank-detail-input form-control" type="text" value="{{$dist->utr_number}}" readonly />
                </div>
                @endif
            </div>
            @endforeach
            @else
            <div class="bank-detail-row card-form" id="bankTemplate">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                    <select class="bank-detail-input form-select" required name="bank[]">
                        <option value="" selected disabled>Select Bank Account</option>
                        @foreach($banks as $bank)
                        <option value="{{$bank->id}}">{{$bank->holder_name}}({{$bank->account_number}})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="save-btn-container">
        <button class="save-btn" id="submitBtn">
            @if($settlement->status == 'checker')
            Approve
            @else
            Save
            @endif
        </button>
    </div>
</form>

@endsection

@section('modal')
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Bank</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>
            <div class="modal-body" style="padding: 20px 20px;">
                <form action="{{url('profile/addBank')}}" method="POST" id="addBankForm">
                    @csrf
                    <div class="row">
                        <div class="col-12 p-2">
                            <h5 style="margin: 0; color: black;">Fill Details</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Bank Name" name="bank_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Branch Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Branch Name" name="branch_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Holder Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Holder Name" name="holder_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Account Number<span class="required">*</span></label>
                            <input type="number" class="form-control" min=0 placeholder="Enter Account Number" name="account_number" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">IFSC<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter IFSC Code" name="ifsc_code" required>
                        </div>
                    </div>
                    <input type="hidden" class="form-control" name="user_id" value="{{$settlement->user_id}}" required>
                    <div class="save-btn-container">
                        <button class="save-btn" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#submitBtn').click(function(event) {
            event.preventDefault();
            var isValid = true;
            if (isValid) {
                $('.needs-validation').submit();
            }
        });

        $('#addBankForm').submit(function(event) {
            event.preventDefault();
            var isValid = true;
            var ifscRegex = /^[A-Z]{4}[0][A-Z0-9]{6}$/;
            $('#ifsc_code').change(function() {
                if (!ifscRegex.test($('#ifsc_code').val())) {
                    $('#ifsc_code').removeClass('is-valid').addClass('is-invalid');
                    isValid = false
                } else {
                    $('#ifsc_code').addClass('is-valid').removeClass('is-invalid');
                }
            });

            if (isValid) {
                var formData = $(this).serialize();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        location.reload();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endsection
