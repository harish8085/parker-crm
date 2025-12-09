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
        <h3 class="application-heading">Announcement Logs - {{ $announcement->title }}</h3>
        <div class="btn-container">
            <a href="{{ route('announcements.index') }}">
                <button class="application-header-btn">
                    <i class="fas fa-arrow-left"></i> Back to Announcements
                </button>
            </a>
        </div>
    </div>

    <div class="bank-card p-4">
        <div class="row">
            
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">User Name / Email</label>
                    <input type="text" class="form-control" name="user_name" id="user_name" placeholder="Enter user name or email">
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
        @include('Frontend.Announcement.Table.logs_table')
    </div>
</div>
@endsection

@section('script')
@include('Frontend.Announcement.logs_js')
@endsection

