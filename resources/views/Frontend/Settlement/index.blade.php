@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
<style>
    .settlement-type-switch {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    .settlement-type-wrap {
        margin-bottom: 8px;
    }
    .settlement-type-caption {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 8px;
    }
    .settlement-type-tab {
        border: 1px solid #d0d7de;
        border-radius: 12px;
        padding: 12px 14px;
        background: #ffffff;
        color: #1f2937;
        text-decoration: none;
        transition: all 0.2s ease;
        display: block;
    }
    .settlement-type-tab:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .settlement-type-tab.active {
        border-color: #0ea5e9;
        background: #f0f9ff;
        box-shadow: inset 0 0 0 1px #bae6fd;
    }
    .settlement-type-title {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
    }
    .settlement-type-note {
        display: block;
        font-size: 12px;
        color: #475569;
        margin-top: 2px;
    }
    @media (max-width: 991px) {
        .settlement-type-switch {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
@section('body')

    @if(!auth()->user()->roles[0]->name == 'Checker' && !auth()->user()->roles[0]->name == 'Maker')
        <div class="card ">
            <div class="settlement-header">
                <h3 class="settlement-heading">Settlements</h3>
                <div class="settlement-btn-container">
                    <a href="{{ url('/settlement/create/upload') }}" style="text-decoration: none;">
                        <button class="settlement-header-btn">
                            <img class="application-header-icon" src="{{ asset('assets/images/import.svg') }}">Upload
                        </button>
                    </a>
                    @if(auth()->user()->roles[0]->id !=2 || auth()->user()->roles[0]->id !=3)
                    <a href="{{url('settlement/view/export-settlement')}}" style="text-decoration: none;">
                        <button class="settlement-header-btn">
                            <img class="application-header-icon" src="{{asset('assets/images/download.svg')}}">Download
                        </button>
                    </a>
                    @endif
                </div>
            </div>
    @endif

    <!-- Tabs -->
    <div class="p-4 pb-0">
        <div class="settlement-type-wrap">
            <div class="settlement-type-caption">Choose settlement page</div>
            <div class="settlement-type-switch">
                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'commission' ? 'active' : '' }}" data-type="commission" href="#" role="tab">
                    <span class="settlement-type-title">Commission</span>
                    <span class="settlement-type-note">Regular payout settlements</span>
                </a>
                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'contest' ? 'active' : '' }}" data-type="contest" href="#" role="tab">
                    <span class="settlement-type-title">Contest</span>
                    <span class="settlement-type-note">Contest payout settlements</span>
                </a>
                <a class="settlement-type-tab {{ ($settlementType ?? 'commission') === 'insurance' ? 'active' : '' }}" data-type="insurance" href="#" role="tab">
                    <span class="settlement-type-title">Insurance</span>
                    <span class="settlement-type-note">Insurance payout settlements</span>
                </a>
            </div>
        </div>
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'pending') === 'pending' ? 'active' : '' }} settlement-tab" data-tab="pending" href="#" role="tab">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ ($tab ?? 'pending') === 'completed' ? 'active' : '' }} settlement-tab" data-tab="completed" href="#" role="tab">Completed</a>
            </li>
        </ul>
    </div>

    <!-- filter form -->
    <div class="bank-card p-4">
        <div class="row">
            <div class="col-lg-4 mb-2">
                <div class="bank-detail-inputs">
                    <label class="bank-input-label">Partner Name</label>
                    <select class="bank-detail-input form-select select" required name="partner_name" id="partner_name">
                        <option value=""></option>
                        @foreach($settlements as $p)
                        <option>{{$p->first_name}}</option>
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

    <div class="table-responsive p-4" id="myTable">
        @include('Frontend.Settlement.Table.settlement_table', compact('tdsPercentage'))
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
@include('Frontend.Settlement.index_js')
@endsection
