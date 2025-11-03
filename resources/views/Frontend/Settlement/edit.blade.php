@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
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
    <div class="bank-card">
        <div class="card-top-border">Settlement Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Application Number/LAN No.<span class="required text-primary cursor-pointer" onclick="window.location.href='/application/view/{{$application->id}}'">View</span> </label>
                <input class="bank-detail-input form-control" type="text" name="app_id" id="app_id" placeholder="Enter application number" value="{{$application->app_id}}" />
            </div>
            <div class="bank-detail-inputs" style="display: none;">
                <label class="bank-input-label">Settlement Date</label>
                <input class="bank-detail-input form-control" type="date" name="settlement_date" id="settlement_date" placeholder="Enter settlement date" value="{{$settlement->settlement_date}}" />
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Settlement Rate</label>
                <input class="bank-detail-input form-control" type="number" name="received_rate" id="received_rate" max="100" placeholder="Enter settlement rate" value="{{$settlement->received_rate}}"  @if($settlement->status =='completed') readonly @endif/>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Settlement Amount</label>
                <input class="bank-detail-input form-control" type="number" name="amount" id="amount" placeholder="Enter  amount" value="{{$settlement->amount}}" @if($settlement->status =='completed') readonly @endif/>
            </div>
            @if(Auth::user()->roles[0]->pivot->role_id !=2 && Auth::user()->roles[0]->pivot->role_id!=3 )
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Gross Amount</label>
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


    @if($settlement->status !='checker')
    <div class="bank-card">
        <div class="card-top-border">Bank Details</div>
        @if($settlement->status =='bankPending' || $settlement->status =='pending')

        <div class="add-more-btn" id="add-more-section">
            <button class="add-p-btn1 add-more">Add More</button>&nbsp;&nbsp;&nbsp;
            <button class="add-p-btn1 add-more1" data-bs-toggle="modal" data-bs-target="#myModal">Add Bank</button>
        </div>
        @endif
        <div id="data">
            @if($settlement_distributions->isNotEmpty())
            @foreach($settlement_distributions as $key=>$settlement_distribution)
            <div class="bank-detail-row card-form" id="bankTemplate">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Select Bank<span class="required">*</span></label>
                    <select class="bank-detail-input form-select" required name="bank[]">
                        <option value="" selected disabled>Select Bank Account</option>
                        @foreach($banks as $bank)
                        <option value="{{$bank->id}}" @if($settlement_distribution->bank_account_id ==$bank->id) selected @endif>{{$bank->holder_name}}({{$bank->account_number}})</option>
                        @endforeach
                    </select>
                </div>

                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Amount Recieve<span class="required">*</span></label>
                    <input class="bank-detail-input form-control" type="number" name="recieve_amount[]" value="{{$settlement_distribution->amount}}" placeholder="Enter amount have to recieved" @if($settlement->status =='completed') readonly @endif />
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">TDS Deduction</label>
                    <input class="bank-detail-input form-control" type="number" name="tds[]" placeholder="Enter tds amount" value="{{$settlement_distribution->tds}}" @if($settlement->status =='completed') readonly @endif/>
                </div>

                @if($settlement->status =='completed')
                <div class="bank-detail-inputs utrnumber">
                    <label class="bank-input-label">UTR Number</label>
                    <input class="bank-detail-input form-control" type="number" name="utr_number[]" placeholder="Enter tds amount" value="{{$settlement_distribution->utr_number}}" @if($settlement->status =='completed') readonly @endif/>
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

                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Amount Recieve<span class="required">*</span></label>
                    <input class="bank-detail-input form-control" type="number" name="recieve_amount[]" @if($settlement->status =='completed') readonly @endif/>
                </div>
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">TDS Deduction</label>
                    <input class="bank-detail-input form-control" type="number" name="tds[]" placeholder="Enter tds amount" @if($settlement->status =='completed') readonly @endif/>
                </div>
                @if($settlement->status !='bankPending' && $settlement->status !='checker')
                <div class="bank-detail-inputs utrnumber">
                    <label class="bank-input-label">UTR Number</label>
                    <input class="bank-detail-input form-control" type="number" name="utr_number[]" placeholder="Enter utr number" @if($settlement->status =='completed') readonly @endif/>
                </div>
                @endif

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

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Bank</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
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
        var settlementAmount = `{{$settlement->amount}}`
        var tdsAmount = `{{$settlement->tds}}`
        var rate = `{{$settlement->received_rate}}`
        var totalAmount = `{{$settlement->gross_amount}}`

        $('#received_rate').change(() => {
            var rate = $('#received_rate').val()
            if (rate > 100) {
                $('#received_rate').val(100)
                rate = 100
            }
            var amount = (parseFloat(totalAmount) * parseFloat(rate)) / 100
            $('#amount').val(amount.toFixed(2))

        })

        $('#amount').change(() => {
            var amount = $('#amount').val()
            if (parseFloat(amount) > parseFloat(totalAmount)) {
                $('#amount').val(totalAmount)
                amount = totalAmount
            }
            var rate = (parseFloat(amount)) * 100 / parseFloat(totalAmount)
            $('#received_rate').val(rate.toFixed(2))
        })

        var settlementAmount = parseFloat(`{{$settlement->amount}}`);

        // Validate "Amount Received" for each bank detail input
        $(document).on('change', '.bank-detail-inputs input[name="recieve_amount[]"]', function() {
            var totalReceived = 0;

            // Sum all the received amounts
            $('.bank-detail-inputs input[name="recieve_amount[]"]').each(function() {
                var value = parseFloat($(this).val()) || 0;
                totalReceived += value;
            });

            // Check if the total received exceeds the settlement amount
            if (totalReceived > settlementAmount) {
                alert('Total received amount exceeds the settlement amount!');
                $(this).closest('.bank-detail-row').find('input[name="tds[]"]').val('');

                $(this).val(''); // Clear the invalid input
            }
        });

        // Optional: Validate for each individual input as well
        $(document).on('blur', '.bank-detail-inputs input[name="recieve_amount[]"]', function() {
            var value = parseFloat($(this).val()) || 0;
            if (value > settlementAmount) {
                alert('Amount cannot exceed the settlement amount!');
                $(this).val(''); // Clear the invalid input
            }
        });

        $(document).on('input', '.bank-detail-inputs input[name="recieve_amount[]"]', function() {
            var receivedAmount = parseFloat($(this).val());
            if (!isNaN(receivedAmount) && receivedAmount >= 0) {

                var tdsAmount = (receivedAmount * 2) / 100; // 2% TDS calculation
                $(this).closest('.bank-detail-row').find('input[name="tds[]"]').val(tdsAmount.toFixed(2));
            } else {
                $(this).closest('.bank-detail-row').find('input[name="tds[]"]').val(''); // Clear TDS if invalid
            }
        });
        $('.add-more').click(function(event) {
            event.preventDefault();

            // Get the container where bank detail inputs will be added
            var container = $('#data');

            // Clone the bank detail template
            var clone = $('#bankTemplate').clone();
            clone.find('input').val('');
            var container1 = $('#data');

            container.append('<hr>');

            // Make the cloned inputs visible by removing the "hidden" style
            clone.removeAttr('style');

            // Append the cloned bank detail inputs to the container
            container.append(clone);
        });

        $('.add-more1').click(function(event) {
            event.preventDefault();
            // $('#myModal').show()
        });

        $('#submitBtn').click(function(event) {
            // Prevent default form submission
            event.preventDefault();
            // Perform form validation
            var isValid = true;

            // If form is valid, submit the form
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