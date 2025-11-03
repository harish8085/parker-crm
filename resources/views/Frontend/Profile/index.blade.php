@extends('Layout.app')
@section('style')
<link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
@endsection
@section('body')
<div class="tab-content">
    <div>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#personal">Personal Details</a>
            </li>

            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#password">Manage Password</a>
            </li>
            <!-- <li class="nav-item">
                      <a class="nav-link" data-bs-toggle="tab" href="#menu2">Menu 2</a>
                    </li> -->
        </ul>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div id="personal" class="container tab-pane active">
        <br>
        <div class="bank-card">
            <form class="needs-validation" action="{{url('/profile/update')}}" method="POST" novalidate>
                @csrf
                <div class="card-form">
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">First Name</label>
                        <input class="bank-detail-input form-control" type="text" id="first_name" name="first_name" placeholder="Enter your first name" value="{{$user->first_name}}" />
                    </div>
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Last Name</label>
                        <input class="bank-detail-input form-control" type="text" id="last_name" name="last_name" placeholder="Enter your last name" value="{{$user->last_name}}" />
                    </div>
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Email</label>
                        <input class="bank-detail-input form-control" type="text" id="email" name="email" placeholder="Enter your email" value="{{$user->email}}" />
                    </div>
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Phone Number</label>
                        <input class="bank-detail-input form-control" type="text" id="phone" name="phone" placeholder="Enter phone number" value="{{$user->phone}}" />
                    </div>

                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Pan Number</label>
                        <input class="bank-detail-input form-control" type="text" id="pan_number" name="pan_number" placeholder="Enter pan number" value="{{$user->pan_number}}" />
                    </div>


                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Aadhar Number</label>
                        <input class="bank-detail-input form-control" type="text" id="aadhar_number" name="aadhar_number" placeholder="Enter aadhar number" value="{{$user->aadhar_number}}" />
                    </div>

                  
                </div>
                <div class="save-btn-container">
                    <button class="save-btn" id="submitBtn">Save</button>
                </div>
            </form>

        </div>
    </div>

    <div id="password" class="container tab-pane fade">
        <br>
        <div class="bank-card">
            <form class="needs-validation2" action="{{url('/profile/update-password')}}" method="POST" novalidate>
                @csrf
                <div class="card-form">
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Old Password</label>
                        <input class="bank-detail-input form-control" type="password" id="old_password" name="old_password" placeholder="Enter your old password" />
                    </div>
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">New Password</label>
                        <input class="bank-detail-input form-control" type="password" id="new_password" name="new_password" placeholder="Enter your new password" />
                        <div class="invalid-feedback">
                            Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                        </div>
                    </div>
                    <div class="bank-detail-inputs">
                        <label class="bank-input-label">Confirm Password</label>
                        <input class="bank-detail-input form-control" type="password" id="confirm_password" name="confirm_password" placeholder="Enter your confirm password" />
                        <div class="invalid-feedback invalid-feedback1">
                            Password does not match
                        </div>
                    </div>
                </div>
                <div class="save-btn-container">
                    <button class="save-btn" id="submitBtn2">Save</button>
                </div>
            </form>

        </div>

    </div>
</div>
@endsection

