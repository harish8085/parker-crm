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
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">All Application</h3>
        <div class="btn-container">
            @if(auth()->user()->hasPermission('application','create'))
            <a href="{{ url('/application/create') }}" style="text-decoration: none;">
                <button class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add
                </button>
            </a>
            @endif
            @if(auth()->user()->hasPermission('application','create'))

            <a href="{{ url('/application/create/upload') }}" style="text-decoration: none;">
                <button class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload
                </button>
            </a>
            @endif
        </div>
    </div>

    <!-- filter form -->
    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <div class="">
                    <label class="">Date Range</label>
                    <select class="bank-detail-input form-select select" required name="date" id="date">
                        <option value=""></option>
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
            @if($user->roles[0]->id == 1)
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label ">Partner Name</label>
                    <select class="bank-detail-input form-select select" required name="partner_name" id="partner_name">
                        <option value=""></option>
                        @foreach($users as $u)
                        <option value="{{$u->id}}">{{$u->first_name}} {{$u->last_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank Name</label>
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
                    <label class="bank-input-label">Product Name</label>
                    <select class="bank-detail-input form-select select" required name="product_name" id="product_name">
                        <option value="">Select Product Name</option>
                        @foreach($product as $p)
                        <option>{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label  ">Status</label>
                    <select class="bank-detail-input form-select select" required name="status" id="status">
                        <option value="">Select Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="rejected">Rejected</option>
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
                <button id="bulkDeleteBtn" class="btn btn-danger">Bulk Delete</button>
            </div>
        </div>
    </div>
    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Application.Table.application_table')
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
                                <option value="">Select Sales Person</option>
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
                                    <option value="">Select Channel Partner</option>
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
@endsection

@section('script')
@include('Frontend.Application.index_js')
@endsection