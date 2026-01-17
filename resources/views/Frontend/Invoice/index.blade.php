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
<!-- Invoice Input Modal (appears first) -->
<div class="modal fade" id="invoic_input_Modal" data-bs-backdrop="static" data-bs-keyboard="false" style="width:100%;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="margin-top: 200px;">
            <div class="modal-header" style="height: 50px;">
                <h5 class="modal-title">Invoice Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding: 20px 25px; max-height: 500px; overflow-y: auto;">
                <form id="invoiceInputForm">
                    <div class="row">
                        <div class="col-12 p-2">
                            <div class="">
                                <label class="input-label">Company Name <span class="required">*</span></label>
                                <select class="form-select" name="company_name" id="company_name" required onchange="updateGSTNO_paymentBank()">
                                    <option value="">Select Option</option>
                                    <option value="Parker's Consulting & Ventures Pvt. Ltd.">Parker's Consulting & Ventures Pvt. Ltd.</option>
                                    <option value="Aadrika Informative Services Pvt. LTD">Aadrika Informative Services Pvt. LTD</option>
                                    <option value="Finance Solution Services">Finance Solution</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Company GST No</label>
                            <input type="text" class="form-control" placeholder="Enter DSA GST no" name="dsa_gst_no" id="dsa_gst_no">
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
                            <label class="input-label">Bank Address</label>
                            <textarea class="form-control" placeholder="Enter bank address" name="bank_address" id="bank_address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Invoice No <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter invoice no (15-16 digits)" name="invoice_no" id="invoice_no" pattern="\d{15,16}" maxlength="16" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Invoice Date <span class="required">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" id="invoice_date" required>
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
                            <label class="input-label">Taxable Value <span class="required">*</span></label>
                            <input type="number" class="form-control" placeholder="Enter taxable value" name="taxable_value" id="taxable_value" step="0.01" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">In-State <span class="required">*</span></label>
                            <select class="form-select" name="in_state" id="in_state" required onchange="updateGSTFields()">
                                <option value="">Select Option</option>
                                <option value="yes">Yes (CGST + SGST)</option>
                                <option value="no">No (IGST)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Invoice Value <span class="required">*</span></label>
                            <input type="number" class="form-control" placeholder="Enter invoice value" name="invoive_value" id="invoive_value" step="0.01" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Payment Received Bank <span class="required">*</span></label>
                            <select class="form-select" name="payment_recevied_bank" id="payment_recevied_bank" required>
                                    <option value="">Select Option</option>
                                </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="validateAndShowListingModal()">Next</button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Listing Modal (appears second) -->
<div class="modal fade" id="invoiceModal" data-bs-backdrop="static" data-bs-keyboard="false" style="margin-top: 200px; width:100%;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>

@endsection

