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
<div class="modal" id="editPaymentModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Invoice Payment</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <div class="modal-body" style="padding: 20px 25px;">
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

                    <!-- Use Remaining Amount Checkbox -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="useRemainingCheckbox">
                                <label class="form-check-label" for="useRemainingCheckbox">
                                    Use remaining amount as payment paid
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Paid -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Payment Paid <span class="required">*</span></label>
                            <input type="number" class="form-control" id="paymentPaid" name="payment_paid" step="0.01" min="0" required>
                        </div>
                    </div>
                    <!-- Reference No 1 -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Reference No 1 <span class="required">*</span></label>
                            <input type="text" class="form-control" id="referanceNo1" name="referance_no1" required>
                        </div>
                    </div>
                    <!-- Reference No 2 -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Reference No 2</label>
                            <input type="text" class="form-control" id="referanceNo2" name="referance_no2">
                        </div>
                    </div>

                    <!-- Payment Date 1 -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Payment Date 1 <span class="required">*</span></label>
                            <input type="date" class="form-control" id="paymentDate1" name="payment_date1" required>
                        </div>
                    </div>

                    <!-- Payment Date 2 -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="input-label">Payment Date 2</label>
                            <input type="date" class="form-control" id="paymentDate2" name="payment_date2">
                        </div>
                    </div>
                    <div class="save-btn-container">
                        <button type="submit" class="save-btn">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- aplication case list modal -->
<div class="modal fade" id="invoiceCasesModal" tabindex="-1" aria-labelledby="invoiceCasesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- Content will be loaded here via AJAX -->
        </div>
    </div>
 

@endsection


@section('script')
@include('Frontend.InvoicePayment.index_js')
<script>
    function viewInvoiceCasesList(id) {
        $.ajax({
            url: '/invoice_payment/view/' + id,
            type: 'GET',
            success: function(response) {
                $('#invoiceCasesModal .modal-content').html(response);
                $('#invoiceCasesModal').modal('show');

            },
            error: function(xhr) {
                alert('An error occurred while fetching invoice cases.');
            }
        });
    }
    
</script>
@endsection