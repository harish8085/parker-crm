 @extends('Layout.app')
 @section('style')
 <link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">

 @endsection
 @section('body')

 <h2>Edit Sales Person </h2>
 <form class="needs-validation" action="{{url('/sales-person/update/'.$sale->id)}}" method="POST" novalidate>
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
                 <input class="bank-detail-input form-control" type="text" name="first_name" id="first_name" placeholder="Enter first name" value="{{$sale->first_name}}">
             </div>

             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Last Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="last_name" id="last_name" placeholder="Enter last name" value="{{$sale->last_name}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Email<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="email" id="email" placeholder="Enter user email" value="{{$sale->email}}" />
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Phone Number<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="number" maxlength="10" name="phone" id="phone" placeholder="Enter user phone number" value="{{$sale->phone}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Password<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="password" name="password" id="password" placeholder="Enter user password" />

             </div>

             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Pan Card<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="tel" maxlength="12" name="pan_number" id="pan_number" placeholder="Enter your pan number" value="{{$sale->pan_number}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Aadhar Number<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="number" name="aadhar_number" id="aadhar_number" placeholder="Enter your aadhar number" value="{{$sale->aadhar_number}}" />
             </div>

         </div>
     </div>
     <br>
     <div class="bank-card">
         <div class="card-top-border">Address Details</div>
         <div class="card-form">
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Address Line 1<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="address_1" id="address_1" placeholder="Enter Address line 1" value="{{$sale->address_1}}" />
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Address Line 2<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="address_2" id="address_2" placeholder="Enter Address line 2" value="{{$sale->address_2}}" />
             </div>
             <div class=" bank-detail-inputs">
                 <label class="bank-input-label">Landmark<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="landmark" id="landmark" placeholder="Enter landmark" value="{{$sale->landmark}}" />
             </div>
             <div class=" bank-detail-inputs">
                 <label class="bank-input-label">Select States<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="state" id="state">
                     @foreach($states as $state)
                     <option value="{{$state['state_code']}}" {{($sale->state == $state['state_code'])?'selected':''}}>{{$state['state']}}</option>
                     @endforeach
                 </select>
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select District<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="district" id="district">
                     @foreach($districts as $district)
                     <option value="{{$district}}" {{($sale->district == $district)?'selected':''}}>{{$district}}</option>
                     @endforeach
                 </select>

             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Pincode<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="number" maxlength="6" name="pincode" id="pincode" placeholder="Enter Pincode" value="{{$sale->pincode}}">
             </div>
         </div>
     </div>
     <br>
     <div class=" bank-card">
         <div class="card-top-border">Bank Details</div>
         <div class="card-form">
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Bank Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="bank_name" id="bank_name" placeholder="Enter Bank Name" value="{{$bank->bank_name}}">
             </div>
             <div class=" bank-detail-inputs">
                 <label class="bank-input-label">Branch Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="branch_name" id="branch_name" placeholder="Enter Branch Name" value="{{$bank->branch_name}}" />
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Account Holder Name<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="holder_name" id="holder_name" placeholder="Enter Holder Name" value="{{$bank->holder_name}}">
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Account Number<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="number" min=0 name="account_number" id="account_number" placeholder="Enter Account Number" value="{{$bank->account_number}}" />
             </div>

             <div class="bank-detail-inputs">
                 <label class="bank-input-label">IFSC Code<span class="required">*</span></label>
                 <input class="bank-detail-input form-control" type="text" name="ifsc_code" id="ifsc_code" placeholder="Enter IFSC Code" value="{{$bank->ifsc_code}}">
             </div>
         </div>
     </div>

     <br>
     <div class="bank-card">
         <div class="card-top-border">Other Details</div>
         <div class="card-form">

             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select Service<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="service_type" id="service_type">
                     <option value="" selected disabled>Select Service</option>
                     @foreach($services as $service)
                     <option value="{{$service->id}}" {{($sale->service_type == $service->id)?'selected':''}}>{{$service->service_name}}</option>
                     @endforeach
                 </select>
             </div>
             <div class="bank-detail-inputs">
                 <label class="bank-input-label">Select Status<span class="required">*</span></label>
                 <select class="bank-detail-input form-select" required name="status" id="status">
                     <option value="1" {{($sale->status == 1)?'selected':''}}>Active</option>
                     <option value="0" {{($sale->status == 0)?'selected':''}}>In-Active</option>
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

         var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

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


         $('#pincode').change(function() {
             if ($('#pincode').val().length != 6) {
                 $('#pincode').removeClass('is-valid').addClass('is-invalid');
             } else {
                 $('#pincode').addClass('is-valid').removeClass('is-invalid');
             }
         });

         $('#ifsc_code').change(function() {
             if (!ifscRegex.test($('#ifsc_code').val())) {
                 $('#ifsc_code').removeClass('is-valid').addClass('is-invalid');
             } else {
                 $('#ifsc_code').addClass('is-valid').removeClass('is-invalid');
             }
         });

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


             //  Address Details
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
             } else if ($('#pincode').val().length != 6) {
                 $('#pincode').removeClass('is-valid').addClass('is-invalid');
                 $('#pincode').focus();
                 isValid = false;
                 return false;
             } else {

                 $('#pincode').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#bank_name').val()) {
                 $('#bank_name').removeClass('is-valid').addClass('is-invalid');
                 $('#bank_name').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#bank_name').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#branch_name').val()) {
                 $('#branch_name').removeClass('is-valid').addClass('is-invalid');
                 $('#branch_name').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#branch_name').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#holder_name').val()) {
                 $('#holder_name').removeClass('is-valid').addClass('is-invalid');
                 $('#holder_name').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#holder_name').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#account_number').val()) {
                 $('#account_number').removeClass('is-valid').addClass('is-invalid');
                 $('#account_number').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#account_number').addClass('is-valid').removeClass('is-invalid');
             }

             if (!ifscRegex.test($('#ifsc_code').val())) {
                 $('#ifsc_code').removeClass('is-valid').addClass('is-invalid');
                 $('#ifsc_code').focus();

                 isValid = false
             } else {
                 $('#ifsc_code').addClass('is-valid').removeClass('is-invalid');
             }

             if (!$('#status').val()) {
                 $('#status').removeClass('is-valid').addClass('is-invalid');
                 $('#status').focus();
                 isValid = false;
                 return false;

             } else {
                 $('#status').addClass('is-valid').removeClass('is-invalid');
             }


             if (!$('#service_type').val()) {
                 $('#service_type').removeClass('is-valid').addClass('is-invalid');
                 $('#service_type').focus();
                 isValid = false;
                 return false;
             } else {
                 $('#service_type').addClass('is-valid').removeClass('is-invalid');
             }


             // If form is valid, submit the form
             if (isValid) {
                 $('.needs-validation').submit();
             }
         });
     });
 </script>
 @endsection