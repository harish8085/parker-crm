@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/commen.css') }}">
<style>
    .status-buttons {
        border: 0;
        border-radius: 14px;
        padding: 4px 10px;
        color: #fff;
        font-size: 12px;
        line-height: 16px;
    }

    .status-buttons.completed {
        background-color: #28a745;
    }

    .status-buttons.pending {
        background-color: #0d6efd;
    }
</style>
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Insurance MIS</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Insurance MIS Details</h3>
        <div class="btn-container">
            @php($roleId = auth()->user()->roles[0]->id ?? null)
            @if(auth()->user()->hasPermission('upload-mis','create') || auth()->user()->hasPermission('insurance','create') || in_array($roleId, [1,35,36]))
            <a href="{{ route('insurance.upload.view') }}" style="text-decoration: none;">
                <button class="application-header-btn add-bank-target-res1">
                    <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload Insurance MIS
                </button>
            </a>
            <a href="{{ route('insurance.upload.view') }}" style="text-decoration: none;">
                <button class="application-header-btn add-bank-target-res2">
                    <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload Insurance MIS
                </button>
            </a>
            @endif
        </div>
    </div>

    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Bank</label>
                    <select class="bank-detail-input form-select select" name="bank_id" id="bank_id">
                        <option value="">All Banks</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Channel Partner</label>
                    <select class="bank-detail-input form-select select" name="channel_id" id="channel_id">
                        <option value="">All Channel Partners</option>
                        @foreach($channels as $channel)
                        <option value="{{ $channel->id }}">{{ trim(($channel->first_name ?? '').' '.($channel->last_name ?? '')) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Disbursement From</label>
                    <input type="date" class="bank-detail-input form-control" id="from_date">
                </div>
            </div>
            <div class="col-lg-3 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label mb-2">Disbursement To</label>
                    <input type="date" class="bank-detail-input form-control" id="to_date">
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
        @include('Frontend.Insurance.Table.insurance_table')
    </div>
</div>
@endsection

@section('script')
@include('Frontend.Insurance.index_js')
@endsection
