<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('assets/css/signup.css')}}">
    <link rel="icon" type="image/x-icon" href="{{asset('assets/images/favicon.webp')}}">

    <!-- Latest compiled and minified CSS bootstrap 5-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>{{env('APP_NAME')}} | Sign Up</title>
</head>

<body>
    <div class="signup-container">
        <div class="signup-header">
            <div class="logo-section">
                <h2 class="logo-text">{{env('APP_NAME')}}</h2>
                <p class="tagline">Join our platform as a Channel Partner</p>
            </div>
            <div class="auth-links">
                <a href="{{url('/')}}" class="login-link">
                    <i class="fas fa-sign-in-alt"></i> Already have an account? Login
                </a>
            </div>
        </div>

        <div class="signup-form-container">
            <div class="form-header">
                <h3 class="form-title">Create Your Account</h3>
                <p class="form-subtitle">Fill in your details to get started</p>
            </div>

            <form class="needs-validation signup-form" action="{{url('/signup')}}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Personal Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-user"></i>
                        <h4>Personal Details</h4>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Channel Name <span class="required">*</span></label>
                            <input class="form-control" type="text" name="first_name" id="first_name" 
                                   placeholder="Enter channel name" value="{{old('first_name')}}" required>
                            <div class="invalid-feedback">Please enter a valid channel name.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Email Address <span class="required">*</span></label>
                            <input class="form-control" type="email" name="email" id="email" 
                                   placeholder="Enter your email" value="{{old('email')}}" required>
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Phone Number <span class="required">*</span></label>
                            <input class="form-control" type="tel" name="phone" id="phone" 
                                   placeholder="Enter your phone number" value="{{old('phone')}}" required>
                            <div class="invalid-feedback">Please enter a valid 10-digit phone number.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password <span class="required">*</span></label>
                            <div class="password-input-container">
                                <input class="form-control" type="password" name="password" id="password" 
                                       placeholder="Enter your password" value="{{old('password')}}" required>
                                <i class="fas fa-eye-slash password-toggle" onclick="togglePasswordVisibility('password')"></i>
                            </div>
                            <div class="invalid-feedback">
                                Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password <span class="required">*</span></label>
                            <div class="password-input-container">
                                <input class="form-control" type="password" value="{{old('password_confirmation')}}" name="password_confirmation" id="password_confirmation" 
                                       placeholder="Confirm your password" required>
                                <i class="fas fa-eye-slash password-toggle" onclick="togglePasswordVisibility('password_confirmation')"></i>
                            </div>
                            <div class="invalid-feedback">Passwords do not match.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">PAN Card Number <span class="required">*</span></label>
                            <input class="form-control" type="text" name="pan_number" id="pan_number" 
                                   placeholder="Enter your PAN number" value="{{old('pan_number')}}" required>
                            <div class="invalid-feedback">Please enter a valid PAN number.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Aadhar Number <span class="required">*</span></label>
                            <input class="form-control" type="text" name="aadhar_number" id="aadhar_number" 
                                   placeholder="Enter your Aadhar number" value="{{old('aadhar_number')}}" required>
                            <div class="invalid-feedback">Please enter a valid 12-digit Aadhar number.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Verification Code <span class="required">*</span></label>
                            <input class="form-control" type="text" name="verification_code" id="verification_code" 
                                   placeholder="Enter verification code" value="{{old('verification_code') ?? $verificationCode}}" readonly="readonly" required>
                            <div class="invalid-feedback">Please enter a valid verification code.</div>
                            <small class="form-text text-muted">Enter the code provided by your administrator.</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST Number</label>
                            <input class="form-control" type="text" name="gst_number" id="gst_number" 
                                   placeholder="Enter GST number" value="{{old('gst_number')}}" maxlength="15">
                            <div class="invalid-feedback">Please enter a valid 15-character GST number.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">GST Certificate <span class="required gst-cert-required" style="display: none;">*</span></label>
                            <input class="form-control" type="file" name="gst_certificate" id="gst_certificate" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Accepted formats: JPEG, JPG, PNG</small>
                            <div class="invalid-feedback">Please upload a valid image file.</div>
                        </div>
                    </div>
                </div>

                <!-- Address Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Address Details</h4>
                    </div>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="form-label">Address Line 1 <span class="required">*</span></label>
                            <input class="form-control" type="text" name="address_1" id="address_1" 
                                   placeholder="Enter your address" value="{{old('address_1')}}" required>
                            <div class="invalid-feedback">Please enter your address.</div>
                        </div>

                        <div class="form-group full-width">
                            <label class="form-label">Address Line 2 <span class="required">*</span></label>
                            <input class="form-control" type="text" name="address_2" id="address_2" 
                                   placeholder="Enter additional address details" value="{{old('address_2')}}" required>
                            <div class="invalid-feedback">Please enter additional address details.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Landmark <span class="required">*</span></label>
                            <input class="form-control" type="text" name="landmark" id="landmark" 
                                   placeholder="Enter nearby landmark" value="{{old('landmark')}}" required>
                            <div class="invalid-feedback">Please enter a landmark.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">State <span class="required">*</span></label>
                            <select class="form-select" name="state" id="state" required>
                                <option value="" selected disabled>Select State</option>
                                @foreach($states as $state)
                                <option value="{{$state['state_code']}}" {{old('state') == $state['state_code'] ? 'selected' : ''}}>
                                    {{$state['state']}}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a state.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">District <span class="required">*</span></label>
                            <select class="form-select" name="district" id="district" required>
                                <option value="" selected disabled>Select District</option>
                            </select>
                            <div class="invalid-feedback">Please select a district.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Pincode <span class="required">*</span></label>
                            <input class="form-control" type="text" name="pincode" id="pincode" 
                                   placeholder="Enter pincode" value="{{old('pincode')}}" required>
                            <div class="invalid-feedback">Please enter a valid 6-digit pincode.</div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-university"></i>
                        <h4>Bank Details</h4>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Bank Name <span class="required">*</span></label>
                            <input class="form-control" type="text" name="bank_name" id="bank_name" 
                                   placeholder="Enter bank name" value="{{old('bank_name')}}" required>
                            <div class="invalid-feedback">Please enter bank name.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Branch Name <span class="required">*</span></label>
                            <input class="form-control" type="text" name="branch_name" id="branch_name" 
                                   placeholder="Enter branch name" value="{{old('branch_name')}}" required>
                            <div class="invalid-feedback">Please enter branch name.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Holder Name <span class="required">*</span></label>
                            <input class="form-control" type="text" name="holder_name" id="holder_name" 
                                   placeholder="Enter account holder name" value="{{old('holder_name')}}" required>
                            <div class="invalid-feedback">Please enter account holder name.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Account Number <span class="required">*</span></label>
                            <input class="form-control" type="text" name="account_number" id="account_number" 
                                   placeholder="Enter account number" value="{{old('account_number')}}" required>
                            <div class="invalid-feedback">Please enter account number.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Account Number <span class="required">*</span></label>
                            <input class="form-control" type="text" name="confirm_account_number" id="confirm_account_number" 
                                   placeholder="Confirm account number" value="{{old('confirm_account_number')}}" required>
                            <div class="invalid-feedback">Account numbers do not match.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">IFSC Code <span class="required">*</span></label>
                            <input class="form-control" type="text" name="ifsc_code" id="ifsc_code" 
                                   placeholder="Enter IFSC code" value="{{old('ifsc_code')}}" required>
                            <div class="invalid-feedback">Please enter a valid IFSC code.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Aadhar Card Photo</label>
                            <input class="form-control" type="file" name="aadhar_photo" id="aadhar_photo" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Accepted formats: JPEG, JPG, PNG</small>
                            <div class="invalid-feedback">Please upload a valid image file.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">PAN Card Photo</label>
                            <input class="form-control" type="file" name="pan_photo" id="pan_photo" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Accepted formats: JPEG, JPG, PNG</small>
                            <div class="invalid-feedback">Please upload a valid image file.</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Passbook Photo</label>
                            <input class="form-control" type="file" name="passbook_photo" id="passbook_photo" 
                                   accept="image/jpeg,image/jpg,image/png">
                            <small class="form-text text-muted">Accepted formats: JPEG, JPG, PNG</small>
                            <div class="invalid-feedback">Please upload a valid image file.</div>
                        </div>
                    </div>
                </div>

                <!-- Service Details Section -->
                <div class="form-section">
                    <div class="section-header">
                        <i class="fas fa-cogs"></i>
                        <h4>Service Details</h4>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Select Service <span class="required">*</span></label>
                            <select class="form-select" name="service_type" id="service_type" required>
                                <option value="" selected disabled>Select Service</option>
                                @foreach($services as $service)
                                <option value="{{$service->id}}" {{old('service_type') == $service->id ? 'selected' : ''}}>
                                    {{$service->service_name}}
                                </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a service.</div>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms-section">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="terms-link">Terms and Conditions</a> and <a href="#" class="terms-link">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="submit-section">
                    <button type="submit" class="btn btn-primary btn-signup" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Password visibility toggle
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.nextElementSibling;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        }

        // State-District AJAX functionality
        $(document).ready(function() {
            // Check if GST number is already filled (from old input) and show required indicator
            var initialGstNumber = $('#gst_number').val().trim();
            if (initialGstNumber && initialGstNumber.length > 0) {
                $('.gst-cert-required').show();
                $('#gst_certificate').attr('data-required', 'true');
            }
            
            $('#state').change(function() {
                var stateId = $(this).val();
                let url = (window.location.host=='localhost') ? '/parker-crm/public/getDistrict/' : '/getDistrict/';
                if (stateId) {
                    $.ajax({
                        url: url + stateId,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#district').html('<option value="" selected disabled>Select District</option>');
                            $('#district').append(response);
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                } else {
                    $('#district').html('<option value="" selected disabled>Select District</option>');
                }
            });

            // Form validation
            var aadharRegex = /^\d{12}$/;
            var panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var ifscRegex = /^[A-Z]{4}[0][A-Z0-9]{6}$/;
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            var gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;

            // Real-time validation
            $('#pan_number').on('input', function() {
                var value = $(this).val().toUpperCase();
                $(this).val(value);
                if (value && !panRegex.test(value)) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#aadhar_number').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                $(this).val(value);
                if (value && !aadharRegex.test(value)) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#phone').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                $(this).val(value);
                if (value && value.length !== 10) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value && value.length === 10) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#pincode').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                $(this).val(value);
                if (value && value.length !== 6) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value && value.length === 6) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#ifsc_code').on('input', function() {
                var value = $(this).val().toUpperCase();
                $(this).val(value);
                if (value && !ifscRegex.test(value)) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#password').on('input', function() {
                var value = $(this).val();
                if (value && !passwordRegex.test(value)) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            $('#password_confirmation').on('input', function() {
                var value = $(this).val();
                var password = $('#password').val();
                if (value && value !== password) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value && value === password) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Verification code validation
            $('#verification_code').on('input', function() {
                var value = $(this).val();
                if (value && value.length < 3) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Confirm account number validation
            $('#confirm_account_number').on('input', function() {
                var value = $(this).val();
                var accountNumber = $('#account_number').val();
                if (value && value !== accountNumber) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else if (value && value === accountNumber) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });

            // Account number validation (to trigger confirm account number validation)
            $('#account_number').on('input', function() {
                var value = $(this).val();
                var confirmValue = $('#confirm_account_number').val();
                if (value) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                    // Re-validate confirm account number if it has a value
                    if (confirmValue) {
                        if (value !== confirmValue) {
                            $('#confirm_account_number').removeClass('is-valid').addClass('is-invalid');
                        } else {
                            $('#confirm_account_number').removeClass('is-invalid').addClass('is-valid');
                        }
                    }
                }
            });

            // GST number validation and conditional requirement for GST certificate
            $('#gst_number').on('input', function() {
                var value = $(this).val().toUpperCase();
                $(this).val(value);
                if (value && value.length > 0) {
                    if (value.length !== 15 || !gstRegex.test(value)) {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    }
                    // Show required indicator for GST certificate
                    $('.gst-cert-required').show();
                    $('#gst_certificate').attr('data-required', 'true');
                } else {
                    $(this).removeClass('is-invalid is-valid');
                    // Hide required indicator for GST certificate
                    $('.gst-cert-required').hide();
                    $('#gst_certificate').removeAttr('data-required');
                    $('#gst_certificate').removeClass('is-invalid is-valid');
                }
            });

            // GST certificate validation
            $('#gst_certificate').on('change', function() {
                var file = this.files[0];
                var gstNumber = $('#gst_number').val().trim();
                
                if (gstNumber && gstNumber.length > 0) {
                    if (!file) {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                    } else {
                        var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (validTypes.includes(file.type)) {
                            $(this).removeClass('is-invalid').addClass('is-valid');
                        } else {
                            $(this).removeClass('is-valid').addClass('is-invalid');
                        }
                    }
                } else {
                    $(this).removeClass('is-invalid is-valid');
                }
            });

            // Form submission validation
            $('#submitBtn').click(function(event) {
                event.preventDefault();
                
                var isValid = true;
                var form = $('.signup-form')[0];

                // Check all required fields
                $('.form-control[required], .form-select[required]').each(function() {
                    if (!$(this).val()) {
                        $(this).removeClass('is-valid').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                    }
                });

                // Conditional validation: GST certificate is required if GST number is provided
                var gstNumber = $('#gst_number').val().trim();
                if (gstNumber && gstNumber.length > 0) {
                    var gstCertificate = $('#gst_certificate')[0].files[0];
                    if (!gstCertificate) {
                        $('#gst_certificate').removeClass('is-valid').addClass('is-invalid');
                        isValid = false;
                    } else {
                        var validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (validTypes.includes(gstCertificate.type)) {
                            $('#gst_certificate').removeClass('is-invalid').addClass('is-valid');
                        } else {
                            $('#gst_certificate').removeClass('is-valid').addClass('is-invalid');
                            isValid = false;
                        }
                    }
                }

                // Check terms checkbox
                if (!$('#terms').is(':checked')) {
                    $('#terms').addClass('is-invalid');
                    isValid = false;
                } else {
                    $('#terms').removeClass('is-invalid');
                }

                if (isValid) {
                    form.submit();
                } else {
                    $('html, body').animate({
                        scrollTop: $('.is-invalid').first().offset().top - 100
                    }, 500);
                }
            });
        });
    </script>
</body>

</html>
