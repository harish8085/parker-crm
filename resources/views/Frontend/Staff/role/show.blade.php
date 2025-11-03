@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection
@section('body')
<h2>View Role</h2>

<div class="bank-card">
        <div class="card-top-border">Basic Details</div>
        <div class="card-form">

                <div class="bank-detail-inputs channel">
                        <label class="bank-input-label" for="validationCustom01">Name<span class="required">*</span> </label>
                        <select class="bank-detail-input form-select" required name="bank_name" id="bank_name" disabled>
                                <option value="" selected disabled>Select Name</option>
                                <option value="{{$role->name}}" selected>{{$role->name}}</option>
                        </select>
                </div>
                <div class="bank-detail-inputs channel">
                        <label class="bank-input-label" for="validationCustom01">Status<span class="required">*</span> </label>
                        <select class="bank-detail-input form-select" required name="product_name" id="product_name" disabled>
                                <option value="" selected disabled>Select Status</option>
                                <option value="{{ $role->status }}" selected>
                                        @if($role->status == "1")
                                        Active
                                        @else
                                        Inactive
                                        @endif
                                </option>
                        </select>
                </div>

        </div>
</div>
<br>


@endsection

@section('script')
<script>

</script>
@endsection