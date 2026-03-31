@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/commen.css') }}">

<style>
    .date_range {
        display: none;
        /* Hidden by default */
    }
    @php
        $restrictedRoles = ['channel', 'Associate_Channel'];
        $isRestricted = false;
        foreach ($restrictedRoles as $role) {
            if (Auth::user()->hasRole($role)) {
                $isRestricted = true;
                break;
            }
        }
    @endphp
    @if($isRestricted)
    .actions-header,
    .data-table tbody tr td:last-child {
        display: none !important;
    }
    @endif
</style>

@endsection
@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            
            <li class="breadcrumb-item active" aria-current="page">All banks</li>
        </ol>
    </nav>
</div>
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">All Bank</h3>
        <div class="btn-container">
            @php
                $restrictedRoles = ['channel', 'Associate_Channel'];
                $isRestricted = false;
                foreach ($restrictedRoles as $role) {
                    if (Auth::user()->hasRole($role)) {
                        $isRestricted = true;
                        break;
                    }
                }
            @endphp
            @if(!$isRestricted)
                        <button class="application-header-btn add-bank-target-res1" data-bs-toggle="modal" data-bs-target="#createBank">
                <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add Bank
            </button>
             <button class="application-header-btn add-bank-target-res2" data-bs-toggle="modal" data-bs-target="#createBank">
                <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">
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
                    <select class="bank-detail-input form-select select date-filter" required name="date" id="date">
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
            <div class="col-lg-12 mt-2">
                <div class="d-flex justify-content-end">
                    <button class="btn btn-primary me-2" type="submit" name="filter" id="filter">Filter</button>
                    <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
                </div>
            </div>
        </div>
    </div>


    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Bank.Table.bank_table')
    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')
<div class="modal" id="createBank">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Bank</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form action="{{url('bank/create')}}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank name" name="name" id="name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Short name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank short name" name="short_name" id="short_name" required>
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
<div class="modal" id="editBankModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Bank</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">
                <form id="editBankForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank name" name="name" id="editbankName" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Bank Short name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter bank short name" name="short_name" id="edit_short_name" required>
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
@include('Frontend.Bank.index_js')
@endsection