@extends('Layout.app')

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<style>
    .date_range {
        display: none;
    }
</style>
@endsection

@section('body')
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">All Announcements</h3>
        @if(auth()->user()->hasPermission('announcements','create'))
        <div class="btn-container">
            <a href="{{ url('/announcements/create') }}">
                <button class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add Announcement
                </button>
            </a>
        </div>
        @endif
    </div>

    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Date Range</label>
                    <select class="bank-detail-input form-select select" name="date" id="date">
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
                    <label class="bank-input-label">Date Range</label>
                    <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" />
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Title</label>
                    <input type="text" class="form-control" name="title" id="title" placeholder="Enter title">
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Category</label>
                    <select class="bank-detail-input form-select select" name="announcement_category_id" id="announcement_category_id">
                        <option value="" selected disabled>Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank</label>
                    <select class="bank-detail-input form-select select" name="bank_id" id="bank_id">
                        <option value="" selected disabled>Select Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-2 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank Product</label>
                    <select class="bank-detail-input form-select select" name="product_id" id="product_id">
                        <option value="" selected disabled>Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}{{ $product->group ? ' ('.$product->group.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-12 mt-2">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary me-2" type="button" id="filter">Filter</button>
                    <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Announcement.Table.announcement_table')
    </div>
</div>
@endsection

@section('script')
@include('Frontend.Announcement.index_js')
@endsection
