@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{ asset('assets/css/application.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/custom-table.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/settlement.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/paginate.css') }}">
<style>
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

    /* Center bootbox confirmation modal */
    .bootbox.modal {
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    .bootbox.modal .modal-dialog {
        margin: 0 1rem;
        width: 100%;
        max-width: 500px;
    }
</style>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.3/bootbox.min.js"></script>
    @include('Frontend.Users.maker-checker.index_js')
@endsection


