@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection

@section('body')
<h2>Edit Maker / Checker</h2>
<form class="needs-validation" action="{{url('maker-checker/update/'.$user->id)}}" method="POST" novalidate>
    @csrf

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bank-card">
        <div class="card-top-border">User Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Name <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="name" id="name" placeholder="Enter full name"
                       value="{{ old('name', trim($user->first_name.' '.$user->last_name)) }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Email <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="email" name="email" id="email" placeholder="Enter email"
                       value="{{ old('email', $user->email) }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Phone Number <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="tel" maxlength="10" name="phone" id="phone" placeholder="Enter phone number"
                       value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Role <span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="role" id="role">
                    <option value="" disabled>Select Role</option>
                    @foreach($roles as $key => $label)
                        <option value="{{$key}}" {{ old('role', $user->user_type) === $key ? 'selected' : '' }}>{{$label}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <br>

    <div class="bank-card">
        <div class="card-top-border">Address Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Country</label>
                <input class="bank-detail-input form-control" type="text" name="country" id="country" placeholder="Enter country"
                       value="{{ old('country', $user->address_2) }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Address <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="address_1" id="address_1" placeholder="Enter address"
                       value="{{ old('address_1', $user->address_1) }}">
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">State <span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="state" id="state">
                    <option value="" disabled {{ old('state', $user->state) ? '' : 'selected' }}>Select State</option>
                    @foreach($states as $state)
                        <option value="{{$state['state_code']}}" {{ old('state', $user->state) === $state['state_code'] ? 'selected' : '' }}>{{$state['state']}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">City <span class="required">*</span></label>
                <select class="bank-detail-input form-select" required name="city" id="district">
                    <option value="" disabled>Select City</option>
                    @if(old('city', $user->district))
                        <option value="{{ old('city', $user->district) }}" selected>{{ old('city', $user->district) }}</option>
                    @endif
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Pincode <span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="number" name="pincode" id="pincode" placeholder="Enter pincode"
                       value="{{ old('pincode', $user->pincode) }}">
            </div>
        </div>
    </div>

    <div class="save-btn-container">
        <button class="save-btn" id="submitBtn">Save</button>
    </div>
</form>
@endsection

@section('script')
<script>
    (function () {
        'use strict';

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        document.getElementById('submitBtn').addEventListener('click', function (event) {
            event.preventDefault();

            let isValid = true;

            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            const roleInput = document.getElementById('role');
            const addressInput = document.getElementById('address_1');
            const stateInput = document.getElementById('state');
            const cityInput = document.getElementById('district');
            const pincodeInput = document.getElementById('pincode');

            if (!nameInput.value.trim()) {
                nameInput.classList.add('is-invalid');
                nameInput.focus();
                isValid = false;
                return;
            } else {
                nameInput.classList.remove('is-invalid');
            }

            if (!emailInput.value.trim() || !emailRegex.test(emailInput.value.trim())) {
                emailInput.classList.add('is-invalid');
                emailInput.focus();
                isValid = false;
                return;
            } else {
                emailInput.classList.remove('is-invalid');
            }

            if (!phoneInput.value.trim() || phoneInput.value.trim().length !== 10) {
                phoneInput.classList.add('is-invalid');
                phoneInput.focus();
                isValid = false;
                return;
            } else {
                phoneInput.classList.remove('is-invalid');
            }

            if (!roleInput.value) {
                roleInput.classList.add('is-invalid');
                roleInput.focus();
                isValid = false;
                return;
            } else {
                roleInput.classList.remove('is-invalid');
            }

            if (!addressInput.value.trim()) {
                addressInput.classList.add('is-invalid');
                addressInput.focus();
                isValid = false;
                return;
            } else {
                addressInput.classList.remove('is-invalid');
            }

            if (!stateInput.value) {
                stateInput.classList.add('is-invalid');
                stateInput.focus();
                isValid = false;
                return;
            } else {
                stateInput.classList.remove('is-invalid');
            }

            if (!cityInput.value) {
                cityInput.classList.add('is-invalid');
                cityInput.focus();
                isValid = false;
                return;
            } else {
                cityInput.classList.remove('is-invalid');
            }

            if (!pincodeInput.value.trim() || pincodeInput.value.trim().length !== 6) {
                pincodeInput.classList.add('is-invalid');
                pincodeInput.focus();
                isValid = false;
                return;
            } else {
                pincodeInput.classList.remove('is-invalid');
            }

            if (isValid) {
                document.querySelector('.needs-validation').submit();
            }
        });
        
        const stateSelect = document.getElementById('state');
        const citySelect = document.getElementById('district');

        stateSelect.addEventListener('change', function () {
            const stateCode = this.value;
            if (!stateCode) {
                citySelect.innerHTML = '<option value="" selected disabled>Select City</option>';
                return;
            }

            fetch('/getDistrict/' + stateCode, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.text())
                .then(html => {
                    citySelect.innerHTML = '<option value="" selected disabled>Select City</option>' + html;
                })
                .catch(error => console.error(error));
        });

    })();
</script>
@endsection


