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
        <h3 class="application-heading">All Bank Code</h3>
        <div class="btn-container">
            @if(auth()->user()->hasPermission('dsa-code','create'))

            <button class="application-header-btn" data-bs-toggle="modal" data-bs-target="#createDSA">
                <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add Bank Code
            </button>
            @endif
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


    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.DSA.Table.dsa_table')

    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')
<div class="modal" id="createDSA">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Bank Code</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form action="{{url('dsa-code/create')}}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Bank<span class="required">*</span></label>
                            <select class="form-select" required name="bank_id" id="bank_id">
                                <option value="" selected disabled>Select Bank</option>
                                @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Group<span class="required">*</span></label>
                            <select class="form-select" required name="group" id="group">
                                <option value="" selected disabled>Select Group</option>
                                <option value="Secured">Secured</option>
                                <option value="Unsecured">Unsecured</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Product<span class="required">*</span></label>
                            <select class="form-select" required name="product_id" id="product_id">
                                <option value="" selected disabled>Select Product</option>
                            </select>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Code<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter dsa code" name="code" id="code">
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
<div class="modal" id="editDSAModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Bank Code</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">
                <form id="editDSAForm">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Bank<span class="required">*</span></label>
                            <select class="form-select" required name="bank_id" id="edit_bank_id">
                                <option value="" selected disabled>Select Bank</option>
                                @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Group<span class="required">*</span></label>
                            <select class="form-select" required name="group" id="edit_group">
                                <option value="" selected disabled>Select Group</option>
                                <option value="Secured">Secured</option>
                                <option value="Unsecured">Unsecured</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Select Product<span class="required">*</span></label>
                            <select class="form-select" required name="product_id" id="edit_product_id">
                                <option value="" selected disabled>Select Product</option>
                            </select>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Code<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter dsa code" name="code" id="edit_code">
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
@include('Frontend.DSA.index_js')
@endsection