@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/settlement.css')}}">
<link rel="stylesheet" href="{{asset('assets/css/custom-table.css')}}">
@endsection
@section('body')


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
        @include('Frontend.Settlement.Table.settlement_table')
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