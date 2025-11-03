 @extends('Layout.app')
 @section('style')
 <link rel="stylesheet" href="{{asset('assets/css/add-service-1.css')}}">
 <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
 <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
 @endsection
 @section('body')

 <div class="bank-card">
     <div class="card-top-border">Personal Details</div>
     <div class="card-form">
         <div class="bank-detail-inputs">
             <label class="bank-input-label">First Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="first_name" id="first_name" placeholder="Enter first name" value="{{$user->first_name}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Last Name<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="last_name" id="last_name" placeholder="Enter last name" value="{{$user->last_name}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Email<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="email" id="email" placeholder="Enter user email" value="{{$user->email}}" disabled />
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Phone Number<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="tel" maxlength="10" name="phone" id="phone" placeholder="Enter user phone number" value="{{$user->phone}}" disabled>
         </div>

         <!-- <div class="bank-detail-inputs">
             <label class="bank-input-label">Pan Card<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="tel" maxlength="12" name="pan_number" id="pan_number" placeholder="Enter your pan number" value="{{$user->pan_number}}" disabled>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Aadhar Number<span class="required">*</span></label>
             <input class="bank-detail-input form-control" type="text" name="aadhar_number" id="aadhar_number" placeholder="Enter your aadhar number" value="{{$user->aadhar_number}}" disabled />
         </div> -->

     </div>
 </div>
 <br>
 <div class="bank-card">
     <div class="card-top-border">Other Details</div>
     <div class="card-form">
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select Role<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="role" id="role" disabled>
                 <option value="">Select Role</option>
                 @foreach($roles as $role)
                 <option value="{{$role->id}}" {{($user->roles[0]->id == $role->id)?'selected':''}}>{{$role->name}}</option>
                 @endforeach
             </select>
         </div>
         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select Status<span class="required">*</span></label>
             <select class="bank-detail-input form-select" required name="status" id="status" disabled>
                 <option value="1" {{($user->status == 1)?'selected':''}}>Active</option>
                 <option value="0" {{($user->status == 0)?'selected':''}}>In-Active</option>
             </select>
         </div>

         <div class="bank-detail-inputs">
             <label class="bank-input-label">Select Channel Partner<span class="required">*</span></label>
             <select class="bank-detail-input select2  form-select" name="access_id[]" multiple disabled>

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
 @endsection
 @section('script')
 <script>
     $(document).ready(function() {
         $('.select2').select2({
             placeholder: 'Select an option',
             allowClear: true // Adds a clear button
             // Other options...
         })
     });
 </script>
 @endsection