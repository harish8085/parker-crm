@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Invoice Tracker Report</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Invoice Tracker</h3>
        <div class="btn-container">
            <a href="{{ route('report.invoice-tracker.export', request()->query()) }}" class="application-header-btn">
                <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Export CSV
            </a>
        </div>
    </div>

    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-3 mb-2">
                <label class="bank-input-label">Bank Name</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ request('bank_name') }}" placeholder="Enter bank name">
            </div>
            <div class="col-lg-3 mb-2">
                <label class="bank-input-label">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">MIS Month</label>
                <input type="month" class="form-control" id="mis_month" name="mis_month" value="{{ request('mis_month') }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">From</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-1 mb-2">
                <label class="bank-input-label">To</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-lg-1 mt-4 mb-2 d-flex align-items-end">
                <button class="btn btn-primary w-100" type="button" id="filter">Filter</button>
            </div>
            <div class="col-lg-12 mt-2 d-flex justify-content-end">
                <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
            </div>
        </div>
    </div>

    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Report.invoice-tracker.Table.table')
    </div>
</div>
@endsection

@section('script')
@include('Frontend.Report.invoice-tracker.index_js')
@endsection
