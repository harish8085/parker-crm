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
    
    /* Toggle Switch Styles */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
        cursor: pointer;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: #4CAF50;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }

    .toggle-switch input:focus + .toggle-slider {
        box-shadow: 0 0 1px #4CAF50;
    }
    
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .form-control.is-invalid {
        border-color: #dc3545;
        padding-right: calc(1.5em + 0.75rem);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3cpath d='m4.5 4.5 3 3m0-3-3 3'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
    }
</style>
@endsection
@section('body')
<div class="card">
    <div class="application-header">
        <h3 class="application-heading">All Announcement Categories</h3>
        <div class="btn-container">
            <button class="application-header-btn" data-bs-toggle="modal" data-bs-target="#createCategory">
                <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add Category
            </button>
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
                    <label class="bank-input-label">Category Name</label>
                    <input type="text" class="form-control" placeholder="Enter category name" name="name" id="name">
                </div>
            </div>
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Status</label>
                    <select class="bank-detail-input form-select select" name="is_active" id="is_active">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
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
        @include('Frontend.AnnouncementCategory.Table.category_table')
    </div>
</div>
<!-- /# row -->
@endsection

@section('modal')
<div class="modal" id="createCategory">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Add Announcement Category</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{ asset('assets/images/cancel-icon.svg') }}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 25px;">
                <form id="createCategoryForm" action="{{url('announcement-categories/create')}}" method="POST">
                    @csrf
                    <div id="createCategoryErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Category Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter category name" name="name" id="category_name" required>
                            <div class="invalid-feedback" id="category_name_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active_create" value="1" checked>
                                <label class="form-check-label" for="is_active_create">
                                    Active
                                </label>
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
<div class="modal" id="editCategoryModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header" style="padding: 2px 15px;">
                <h5 class="modal-title">Edit Announcement Category</h5>
                <button type="button" class="btn custom-close-btn" data-bs-dismiss="modal">
                    <img src="{{asset('assets/images/cancel-icon.svg')}}" alt="Cancel">
                </button>
            </div>
            <!-- Modal body -->
            <div class="modal-body" style="padding: 20px 20px;">
                <form id="editCategoryForm">
                    @csrf
                    @method('POST')
                    <div id="editCategoryErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <label class="input-label">Category Name<span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter category name" name="name" id="edit_category_name" required>
                            <div class="invalid-feedback" id="edit_category_name_error"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 p-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">
                                    Active
                                </label>
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
@include('Frontend.AnnouncementCategory.index_js')
@endsection

