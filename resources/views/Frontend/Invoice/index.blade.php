@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">

<style>
    .date_range {
        display: none;
        /* Hidden by default */
    }
</style>
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Invoice</li>
        </ol>
    </nav>
</div>
<div class="card">

    <div class="application-header">
        <h3 class="application-heading">Generate Invoice</h3>
    </div>

    <!-- filter form -->
    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Date Range</label>
                    <select class="bank-detail-input form-select select" required name="date" id="date">
                        <option value="" selected disabled></option>
                        <option value="custom">Custom</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="last_3months">Last 3 months</option>
                        <option value="last_6months">Last 6 months</option>
                        <option value="this_year">This Year</option>
                        <option value="last_year">Last Year</option>
                    </select>
                </div>
                <div class="bank-detail-inputs date_range">
                    <label class="bank-input-label"><b>Date Range</b></label>
                    <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" />
                </div>

            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Bank Name</label>
                    <select class="bank-detail-input form-select select" required name="bank_name" id="bank_name">
                        <option value="">Select Bank Name</option>
                        @foreach($bank as $b)
                        <option>{{$b->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Product Name</label>
                    <select class="bank-detail-input form-select select" required name="product_name" id="product_name">
                        <option value="">Select Product Name</option>
                        @foreach($product as $p)
                        <option>{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-12 mt-2">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary me-2" type="submit" name="filter" id="filter">Filter</button>
                    <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <div class="bank-card" id="actionButton">
        <div class="row">
            <div class="col-lg-4 ml-3 mb-2">
                <button id="generateInvoiceBtn" class="btn btn-success generatInvoice">Generate Invoice</button>
            </div>
        </div>
    </div>
    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Invoice.Table.invoice_table')
    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Filter</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form id="filterForm">
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select User Type<span class="required">*</span></label>
                            <div class="roles-dropdown">
                                <select class="form-select" name="user_type" id="user_type">
                                    <option value="" selected>Select User Type</option>
                                    <option value="channel">Channel Partner</option>
                                    <option value="sales">Sales Person</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row sales">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Sales Person<span class="required">*</span></label>
                            <select class="form-select" name="sales_id" id="sales_id">
                                <option value="" selected disabled>Select Sales Person</option>
                                @foreach($sales as $sale)
                                <option value="{{ $sale->id }}">{{ $sale->first_name }} {{ $sale->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row channel">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Channel Partner<span class="required">*</span></label>
                            <div class="roles-dropdown">
                                <select class="form-select" name="channel_id" id="channel_id">
                                    <option value="" selected disabled>Select Channel Partner</option>
                                    @foreach($channels as $channel)
                                    <option value="{{ $channel->id }}">{{ $channel->first_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">From Date <span class="required">*</span></label>
                            <input type="date" class="form-control" placeholder="Enter from date" name="from" id="from">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">To Date<span class="required">*</span></label>
                            <input type="date" class="form-control" placeholder="Enter from date" name="to" id="to">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Status<span class="required">*</span></label>
                            <div class="roles-dropdown">
                                <select class="form-select" name="status" id="status">
                                    <option selected>All</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="save-btn-container">
                        <button type="submit" class="save-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="invoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" style="margin-top: 200px; width:100%;" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>
<div class="modal fade" id=" invoic_input_Modal" data-bs-backdrop="static" data-bs-keyboard="false" style="width:100%;" >
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="margin-top: 200px;">
            <div class="modal-header" style="height: 50px;">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 20px 25px; max-height: 400px; overflow-y: auto;">
                <form id="invoiceInputForm">
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Invoice No <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter invoice no (15-16 digits)" name="invoice_no" id="invoice_no" pattern="\d{15,16}" maxlength="16" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Date <span class="required">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" id="invoice_date" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank GST No <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank GST no" name="bank_gst_no" id="bank_gst_no" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank HSN code" name="bank_hsn_code" id="bank_hsn_code" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Address</label>
                            <textarea class="form-control" placeholder="Enter bank address" name="bank_address" id="bank_address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">DSA PAN</label>
                            <input type="text" class="form-control" placeholder="Enter DSA PAN" name="dsa_pan" id="dsa_pan">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">DSA GST No</label>
                            <input type="text" class="form-control" placeholder="Enter DSA GST no" name="dsa_gst_no" id="dsa_gst_no">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitInvoiceInput()">Submit</button>
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
@include('Frontend.Bank_MIS.index_js')
<script>
    // Store selected IDs globally to use in submit function
    let selectedMisIds = [];

    document.getElementById('generateInvoiceBtn').addEventListener('click', function() {
        console.log('Generate Invoice button clicked');
        selectedMisIds = $('.rowCheckbox:checked')
            .map(function() {
                return $(this).val();
            })
            .get();

        if (selectedMisIds.length === 0) {
            alert('Please select at least one case.');
            return;
        }

        $.ajax({
            url: '/invoice/generateInvoice',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                mis_ids: selectedMisIds
            },
            success: function(response) {
                if (response.success) {
                    let modalHTML = `
                        <div class="modal-header" style="height: 50px;">
                            <h5 class="modal-title">Generate Invoice - Case Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application No</th>
                                        <th>Bank Name</th>
                                        <th>Product Name</th>
                                        <th>Month</th>
                                        <th>Date</th>
                                        <th>Rate</th>
                                        <th>Group</th>
                                        <th>Customer Name</th>
                                        <th>Payout Amount</th>
                                        <th>Disburse Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    response.cases.forEach(function(caseItem) {
                        modalHTML += `
                            <tr>
                                <td>${caseItem.app_id}</td>
                                <td>${caseItem.bank_name}</td>
                                <td>${caseItem.product_name}</td>
                                <td>${caseItem.month}</td>
                                <td>${caseItem.month_year}</td>
                                <td>${caseItem.payout_rate}</td>
                                <td>${caseItem.group}</td>
                                <td>${caseItem.customer_name}</td>
                                <td>₹${parseFloat(caseItem.payoutAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td>₹${parseFloat(caseItem.disbAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    });

                    const totalPaymentAmount = response.totalPayoutAmount + (0.18 * response.totalPayoutAmount) - (0.02 * response.totalPayoutAmount);

                    modalHTML += `
                                </tbody>
                            </table>
                            <div class="alert alert-info mt-3">
                                <strong>Total Payment Amount (Total Payout Amount + 18% GST - TDS 2%): </strong>₹${parseFloat(totalPaymentAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="openInvoiceInputModal()">Generate</button>
                        </div>
                    `;

                    $('#invoiceModal .modal-content').html(modalHTML);
                    $('#invoiceModal').modal('show');
                } else {
                    alert(response.message || 'An error occurred.');
                }
            },
            error: function(xhr, status, error) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while generating the invoice.';
                alert(errorMessage);
                console.log('Error:', xhr.responseText);
            }
        });
    });

    function openInvoiceInputModal() {
        $('#invoiceModal').modal('hide');
        document.getElementById('invoiceInputForm').reset();
        $('#invoic_input_Modal').modal('show');
    }

    function submitInvoiceInput() {
        const form = document.getElementById('invoiceInputForm');
        
        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Collect form data
        const formData = {
            invoice_no: document.getElementById('invoice_no').value,
            invoice_date: document.getElementById('invoice_date').value,
            bank_gst_no: document.getElementById('bank_gst_no').value,
            bank_hsn_code: document.getElementById('bank_hsn_code').value,
            bank_address: document.getElementById('bank_address').value,
            dsa_pan: document.getElementById('dsa_pan').value,
            dsa_gst_no: document.getElementById('dsa_gst_no').value,
            mis_ids: selectedMisIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Send AJAX request to save invoice
        $.ajax({
            url: '/invoice/store',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Invoice saved successfully!');
                    form.reset();
                    form.classList.remove('was-validated');
                    $('#invoic_input_Modal').modal('hide');
                    
                    // Reload table or redirect
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                const errorMessage = xhr.responseJSON?.message || 'An error occurred while saving the invoice.';
                alert('Error: ' + errorMessage);
                console.log('Error:', xhr.responseText);
            }
        });
    }

    $('#invoic_input_Modal').on('hidden.bs.modal', function () {
        document.getElementById('invoiceInputForm').reset();
        document.getElementById('invoiceInputForm').classList.remove('was-validated');
    });
</script>
@endsection