@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .image-preview-container {
        margin-top: 15px;
        position: relative;
        display: inline-block;
    }
    .image-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background: #f9f9f9;
        display: block;
    }
    .remove-image-btn {
        position: absolute;
        top: -10px;
        right: -10px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .remove-image-btn:hover {
        background: #c82333;
    }
    .file-input-wrapper {
        position: relative;
    }
</style>
@endsection
@section('body')
<div class="breadcrumb-container" style="margin-bottom: 24px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-white px-0 py-2" style="margin-bottom:0;">
            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('link-bank.index') }}">Link Banks</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add Bank Account</li>
        </ol>
    </nav>
</div>

<form class="needs-validation" action="{{ url('/link-bank/create') }}" method="POST" enctype="multipart/form-data" novalidate>
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
        <div class="card-top-border">Bank Account Details</div>
        <div class="card-form">
            <div class="bank-detail-inputs">

                <label class="bank-input-label">Bank Name<span class="required">*</span></label>
                <select class="bank-detail-input form-select select" required name="bank_name" id="bank_name">
                    <option value="">Select Bank Name</option>
                    @foreach($banks as $bank)
                    <option value="{{$bank->name}}">{{$bank->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Account Holder Name<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="holder_name" id="account_holder_name" placeholder="Enter Account Holder Name" required>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Account Number<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="account_number" id="account_number" placeholder="Enter Account Number" maxlength="64" pattern="[0-9]+" inputmode="numeric" required>
                <small class="form-text text-muted">Only digits are allowed</small>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Confirm Account Number<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="confirm_account_number" id="confirm_account_number" placeholder="Enter Confirm Account Number" maxlength="64" pattern="[0-9]+" inputmode="numeric" required>
                <small class="form-text text-muted">Only digits are allowed. The account number and confirm account number must match.</small>
                <div class="invalid-feedback">
                    The account number and confirm account number must match.
                </div>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">IFSC Code<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="ifsc_code" id="ifsc_code" placeholder="Enter IFSC Code" maxlength="32" required>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Branch Name<span class="required">*</span></label>
                <input class="bank-detail-input form-control" type="text" name="branch_name" id="branch_name" placeholder="Enter Branch Name" maxlength="32" required>
            </div>

        </div>
    </div>

    <br>

    <div class="bank-card">
        <div class="card-top-border">Document Uploads</div>
        <div class="card-form">
            <div class="bank-detail-inputs">
                <label class="bank-input-label">PAN Photo<span class="required">*</span></label>
                <div class="file-input-wrapper">
                    <input class="bank-detail-input form-control" type="file" name="pan_photo" id="pan_photo" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG only (Max size: 4MB)</small>
                </div>
                <div class="image-preview-container" id="pan_photo_preview" style="display: none;">
                    <img class="image-preview" id="pan_photo_preview_img" src="" alt="PAN Photo Preview">
                    <button type="button" class="remove-image-btn" onclick="removeImage('pan_photo')" title="Remove image">&times;</button>
                </div>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Aadhar Photo<span class="required">*</span></label>
                <div class="file-input-wrapper">
                    <input class="bank-detail-input form-control" type="file" name="aadhar_photo" id="aadhar_photo" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG only (Max size: 4MB)</small>
                </div>
                <div class="image-preview-container" id="aadhar_photo_preview" style="display: none;">
                    <img class="image-preview" id="aadhar_photo_preview_img" src="" alt="Aadhar Photo Preview">
                    <button type="button" class="remove-image-btn" onclick="removeImage('aadhar_photo')" title="Remove image">&times;</button>
                </div>
            </div>
            <div class="bank-detail-inputs">
                <label class="bank-input-label">Passbook Photo<span class="required">*</span></label>
                <div class="file-input-wrapper">
                    <input class="bank-detail-input form-control" type="file" name="passbook_photo" id="passbook_photo" accept="image/jpeg,image/png,image/jpg" required>
                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG only (Max size: 4MB)</small>
                </div>
                <div class="image-preview-container" id="passbook_photo_preview" style="display: none;">
                    <img class="image-preview" id="passbook_photo_preview_img" src="" alt="Passbook Photo Preview">
                    <button type="button" class="remove-image-btn" onclick="removeImage('passbook_photo')" title="Remove image">&times;</button>
                </div>
            </div>
        </div>
    </div>

    <br>

    <div class="save-btn-container">
        <button type="submit" class="save-btn">Save</button>
        <a href="{{ route('link-bank.index') }}" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </div>
</form>
@endsection

@section('script')
<script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Initialize Select2
    $(document).ready(function() {
        $('.select').select2({
            placeholder: "Select an option",
            allowClear: true
        });

        // Restrict account number fields to digits only
        $('#account_number, #confirm_account_number').on('input', function(e) {
            // Remove any non-digit characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Handle paste event to filter non-numeric characters
        $('#account_number, #confirm_account_number').on('paste', function(e) {
            var paste = (e.originalEvent || e).clipboardData.getData('text');
            if (!/^\d+$/.test(paste)) {
                e.preventDefault();
                // Only allow digits
                this.value = paste.replace(/[^0-9]/g, '');
            }
        });

        // Prevent non-numeric keypress
        $('#account_number, #confirm_account_number').on('keypress', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([46, 8, 9, 27, 13].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow: home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

        // Validate account numbers match on blur
        $('#confirm_account_number').on('blur', function() {
            var accountNumber = $('#account_number').val();
            var confirmAccountNumber = $(this).val();
            
            if (accountNumber && confirmAccountNumber && accountNumber !== confirmAccountNumber) {
                this.setCustomValidity('The account number and confirm account number must match.');
                $(this).addClass('is-invalid');
            } else {
                this.setCustomValidity('');
                $(this).removeClass('is-invalid');
            }
        });

        // Clear validation when user starts typing
        $('#account_number, #confirm_account_number').on('input', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
            }
        });

        // Image preview functionality
        function previewImage(inputId) {
            var input = document.getElementById(inputId);
            var previewContainer = document.getElementById(inputId + '_preview');
            var previewImg = document.getElementById(inputId + '_preview_img');
            
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                
                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    bootbox.alert({
                        message: 'Please select a valid image file (JPEG, JPG, or PNG only).',
                        className: 'bootbox-danger'
                    });
                    input.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Validate file size (4MB)
                if (file.size > 4 * 1024 * 1024) {
                    bootbox.alert({
                        message: 'File size must be less than 4MB.',
                        className: 'bootbox-danger'
                    });
                    input.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Function to remove image
        window.removeImage = function(inputId) {
            var input = document.getElementById(inputId);
            var previewContainer = document.getElementById(inputId + '_preview');
            
            input.value = '';
            previewContainer.style.display = 'none';
        };

        // Attach preview to file inputs
        $('#pan_photo').on('change', function() {
            previewImage('pan_photo');
        });

        $('#aadhar_photo').on('change', function() {
            previewImage('aadhar_photo');
        });

        $('#passbook_photo').on('change', function() {
            previewImage('passbook_photo');
        });
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.3/bootbox.min.js"></script>
@endsection