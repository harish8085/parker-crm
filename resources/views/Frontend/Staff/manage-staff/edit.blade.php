 @extends('Layout.app')
 @section('style')
 <link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
 <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
 @endsection
 @section('body')

 <h2>Edit Staff </h2>
 <form class="needs-validation" action="{{url('/staff/update/'.$user->id)}}" method="POST" novalidate>
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
         <div class="card-top-border">Personal Details</div>
         <div class="card-form">
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">First Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="first_name" id="first_name" placeholder="Enter first name" value="{{$user->first_name}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Last Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="last_name" id="last_name" placeholder="Enter last name" value="{{$user->last_name}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Email<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="email" id="email" placeholder="Enter user email" value="{{$user->email}}" />
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Phone Number<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="number" maxlength="10" name="phone" id="phone" placeholder="Enter user phone number" value="{{$user->phone}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Password<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="password" name="password" id="password" placeholder="Enter user password" />
                 <div class="invalid-feedback">
                     Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                 </div>
             </div>
       
         </div>
     </div>
     <br>
 
     <br>
     <div class="bank-card">
         <div class="card-top-border">Other Details</div>
         <div class="card-form">
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select Role<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="role" id="role">
                     <option value="">Select Role</option>
                     @foreach($roles as $role)
                     <option value="{{$role->id}}" {{($user->roles[0]->id == $role->id)?'selected':''}}>{{$role->name}}</option>
                     @endforeach
                 </select>
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select Status<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="status" id="status">
                     <option value="1" {{($user->status == 1)?'selected':''}}>Active</option>
                     <option value="0" {{($user->status == 0)?'selected':''}}>In-Active</option>
                 </select>
             </div>

             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select Channel Partner<span class="required">*</span></label>
                 <select class="bank-detail-input select2  form-select" name="access_id[]" multiple>

                     <optgroup label="Channel Partner">
                         @foreach($channels as $channel)
                         <option value="{{$channel->id}}" {{in_array($channel->id, $assignedUser)?'selected':''}}>{{$channel->first_name}}</option>
                         @endforeach
                     </optgroup>
                     <optgroup label="Sales Person">
                         @foreach($sales as $sale)
                         <option value="{{$sale->id}}" {{in_array($sale->id, $assignedUser)?'selected':''}}>{{$sale->first_name}} {{$sale->last_name}}</option>
                         @endforeach
                     </optgroup>
                 </select>
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
     $(document).ready(function() {
         $('.select2').select2({
             placeholder: 'Select an option',
             allowClear: true // Adds a clear button
             // Other options...
         });

         $('#state').change(function() {
             var stateId = $(this).val();
             $.ajax({
                 url: '/getDistrict/' + stateId,
                 type: 'GET',
                 headers: {
                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                 },
                 success: function(response) {
                     $('#district').html('')
                     $('#district').append('<option value="" selected disabled>Select District</option>')
                     $('#district').val('')
                     $('#district').append(response)
                 },
                 error: function(xhr) {
                     console.log(xhr.responseText);
                 }
             });

         });
         // Aadhar Number Regex
         var aadharRegex = /^\d{12}$/;

         // PAN Number Regex
         var panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;

         // Email Regex
         var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

         // IFSC Regex
         var ifscRegex = /^[A-Z]{4}[0][A-Z0-9]{6}$/;

         // Password Regex
         var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

         //  $('#pan_number').change(function() {
         //      if (!panRegex.test($('#pan_number').val())) {
         //          $('#pan_number').removeClass('is-valid').addClass('is-invalid');
         //          return false;
         //      } else {
         //          $('#pan_number').addClass('is-valid').removeClass('is-invalid');
         //      }
         //  });

         //  $('#aadhar_number').change(function() {
         //      if (!aadharRegex.test($('#aadhar_number').val())) {
         //          $('#aadhar_number').removeClass('is-valid').addClass('is-invalid');
         //      } else {
         //          $('#aadhar_number').addClass('is-valid').removeClass('is-invalid');
         //      }
         //  });


         $('#phone').change(function() {
             if ($('#phone').val().length != 10) {
                 $('#phone').removeClass('is-valid').addClass('is-invalid');
             } else {
                 $('#phone').addClass('is-valid').removeClass('is-invalid');
             }
         });

         $('#password').change(function() {
             if (!passwordRegex.test($('#password').val())) {
                 $('#password').removeClass('is-valid').addClass('is-invalid');
                 $('.invalid-feedback').show()
             } else {
                 $('#password').addClass('is-valid').removeClass('is-invalid');
                 $('.invalid-feedback').hide()

             }
         });


         //  $('#pincode').change(function() {
         //      if ($('#pincode').val().length != 6) {
         //          $('#pincode').removeClass('is-valid').addClass('is-invalid');
         //      } else {
         //          $('#pincode').addClass('is-valid').removeClass('is-invalid');
         //      }
         //  });

         //  $('#ifsc_code').change(function() {
         //      if (!ifscRegex.test($('#ifsc_code').val())) {
         //          $('#ifsc_code').removeClass('is-valid').addClass('is-invalid');
         //      } else {
         //          $('#ifsc_code').addClass('is-valid').removeClass('is-invalid');
         //      }
         //  });

         $('#submitBtn').click(function(event) {
             // Prevent default form submission
             event.preventDefault();

             // Perform form validation
             var isValid = true;


             //  Personal Details

             if (!$('#first_name').val()) {
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

             } else if ($('#phone').val().length != 10) {
                 console.log($('#phone').val().length != 10);
                 $('#phone').removeClass('is-valid').addClass('is-invalid');
                 $('#phone').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#phone').addClass('is-valid').removeClass('is-invalid');
             }

             if (passwordRegex.test($('#password').val())) {
                 if (!passwordRegex.test($('#password').val())) {
                     $('#password').removeClass('is-valid').addClass('is-invalid');
                     $('#password').focus();
                     $('.invalid-feedback').show()
                     isValid = false;
                     return false;
                 } else {
                     $('#password').addClass('is-valid').removeClass('is-invalid');
                     $('.invalid-feedback').hide()

                 }
             }



             //  if (!panRegex.test($('#pan_number').val())) {
             //      $('#pan_number').removeClass('is-valid').addClass('is-invalid');
             //      $('#pan_number').focus();
             //      isValid = false;
             //      return false;
             //  } else {
             //      $('#pan_number').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!aadharRegex.test($('#aadhar_number').val())) {
             //      $('#aadhar_number').removeClass('is-valid').addClass('is-invalid');
             //      $('#aadhar_number').focus();
             //      isValid = false;
             //      return false;
             //  } else {
             //      $('#aadhar_number').addClass('is-valid').removeClass('is-invalid');
             //  }


             //  //  Address Details
             //  if (!$('#address_1').val()) {
             //      $('#address_1').removeClass('is-valid').addClass('is-invalid');
             //      $('#address_1').focus();
             //      isValid = false;
             //      return false;
             //  } else {
             //      $('#address_1').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#address_2').val()) {
             //      $('#address_2').removeClass('is-valid').addClass('is-invalid');
             //      $('#address_2').focus();
             //      isValid = false;
             //      return false;
             //  } else {
             //      $('#address_2').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#landmark').val()) {
             //      $('#landmark').removeClass('is-valid').addClass('is-invalid');
             //      $('#landmark').focus();
             //      isValid = false;
             //      return false;
             //  } else {
             //      $('#landmark').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#state').val()) {
             //      $('#state').removeClass('is-valid').addClass('is-invalid');
             //      $('#state').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#state').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#district').val()) {
             //      $('#district').removeClass('is-valid').addClass('is-invalid');
             //      $('#district').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#district').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#pincode').val()) {
             //      $('#pincode').removeClass('is-valid').addClass('is-invalid');
             //      $('#pincode').focus();
             //      isValid = false;
             //      return false;
             //  } else if ($('#pincode').val().length != 6) {
             //      $('#pincode').removeClass('is-valid').addClass('is-invalid');
             //      $('#pincode').focus();
             //      isValid = false;
             //      return false;
             //  } else {

             //      $('#pincode').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#bank_name').val()) {
             //      $('#bank_name').removeClass('is-valid').addClass('is-invalid');
             //      $('#bank_name').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#bank_name').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#branch_name').val()) {
             //      $('#branch_name').removeClass('is-valid').addClass('is-invalid');
             //      $('#branch_name').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#branch_name').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#holder_name').val()) {
             //      $('#holder_name').removeClass('is-valid').addClass('is-invalid');
             //      $('#holder_name').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#holder_name').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!$('#account_number').val()) {
             //      $('#account_number').removeClass('is-valid').addClass('is-invalid');
             //      $('#account_number').focus();
             //      isValid = false;
             //      return false;

             //  } else {
             //      $('#account_number').addClass('is-valid').removeClass('is-invalid');
             //  }

             //  if (!ifscRegex.test($('#ifsc_code').val())) {
             //      $('#ifsc_code').removeClass('is-valid').addClass('is-invalid');
             //      $('#ifsc_code').focus();

             //      isValid = false
             //  } else {
             //      $('#ifsc_code').addClass('is-valid').removeClass('is-invalid');
             //  }

             if (!$('#role').val()) {
                 $('#role').removeClass('is-valid').addClass('is-invalid');
                 $('#role').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#role').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#status').val()) {
                 $('#status').removeClass('is-valid').addClass('is-invalid');
                 $('#status').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#status').addClass('is-valid').removeClass('is-invalid');
             }



             // If form is valid, submit the form
             if (isValid) {
                 $('.needs-validation').submit();
             }
         });
     });
 </script>
 @endsection