@section('script')
@include('Frontend.Bank_MIS.index_js')
<script>
    // Store selected IDs and form data globally
    let selectedMisIds = [];
    let invoiceFormData = {};
    let currentResponse = {}; // Add this to store the response globally

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

        // Open the invoice input form modal first
        document.getElementById('invoiceInputForm').reset();
        $('#invoic_input_Modal').modal('show');
    });

    function updateGSTFields() {
        const inState = document.getElementById('in_state').value;
        // This function can be used to update UI based on GST type selection
        console.log('GST Type Selected:', inState);
    }

    function updateGSTNO_paymentBank() {
        const companyName = document.getElementById('company_name').value;
        const gstNoField = document.getElementById('dsa_gst_no');

        const gstNumbers = {
            "Parker's Consulting & Ventures Pvt. Ltd.": '23AALCP8380J1ZY',
            "Aadrika Informative Services Pvt. LTD": '23AAOCA6070B1Z0',
            "Finance Solution Services": '23BQCPS2686D2ZU'
        };

        gstNoField.value = gstNumbers[companyName] || '';

        // in parker we have icici , kotak,idfc , hdfc,AU
        // IN aadrika we have sbi , axis , yes bank, ICICI
        // IN finance solution we have hdfc

        const paymentBankField = document.getElementById('payment_recevied_bank');
        paymentBankField.innerHTML = '<option value="">Select Option</option>'; // Reset options
        let paymentbanks = [];
        if (companyName === "Parker's Consulting & Ventures Pvt. Ltd.") {
            paymentbanks = ['ICICI Bank', 'Kotak Mahindra Bank', 'IDFC First Bank', 'HDFC Bank', 'AU Small Finance Bank'];
        } else if (companyName === 'Aadrika Informative Services Pvt. LTD') {
            paymentbanks = ['State Bank of India', 'Axis Bank', 'Yes Bank', 'ICICI Bank'];
        } else if (companyName === 'Finance Solution Services') {
            paymentbanks = ['HDFC Bank'];
        }
        paymentbanks.forEach(function(bank) {
            const option = document.createElement('option');
            option.value = bank;
            option.text = bank;
            paymentBankField.appendChild(option);
        });


        
    }

    function validateAndShowListingModal() {
        const form = document.getElementById('invoiceInputForm');

        if (form.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
            form.classList.add('was-validated');
            return;
        }

        // Collect form data
        invoiceFormData = {
            company_name: document.getElementById('company_name').value,
            invoice_no: document.getElementById('invoice_no').value,
            invoice_date: document.getElementById('invoice_date').value,
            bank_gst_no: document.getElementById('bank_gst_no').value,
            bank_hsn_code: document.getElementById('bank_hsn_code').value,
            bank_address: document.getElementById('bank_address').value,
            in_state: document.getElementById('in_state').value,
            taxable_value: parseFloat(document.getElementById('taxable_value').value),
            invoive_value: parseFloat(document.getElementById('invoive_value').value),
            payment_recevied_bank: document.getElementById('payment_recevied_bank').value,
            dsa_pan: document.getElementById('dsa_pan').value,
            dsa_gst_no: document.getElementById('dsa_gst_no').value
        };

        // Close input modal and show listing modal
        $('#invoic_input_Modal').modal('hide');
        showInvoiceListingModal();
    }

    function showInvoiceListingModal() {
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
                    // Store response globally
                    currentResponse = response;

                    // Calculate GST and TDS based on in_state selection
                    const totalPayoutAmount = response.totalPayoutAmount;
                    let cgst = 0,
                        sgst = 0,
                        igst = 0;

                    if (invoiceFormData.in_state === 'yes') {
                        // In-state: CGST 9% + SGST 9%
                        cgst = totalPayoutAmount * 0.09;
                        sgst = totalPayoutAmount * 0.09;
                    } else {
                        // Out-of-state: IGST 18%
                        igst = totalPayoutAmount * 0.18;
                    }

                    // Calculate final payment amount with TDS deduction (2%)
                    const gstAmount = cgst + sgst + igst;
                    const tdsAmount = totalPayoutAmount * 0.02;
                    const finalPaymentAmount = totalPayoutAmount + gstAmount - tdsAmount;

                    let modalHTML = `
                        <div class="modal-header" style="height: 50px;">
                            <h5 class="modal-title">Generate Invoice - Case Details</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                            <div class="alert alert-primary">
                                <strong>Invoice No:</strong> ${invoiceFormData.invoice_no} | 
                                <strong>Date:</strong> ${invoiceFormData.invoice_date}
                            </div>
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

                    modalHTML += `
                                </tbody>
                            </table>
                            <div class="alert alert-info mt-3">
                                <strong>Payout Amount:</strong> ₹${parseFloat(totalPayoutAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}<br>
                    `;

                    if (invoiceFormData.in_state === 'yes') {
                        modalHTML += `
                                <strong>CGST (9%):</strong> ₹${parseFloat(cgst).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}<br>
                                <strong>SGST (9%):</strong> ₹${parseFloat(sgst).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}<br>
                        `;
                    } else {
                        modalHTML += `
                                <strong>IGST (18%):</strong> ₹${parseFloat(igst).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}<br>
                        `;
                    }

                    modalHTML += `
                                <strong>TDS (2%):</strong> -₹${parseFloat(tdsAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}<br>
                                <strong style="color: green; font-size: 18px;">Total Payment Amount:</strong> ₹${parseFloat(finalPaymentAmount).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Back</button>
                            <button type="button" class="btn btn-primary" onclick="submitInvoiceData()">Submit</button>
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
    }

    function submitInvoiceData() {
        // Use the globally stored response.totalPayoutAmount
        const totalPayoutAmount = parseFloat(currentResponse.totalPayoutAmount);
        let cgst = 0,
            sgst = 0,
            igst = 0;

        if (invoiceFormData.in_state === 'yes') {
            cgst = totalPayoutAmount * 0.09;
            sgst = totalPayoutAmount * 0.09;
        } else {
            igst = totalPayoutAmount * 0.18;
        }

        const tdsAmount = totalPayoutAmount * 0.02;
        const finalPaymentAmount = totalPayoutAmount + (cgst + sgst + igst) - tdsAmount;

        const submitData = {
            ...invoiceFormData,
            cgst: cgst.toFixed(2),
            sgst: sgst.toFixed(2),
            igst: igst.toFixed(2),
            payment_amount: finalPaymentAmount.toFixed(2),
            mis_ids: selectedMisIds,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        $.ajax({
            url: '/invoice/store',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: submitData,
            success: function(response) {
                if (response.success) {
                    alert('Invoice saved successfully!');
                    $('#invoiceModal').modal('hide');
                    document.getElementById('invoiceInputForm').reset();

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

    $('#invoic_input_Modal').on('hidden.bs.modal', function() {
        document.getElementById('invoiceInputForm').reset();
        document.getElementById('invoiceInputForm').classList.remove('was-validated');
    });
</script>
@endsection