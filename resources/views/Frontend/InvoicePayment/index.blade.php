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
                    <label class="bank-input-label mb-2">Payment Status</label>
                    <select class="bank-detail-input form-select select" required name="payment_status" id="payment_status">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="paid">Received</option>
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

    <!-- <div class="bank-card" id="actionButton">
        <div class="row">
            <div class="col-lg-4 ml-3 mb-2">
                <button id="generateInvoiceBtn" class="btn btn-success generatInvoice">Generate Invoice</button>
            </div>
        </div>
    </div> -->
    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.InvoicePayment.Table.invoice_payment_table')
    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')

<!-- Edit Invoice Payment Modal -->
<div class="modal" id="editPaymentModal"  data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Invoice Payment</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <div class="modal-body" style="padding: 20px 25px; max-height: 500px; overflow-y: auto;">
                <form id="editPaymentForm">
                    @csrf
                    <input type="hidden" id="paymentId" name="payment_id">

                    <!-- Remaining Amount Display -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Remaining Amount</label>
                            <input type="text" class="form-control" id="remainingAmountDisplay" readonly style="background-color: #f5f5f5;">
                        </div>
                    </div>
                    <!-- <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useRemainingCheckbox">
                                <label class="form-check-label" for="useRemainingCheckbox">
                                    Use remaining amount as payment paid
                                </label>
                            </div>
                        </div>
                    </div> -->

                    <!-- Payment Paid -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="input-label">Payment Received 1<span class="required">*</span></label>
                            <input type="number" class="form-control" id="paymentPaid1" name="payment_paid1" step="0.01" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="input-label">Payment Received 2</label>
                            <input type="number" class="form-control" id="paymentPaid2" name="payment_paid2" step="0.01" min="0">
                        </div>
                    </div>
                    <!-- Reference No 1 -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="input-label">UTR No 1 <span class="required">*</span></label>
                            <input type="text" class="form-control" id="referanceNo1" name="referance_no1" required>
                        </div>
                        <div class="col-6">
                            <label class="input-label">UTR No 2</label>
                            <input type="text" class="form-control" id="referanceNo2" name="referance_no2">
                        </div>
                    </div>

                    <!-- Payment Date 1 -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="input-label">Payment Date 1 <span class="required">*</span></label>
                            <input type="date" class="form-control" id="paymentDate1" name="payment_date1" required>
                        </div>
                        <div class="col-6">
                            <label class="input-label">Payment Date 2</label>
                            <input type="date" class="form-control" id="paymentDate2" name="payment_date2">
                        </div>
                    </div>

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
                        <div class="col-6 p-2">
                            <label class="input-label">Company GST No<span class="required">*</span></label>
                            <select class="form-select" name="dsa_gst_no" id="dsa_gst_no" required>
                            </select>
                        </div>
                        <div class="col-6 p-2">
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
                            <input type="text" class="form-control" placeholder="Enter invoice no" name="invoice_no" id="invoice_no" maxlength="17" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6 p-2">
                            <label class="input-label">Invoice Date <span class="required">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" id="invoice_date" required>
                        </div>
                        <div class="col-6 p-2">
                            <label class="input-label">Bank HSN Code <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank HSN code" name="bank_hsn_code" id="bank_hsn_code" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Payment Received Bank <span class="required">*</span></label>
                            <select class="form-select" name="payment_received_bank" id="payment_received_bank" required>
                            </select>
                        </div>
                    </div>

                    <div class="save-btn-container">
                        <button type="submit" class="save-btn">Update Payment</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- aplication case list modal -->
<!-- Invoice Listing Modal (appears second) -->
<div class="modal fade" id="invoiceCasesModal" data-bs-backdrop="static" data-bs-keyboard="false" style="margin-top: 200px; width:100%;">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <!-- Content will be inserted here -->
        </div>
    </div>
</div>


    @endsection

    @section('script')
    @include('Frontend.InvoicePayment.index_js')
    <script>


        function updateGSTFields() {
            const inState = document.getElementById('in_state').value;
            // This function can be used to update UI based on GST type selection
        }

        function updateGSTNO_paymentBank() {
            const companyName = document.getElementById('company_name').value;
            const gstNoField = document.getElementById('dsa_gst_no');
            let gstNoFieldNo = [];
            gstNoField.innerHTML = '<option value="">Select Option</option>'; // Reset options

            if (companyName === "Parker's Consulting & Ventures Pvt. Ltd.") {
                gstNoFieldNo = ["23AALCP8380J1ZY", "09AALCP8380J1ZO", "27AALCP8380J1ZQ"];
            } else if (companyName === "Aadrika Informative Services Pvt. LTD") {
                gstNoFieldNo = ["23AAOCA6070B1Z0", "07AAOCA6070B1ZU", "04AAOCA6070B1Z0"];
            } else if (companyName === "Finance Solution Services") {
                gstNoFieldNo = ["23BQCPS2686D2ZU"];
            }
            gstNoFieldNo.forEach(function(gstNo) {
                const option = document.createElement('option');
                option.value = gstNo;
                option.text = gstNo;
                gstNoField.appendChild(option);
            });


            // in parker we have icici , kotak,idfc , hdfc,AU
            // IN aadrika we have sbi , axis , yes bank, ICICI
            // IN finance solution we have hdfc

            const paymentBankField = document.getElementById('payment_received_bank');
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
                payment_received_bank: document.getElementById('payment_received_bank').value,
                dsa_gst_no: document.getElementById('dsa_gst_no').value

            };

            if (payment_paid2) {
                invoiceFormData.payment_paid2 = document.getElementById('payment_date2').value;
                invoiceFormData.referance_no2 = document.getElementById('referanceNo2').value;
            }
            if (payment_paid2) {
                if (!invoiceFormData.payment_paid2 || !invoiceFormData.referance_no2) {
                    alert('Please fill Payment Received 2 and Reference No 2 fields');
                    return;
                }
            }



            // Close input modal and show listing modal
            $('#invoic_input_Modal').modal('hide');
        }
    </script>
    @endsection