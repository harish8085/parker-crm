@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/roles.css')}}">
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
        <h3 class="settlement-heading">Role</h3>
        <div class="user-btn-container">

            @if(auth()->user()->hasPermission('staff','create'))
            <button class="settlement-header-btn" data-bs-toggle="modal" data-bs-target="#myModal"><img class="application-header-icon" src="{{asset('assets/images/add-table-icon.svg')}}">Add</button>
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
                    <label class="bank-input-label">Name</label>
                    <select class="bank-detail-input form-select select" required name="name" id="name">
                        <option value="">Select Name</option>
                        @foreach($roles as $b)
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
        @include('Frontend.Staff.role.Table.table')
    </div>
</div>
@endsection
@section('modal')
<div class="modal" id="myModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Role</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">

                <form action="{{url('staff/create/role')}}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 p-2">
                            <h5 style="margin: 0; color: black;">Fill Details</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Role Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Role Name" name="name" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Status<span class="required">*</span></label>
                            <div class="dropdown bar">
                                <select class="form-select" required name="status">
                                    <option value="true">Active</option>
                                    <option value="false">In-Active</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="save-btn-container">
                        <button class="save-btn" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="editRoleModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Role</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">

                <form id="editRoleForm">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-12 p-2">
                            <h5 style="margin: 0; color: black;">Fill Details</h5>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Role Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Role Name" name="name" id="editRoleName" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Status<span class="required">*</span></label>
                            <div class="dropdown bar">
                                <select class="form-select" required name="status" id="editStatus">
                                    <option value="1">Active</option>
                                    <option value="0">In-Active</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="save-btn-container">
                        <button class="save-btn" type="submit">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="viewRoleModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">View Role</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>

            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">



                <div class="row">
                    <div class="col-12 p-2">
                        <label class="input-label">Role Name<span class="required">*</span></label>
                        <input type="text" class="form-control" placeholder="Enter Role Name" name="name" id="viewRoleName" disabled>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 p-2">
                        <label class="input-label">Status<span class="required">*</span></label>
                        <div class="dropdown bar">
                            <select class="form-select" disabled name="status" id="viewStatus">
                                <option value="1">Active</option>
                                <option value="0">In-Active</option>
                            </select>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
@include('Frontend.Staff.role.index_js')
@endsection