@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">

<style>
    .date_range {
        display: none;
        /* Hidden by default */
    }
</style>

@endsection
@section('body')


<div class="card ">
    <div class="settlement-header">
        <h3 class="settlement-heading">All Sheet Data</h3>
        <div class="settlement-btn-container">

            <a class="settlement-header-btn no-style" href="{{ route('add-sheet-data') }}">
                <i class="fa fa-plus me-1"></i> Add Sheet Data
            </a>

        </div>
    </div>

    <!-- filter form -->
    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Date Range</label>
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
                    <label class="bank-input-label">Date Range</label>
                    <input type="text" class="form-control date-range-picker" id="date-range-picker" name="date_range" />
                </div>

            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Bank Name</label>
                    <select class="bank-detail-input form-select select" required name="bank_name" id="bank_name">
                        <option value="">Select Bank Name</option>
                        @foreach($banks as $b)
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
                        @foreach($products as $p)
                        <option>{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Group</label>
                    <select class="bank-detail-input form-select select" required name="group" id="group">
                        <option value="">Select Group</option>
                        <option value="Secured">Secured</option>
                        <option value="Unsecured">Unsecured</option>
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


    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.SheetMatching.Table.table')
    </div>
</div>
@endsection
@section('modal')
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Filter</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form>
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
                        <button class="save-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@include('Frontend.SheetMatching.index_js')
@endsection