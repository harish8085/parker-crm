@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/manage-user.css')}}">
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
        <h3 class="settlement-heading">Manage Sales Person</h3>
        <div class="user-btn-container">
            @if(auth()->user()->hasPermission('sales-person','create'))
            <a href="{{url('sales-person/create')}}">
                <button class="settlement-header-btn"><img class="application-header-icon" src="{{asset('assets/images/add-table-icon.svg')}}">Add</button>
            </a> @endif
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
                        @foreach($sales as $b)
                        <option>{{$b->first_name}} {{$b->last_name}}</option>
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
        @include('Frontend.Users.sales-person.Table.table')
    </div>
</div>
@endsection
@section('script')
@include('Frontend.Users.sales-person.index_js')
@endsection