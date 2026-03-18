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
            <li class="breadcrumb-item active" aria-current="page">Advance Report</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Advance Report</h3>
        <div class="btn-container">
            <a href="{{ route('report.advance.export', request()->query()) }}" class="application-header-btn">
                <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Export CSV
            </a>
        </div>
    </div>

    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-3 mb-2">
                <label class="bank-input-label">Partner</label>
                <select name="user_id" id="user_id" class="form-select select">
                        <option value="">All</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}{{ $user->Emp_Id ? ' (' . $user->Emp_Id . ')' : '' }}
                        </option>
                        @endforeach
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Advance Type</label>
                <select name="advance_type" id="advance_type" class="form-select">
                    <option value="">All</option>
                    <option value="add" {{ request('advance_type') === 'add' ? 'selected' : '' }}>Advance</option>
                    <option value="deduct" {{ request('advance_type') === 'deduct' ? 'selected' : '' }}>Recovery</option>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Min Amount</label>
                <input type="number" step="0.01" class="form-control" id="min_amount" name="min_amount" value="{{ request('min_amount') }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Max Amount</label>
                <input type="number" step="0.01" class="form-control" id="max_amount" name="max_amount" value="{{ request('max_amount') }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label class="bank-input-label">Company</label>
                <select class="form-select" id="company" name="company">
                    <option value="">All</option>
                    <option value="aadrika" {{ request('company') === 'aadrika' ? 'selected' : '' }}>AADRIKA</option>
                    <option value="parker" {{ request('company') === 'parker' ? 'selected' : '' }}>PARKER</option>
                    <option value="fss" {{ request('company') === 'fss' ? 'selected' : '' }}>FSS</option>
                </select>
            </div>
            <div class="col-lg-1 mb-2">
                <label class="bank-input-label">From</label>
                <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="col-lg-1 mb-2">
                <label class="bank-input-label">To</label>
                <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div class="col-lg-1 mt-4 mb-2 d-flex align-items-end">
                <button class="btn btn-primary w-100 me-1" type="button" id="filter">Filter</button>
            </div>
            <div class="col-lg-12 mt-2 d-flex justify-content-end">
                <button class="btn btn-secondary" type="button" id="refresh">Refresh</button>
            </div>
        </div>
    </div>

    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Report.advance.Table.table')
    </div>
</div>
@endsection

@section('script')
@include('Frontend.Report.advance.index_js')
@endsection
