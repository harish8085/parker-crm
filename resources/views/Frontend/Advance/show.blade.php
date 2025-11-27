@extends('Layout.app')
@php
use Illuminate\Support\Str;
@endphp

@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/add-service-1.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<style>
    .advance-detail-section {
        margin-bottom: 30px;
    }
    .logs-section {
        margin-top: 0;
    }
    .log-item {
        border-bottom: 1px solid #e0e0e0;
        padding: 15px 0;
    }
    .log-item:last-child {
        border-bottom: none;
    }
    .log-type-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .log-type-add {
        background-color: #d4edda;
        color: #155724;
    }
    .log-type-deduct {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('advance.index') }}">Advances</a></li>
            <li class="breadcrumb-item active" aria-current="page">View Advance Details</li>
        </ol>
    </nav>
</div>

<!-- Advance Details Section (Top) -->
<div class="bank-card advance-detail-section">
    <div class="card-top-border">Advance Details</div>
    <div class="p-4">
        <div class="row g-3">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Channel Partner</label>
                    <input class="bank-detail-input form-control" type="text" 
                        value="{{ $advance->user ? $advance->user->first_name . ' ' . $advance->user->last_name : '-' }}" 
                        disabled>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Email</label>
                    <input class="bank-detail-input form-control" type="text" 
                        value="{{ $advance->user && $advance->user->email ? $advance->user->email : '-' }}" 
                        disabled>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Advance Amount</label>
                    <input class="bank-detail-input form-control" type="text" 
                        value="{{ $advance->advance_amount ? '₹' . number_format($advance->advance_amount, 2) : '-' }}" 
                        disabled>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Advance Date</label>
                    <input class="bank-detail-input form-control" type="text" 
                        value="{{ $advance->advance_date ? date('d-m-Y', strtotime($advance->advance_date)) : '-' }}" 
                        disabled>
                </div>
            </div>
            <div class="col-lg-1 col-md-2 col-sm-4">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Status</label>
                    <div>
                        @if($advance->advance_status == 1)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            @if($advance->advance_remark)
            <div class="col-lg-1 col-md-2 col-sm-8">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Remark</label>
                    <input class="bank-detail-input form-control" type="text" 
                        value="{{ Str::limit($advance->advance_remark, 20) }}" 
                        title="{{ $advance->advance_remark }}"
                        disabled>
                </div>
            </div>
            @endif
        </div>
        @if($advance->advance_remark && strlen($advance->advance_remark) > 20)
        <div class="row mt-2">
            <div class="col-lg-12">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Full Remark</label>
                    <textarea class="bank-detail-input form-control" rows="2" disabled>{{ $advance->advance_remark }}</textarea>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Advance Logs Section (Bottom) -->
<div class="bank-card logs-section">
    <div class="card-top-border">Advance Logs</div>
    <!-- <div class="card-form"> -->
        <div class="table-responsive p-4">
            @include('Frontend.Advance.Table._logs_table')
        </div>
    <!-- </div> -->
</div>

<div class="save-btn-container mt-4">
    <a href="{{ route('advance.index') }}" class="btn btn-secondary">Back to List</a>
</div>
@endsection

@section('script')
@include('Frontend.Advance.show_js')
@endsection

