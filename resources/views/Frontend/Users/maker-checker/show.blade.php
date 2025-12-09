@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>View Maker / Checker</h2>

<div class="bank-card">
    <div class="card-top-border">User Details</div>
    <div class="card-form">
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Emp ID</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->Emp_Id }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Name</label>
            <input class="bank-detail-input form-control" type="text" value="{{ trim($user->first_name.' '.$user->last_name) }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Email</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->email }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Phone Number</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->phone }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Role</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->user_type === 'checker' ? 'Checker' : 'Maker' }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Country</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->address_2 }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Address</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->address_1 }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">State</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->state }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">City</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->district }}" disabled>
        </div>
        <div class="bank-detail-inputs">
            <label class="bank-input-label">Pincode</label>
            <input class="bank-detail-input form-control" type="text" value="{{ $user->pincode }}" disabled>
        </div>
    </div>
</div>

<div class="save-btn-container">
    <a href="{{ url('maker-checker') }}" class="save-btn" style="text-decoration:none;display:inline-block;text-align:center;">Back to List</a>
</div>
@endsection


