@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service.css')}}">
@endsection
@section('body')
<div>
    <div class="bank-card">
        <div class="card-top-border">Add Service Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Service Name</label>
                <input class="bank-detail-input" id="service_name" type="text" placeholder="Enter service name" />
            </div>

        </div>
    </div>
    <div id="data">
        @include('Frontend.Setting.Service.component.product')
    </div>
</div>
@endsection