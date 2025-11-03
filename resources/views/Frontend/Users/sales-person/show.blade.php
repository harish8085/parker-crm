 @extends('Layout.app')
 @section('style')
 <link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">

 @endsection
 @section('body')

 <h2> Sales Person </h2>

 <div class="bank-card">
     <div class="card-top-border">Personal Details</div>
     <div class="card-form">
         <div class="bank-detail-inputs">
             <label class="bank-input-label">First Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="first_name" id="first_name" placeholder="Enter first name" value="{{$sale->first_name}}" disabled>
         </div>

         <div class="bank-detail-inputs">
             <label class="bank-input-label">Last Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="last_name" id="last_name" placeholder="Enter last name" value="{{$sale->last_name}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Email<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="email" id="email" placeholder="Enter user email" value="{{$sale->email}}" disabled />
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Phone Number<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="tel" maxlength="10" name="phone" id="phone" placeholder="Enter user phone number" value="{{$sale->phone}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Pan Card<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="tel" maxlength="12" name="pan_number" id="pan_number" placeholder="Enter your pan number" value="{{$sale->pan_number}}" disabled />
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Aadhar Number<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="aadhar_number" id="aadhar_number" placeholder="Enter your aadhar number" value="{{$sale->aadhar_number}}" disabled />
         </div>

     </div>
 </div>
 <br>
 <div class="bank-card">
     <div class="card-top-border">Address Details</div>
     <div class="card-form">
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Address Line 1<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="address_1" id="address_1" placeholder="Enter Address line 1" value="{{$sale->address_1}}" disabled />
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Address Line 2<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="address_2" id="address_2" placeholder="Enter Address line 2" value="{{$sale->address_2}}" disabled />
         </div>
         <div class=" bank-detail-inputs">
             <label class="bank-input-label">Landmark<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="landmark" id="landmark" placeholder="Enter landmark" value="{{$sale->landmark}}" disabled />
         </div>
         <div class=" bank-detail-inputs">
             <label class="bank-input-label">Select States<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="state" id="state" disabled>
                 @foreach($states as $state)
                 <option value="{{$state['state_code']}}" {{($sale->state == $state['state_code'])?'selected':''}}>{{$state['state']}}</option>
                 @endforeach
             </select>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select District<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="district" id="district" disabled>
                 @foreach($districts as $district)
                 <option value="{{$district}}" {{($sale->district == $district)?'selected':''}}>{{$district}}</option>
                 @endforeach
             </select>

         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Pincode<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="tel" maxlength="6" name="pincode" id="pincode" placeholder="Enter Pincode" value="{{$sale->pincode}}" disabled>
         </div>
     </div>
 </div>
 <br>
 <div class=" bank-card">
     <div class="card-top-border">Bank Details</div>
     <div class="card-form">
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Bank Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="bank_name" id="bank_name" placeholder="Enter Bank Name" value="{{$bank->bank_name}}" disabled>
         </div>
         <div class=" bank-detail-inputs">
             <label class="bank-input-label">Branch Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="branch_name" id="branch_name" placeholder="Enter Branch Name" value="{{$bank->branch_name}}" disabled />
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Account Holder Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="holder_name" id="holder_name" placeholder="Enter Holder Name" value="{{$bank->holder_name}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Account Number<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="number" min=0sa name="account_number" id="account_number" placeholder="Enter Account Number" value="{{$bank->account_number}}" disabled />
         </div>

         <div class="bank-detail-inputs">
             <label class="bank-input-label">IFSC Code<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="ifsc_code" id="ifsc_code" placeholder="Enter IFSC Code" value="{{$bank->ifsc_code}}" disabled>
         </div>
     </div>
 </div>

 <br>
 <div class="bank-card">
     <div class="card-top-border">Other Details</div>
     <div class="card-form">

         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select Service<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="service_type" id="service_type" disabled>
                 <option value="" selected disabled>Select Service</option>
                 @foreach($services as $service)
                 <option value="{{$service->id}}" {{($sale->service_type == $service->id)?'selected':''}}>{{$service->service_name}}</option>
                 @endforeach
             </select>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select Status<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="status" id="status" disabled>
                 <option value="1" {{($sale->status == 1)?'selected':''}}>Active</option>
                 <option value="0" {{($sale->status == 0)?'selected':''}}>In-Active</option>
             </select>
         </div>

     </div>
 </div>

 @endsection