@section('script')
<script>
    // Aadhar Number Regex
    var aadharRegex = /^\d{12}$/;

    // PAN Number Regex
    var panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;

    // Email Regex
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    $('#new_password').change(function() {
        if (!passwordRegex.test($('#new_password').val())) {
            $('#new_password').removeClass('is-valid').addClass('is-invalid');
            $('.invalid-feedback').show()
        } else {
            $('#new_password').addClass('is-valid').removeClass('is-invalid');
            $('.invalid-feedback').hide()

        }
    });
    $('#pan_number').change(function() {
        if (!panRegex.test($('#pan_number').val())) {
            $('#pan_number').removeClass('is-valid').addClass('is-invalid');
            return false;
        } else {
            $('#pan_number').addClass('is-valid').removeClass('is-invalid');
        }
    });

    $('#aadhar_number').change(function() {
        if (!aadharRegex.test($('#aadhar_number').val())) {
            $('#aadhar_number').removeClass('is-valid').addClass('is-invalid');
        } else {
            $('#aadhar_number').addClass('is-valid').removeClass('is-invalid');
        }
    });


    $('#submitBtn').click(function(event) {
        event.preventDefault();
        var isValid = true;
        if (!$('#first_name').val()) {
            $('#first_name').removeClass('is-valid').addClass('is-invalid');
            $('#first_name').focus();
            isValid = false;
            return false;
        } else if (!/^[a-zA-Z ]+$/.test($('#first_name').val())) {
            $('#first_name').removeClass('is-valid').addClass('is-invalid');
            $('#first_name').focus();
            isValid = false;
            return false;
        } else {
            $('#first_name').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#last_name').val()) {
            $('#last_name').removeClass('is-valid').addClass('is-invalid');
            $('#last_name').focus();
            isValid = false;
            return false;

        } else if (!/^[a-zA-Z ]+$/.test($('#last_name').val())) {
            $('#last_name').removeClass('is-valid').addClass('is-invalid');
            $('#last_name').focus();
            isValid = false;
            return false;
        } else {
            $('#last_name').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#email').val()) {
            $('#email').removeClass('is-valid').addClass('is-invalid');
            $('#email').focus();
            isValid = false;
            return false;

        } else if (!emailRegex.test($('#email').val())) {
            $('#email').removeClass('is-valid').addClass('is-invalid');
            $('#email').focus();
            isValid = false;
            return false;
        } else {
            $('#email').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#phone').val()) {
            $('#phone').removeClass('is-valid').addClass('is-invalid');
            $('#phone').focus();
            isValid = false;
            return false;

        } else {
            $('#phone').addClass('is-valid').removeClass('is-invalid');
        }


        if (!panRegex.test($('#pan_number').val())) {
            $('#pan_number').removeClass('is-valid').addClass('is-invalid');
            $('#pan_number').focus();
            isValid = false;
            return false;
        } else {
            $('#pan_number').addClass('is-valid').removeClass('is-invalid');
        }

        if (!aadharRegex.test($('#aadhar_number').val())) {
            $('#aadhar_number').removeClass('is-valid').addClass('is-invalid');
            $('#aadhar_number').focus();
            isValid = false;
            return false;
        } else {
            $('#aadhar_number').addClass('is-valid').removeClass('is-invalid');
        }

        //address details

        if (!$('#address_1').val()) {
            $('#address_1').removeClass('is-valid').addClass('is-invalid');
            $('#address_1').focus();
            isValid = false;
            return false;

        } else {
            $('#address_1').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#address_2').val()) {
            $('#address_2').removeClass('is-valid').addClass('is-invalid');
            $('#address_2').focus();
            isValid = false;
            return false;

        } else {
            $('#address_2').addClass('is-valid').removeClass('is-invalid');
        }


        if (!$('#landmark').val()) {
            $('#landmark').removeClass('is-valid').addClass('is-invalid');
            $('#landmark').focus();
            isValid = false;
            return false;

        } else {
            $('#landmark').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#state').val()) {
            $('#state').removeClass('is-valid').addClass('is-invalid');
            $('#state').focus();
            isValid = false;
            return false;

        } else {
            $('#state').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#district').val()) {
            $('#district').removeClass('is-valid').addClass('is-invalid');
            $('#district').focus();
            isValid = false;
            return false;

        } else {
            $('#district').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#pincode').val()) {
            $('#pincode').removeClass('is-valid').addClass('is-invalid');
            $('#pincode').focus();
            isValid = false;
            return false;
        } else {
            $('#pincode').addClass('is-valid').removeClass('is-invalid');
        }




        // If form is valid, submit the form
        if (isValid) {
            $('.needs-validation').submit();
        }
    });



    $('#submitBtn1').click(function(event) {
        event.preventDefault();

        // Serialize form data including dynamically added bank detail inputs
        var formData = $('#BankData').serialize();

        // Send form data to the controller via AJAX
        $.ajax({
            url: `{{url('/profile/updateBank')}}`,
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Handle success response from the server

                if (response.code == 200) {
                    location.reload()
                }
            },
            error: function(xhr, status, error) {
                // Handle error response from the server
                console.error(error);
            }
        });
    });


    $(document).ready(function() {
        // Add event listener to the Add More button
        $('.add-more').click(function(event) {
            event.preventDefault();

            // Get the container where bank detail inputs will be added
            var container = $('#data');

            // Clone the bank detail template
            var clone = $('#bankDetailTemplate').clone();
            clone.find('input').val('');
            container.append('<hr>');

            // Make the cloned inputs visible by removing the "hidden" style
            clone.removeAttr('style');

            // Append the cloned bank detail inputs to the container
            container.append(clone);
        });
    });

    $('#submitBtn2').click(function(event) {
        event.preventDefault();
        var isValid = true;

        if (!$('#old_password').val()) {
            $('#old_password').removeClass('is-valid').addClass('is-invalid');
            $('#old_password').focus();
            isValid = false;
            return false;

        } else {
            $('#old_password').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#new_password').val()) {
            $('#new_password').removeClass('is-valid').addClass('is-invalid');
            $('#new_password').focus();
            isValid = false;
            return false;

        } else {
            $('#new_password').addClass('is-valid').removeClass('is-invalid');
        }

        if (!$('#confirm_password').val()) {
            $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
            $('#confirm_password').focus();
            isValid = false;
            return false;

        } else {
            $('#confirm_password').addClass('is-valid').removeClass('is-invalid');
        }

        if ($('#confirm_password').val() !== $('#new_password').val()) {
            $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
            $('.invalid-feedback1').show()
            isValid = false;
            return false;
        }



        // If form is valid, submit the form
        if (isValid) {
            $('.needs-validation2').submit();
        }
    });
</script>
@endsection