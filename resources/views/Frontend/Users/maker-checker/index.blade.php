@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
@endsection

@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Maker / Checker</li>
        </ol>
    </nav>
</div>

<div class="card">
    <div class="application-header">
        <h3 class="application-heading">Maker / Checker</h3>
        <div class="btn-container">
            @if(auth()->user()->roles[0]->id == 1)
                <a href="{{ url('maker-checker/create') }}" class="application-header-btn">
                    <img class="application-header-icon" src="{{ asset('assets/images/add-table-icon.svg') }}">Add Maker / Checker
                </a>
            @endif
        </div>
    </div>

    <div class="table-responsive p-4" id="dataTable">
        @include('Frontend.Users.maker-checker.Table.table')
    </div>
</div>
@endsection

@section('script')
    @include('Frontend.Users.maker-checker.index_js')
@endsection


