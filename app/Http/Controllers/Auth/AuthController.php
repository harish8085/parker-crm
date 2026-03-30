<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use App\Models\MasterCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpVerificationMail;
use App\Mail\WelcomeMail;
use App\Models\ChannelUser;
use App\Models\BankData;
use App\Models\Role;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function index()
    {
        $user_id =  Auth::id();
        if (Auth::user()) {
            return redirect('/dashboard');
        } else {
            return view('Auth.login');
        }
    }

    public function Login(Request $request)
    {
        $email = trim((string) $request->email);
        $password = (string) $request->password;

        if (empty($email) || empty($password)) {
            return redirect()->back()->with('error', "Invalid Request");
        }

        // Determine the subdomain and user type
        $subdomain = explode('.', $request->getHost())[0] ?? '';
        switch ($subdomain) {
            case 'admin':
                $type = ['admin', 'staff'];
                break;
            case 'parker':
                $type = ['admin', 'staff', 'channel', 'sales', 'maker', 'checker', 'associate_channel'];
                break;
            case 'partner':
                $type = ['channel', 'maker', 'checker', 'associate_channel'];
                break;
            case 'sales-team':
                $type = ['sales', 'maker', 'checker', 'associate_channel'];
                break;
            default:
                $type = ['admin', 'staff', 'channel', 'sales', 'maker', 'checker', 'associate_channel'];
                break;
        }

        $maskedEmail = (strlen($email) > 4)
            ? substr($email, 0, 2) . '***' . substr($email, -1)
            : '***';

        // \Illuminate\Support\Facades\Log::info('AUTH_LOGIN_STEP1_REQUEST', [
        //     'host' => $request->getHost(),
        //     'subdomain' => $subdomain,
        //     'email' => $maskedEmail,
        //     'remember_present' => $request->has('remember'),
        //     'allowed_user_types' => array_values($type),
        //     'password_input_length' => strlen($password),
        //     'password_trimmed_length' => strlen(trim($password)),
        // ]);

        // Case-insensitive email lookup to avoid collation differences between local and server
        $user = User::whereRaw('LOWER(email) = LOWER(?)', [$email])->first();

        // \Illuminate\Support\Facades\Log::info('AUTH_LOGIN_STEP2_USER_LOOKUP', [
        //     'email' => $maskedEmail,
        //     'user_found' => (bool) $user,
        //     'user_id' => $user?->id,
        //     'user_type' => $user?->user_type,
        //     'user_status' => $user?->status,
        //     'stored_password_prefix' => $user?->password ? substr((string) $user->password, 0, 4) : null,
        //     'stored_password_length' => $user?->password ? strlen((string) $user->password) : null,
        // ]);
        

        $hashOk = $user ? Hash::check($password, (string) $user->password) : false;


        // \Illuminate\Support\Facades\Log::info('AUTH_LOGIN_STEP3_PASSWORD_CHECK', [
        //     'email' => $maskedEmail,
        //     'hash_ok' => $hashOk,
        // ]);

        if (!$hashOk || !$user) {
            // \Illuminate\Support\Facades\Log::warning('AUTH_LOGIN_FAILED_CREDENTIALS', [
            //     'email' => $maskedEmail,
            //     'user_found' => (bool) $user,
            //     'user_id' => $user?->id,
            //     'user_type' => $user?->user_type,
            //     'stored_password_prefix' => $user?->password ? substr((string) $user->password, 0, 4) : null,
            //     'stored_password_length' => $user?->password ? strlen((string) $user->password) : null,
            // ]);
            return redirect()->back()->with('error', "Invalid Credential");
        }

        Auth::login($user);

        $userType = strtolower((string) $user->user_type);
        $allowedUserTypes = array_map('strtolower', $type);
        $allowedType = in_array($userType, $allowedUserTypes, true);

        // \Illuminate\Support\Facades\Log::info('AUTH_LOGIN_STEP4_ALLOWED_TYPE_CHECK', [
        //     'email' => $maskedEmail,
        //     'user_type' => $userType,
        //     'allowed' => $allowedType,
        //     'allowed_user_types' => $allowedUserTypes,
        // ]);

        if ($userType === 'channel' && (int) $user->status === 0) {
            Auth::logout();
            \Illuminate\Support\Facades\Log::warning('AUTH_LOGIN_BLOCKED_INACTIVE_CHANNEL', [
                'email' => $maskedEmail,
                'user_id' => $user->id,
                'status' => $user->status,
            ]);
            return redirect()->back()->with('error', 'Your account is inactive. Please contact admin.');
        }

        if ($userType === 'staff' || $userType === 'sales') {
            $parentChannel = \App\Models\ChannelUser::where('associate_channel_id', $user->id)->with('channel')->first();
            if ($parentChannel && $parentChannel->channel && (int) $parentChannel->channel->status === 0) {
                Auth::logout();
                // \Illuminate\Support\Facades\Log::warning('AUTH_LOGIN_BLOCKED_PARENT_INACTIVE', [
                //     'email' => $maskedEmail,
                //     'user_id' => $user->id,
                //     'parent_channel_user_id' => $parentChannel->id ?? null,
                //     'parent_channel_status' => $parentChannel->channel?->status,
                // ]);
                return redirect()->back()->with('error', 'Your parent is no longer associated with Parker.');
            }
        }

        if (!$allowedType) {
            Session::flush();
            Auth::logout();
            flash()->error('Account does not exists.')->flash();
            return redirect('/');
        }

        if ($request->has('remember') == null) {
            setcookie('email', $email, 100);
            setcookie('password', $password, 100);
        } else {
            setcookie('email', $email, time() + 606024100);
            setcookie('password', $password, time() + 606024100);
        }

        session('Login', true);
        flash()->success('Logged In successfully.')->flash();
        return redirect('/application');
    }


    public function Logout()
    {
        Session::flush();
        flash()
            ->success('Logged Out successfully.')
            ->flash();
        return redirect('/');
    }

    public function signup()
    {
        $user_id = Auth::id();
        if (Auth::user()) {
            return redirect('/dashboard');
        } else {
            // Get states and services for the signup form
            $states = [
                ['state_code' => 'AP', 'state' => 'Andhra Pradesh'],
                ['state_code' => 'AR', 'state' => 'Arunachal Pradesh'],
                ['state_code' => 'AS', 'state' => 'Assam'],
                ['state_code' => 'BR', 'state' => 'Bihar'],
                ['state_code' => 'CT', 'state' => 'Chhattisgarh'],
                ['state_code' => 'GA', 'state' => 'Goa'],
                ['state_code' => 'GJ', 'state' => 'Gujarat'],
                ['state_code' => 'HR', 'state' => 'Haryana'],
                ['state_code' => 'HP', 'state' => 'Himachal Pradesh'],
                ['state_code' => 'JK', 'state' => 'Jammu and Kashmir'],
                ['state_code' => 'JH', 'state' => 'Jharkhand'],
                ['state_code' => 'KA', 'state' => 'Karnataka'],
                ['state_code' => 'KL', 'state' => 'Kerala'],
                ['state_code' => 'MP', 'state' => 'Madhya Pradesh'],
                ['state_code' => 'MH', 'state' => 'Maharashtra'],
                ['state_code' => 'MN', 'state' => 'Manipur'],
                ['state_code' => 'ML', 'state' => 'Meghalaya'],
                ['state_code' => 'MZ', 'state' => 'Mizoram'],
                ['state_code' => 'NL', 'state' => 'Nagaland'],
                ['state_code' => 'OR', 'state' => 'Odisha'],
                ['state_code' => 'PB', 'state' => 'Punjab'],
                ['state_code' => 'RJ', 'state' => 'Rajasthan'],
                ['state_code' => 'SK', 'state' => 'Sikkim'],
                ['state_code' => 'TN', 'state' => 'Tamil Nadu'],
                ['state_code' => 'TG', 'state' => 'Telangana'],
                ['state_code' => 'TR', 'state' => 'Tripura'],
                ['state_code' => 'UP', 'state' => 'Uttar Pradesh'],
                ['state_code' => 'UT', 'state' => 'Uttarakhand'],
                ['state_code' => 'WB', 'state' => 'West Bengal'],
                ['state_code' => 'AN', 'state' => 'Andaman and Nicobar Islands'],
                ['state_code' => 'CH', 'state' => 'Chandigarh'],
                ['state_code' => 'DN', 'state' => 'Dadra and Nagar Haveli'],
                ['state_code' => 'DD', 'state' => 'Daman and Diu'],
                ['state_code' => 'DL', 'state' => 'Delhi'],
                ['state_code' => 'LD', 'state' => 'Lakshadweep'],
                ['state_code' => 'PY', 'state' => 'Puducherry']
            ];
            
            $services = Service::all();
            $encryptedCode = request()->query('code');
            $verificationCode = null;
            
            if ($encryptedCode) {
                try {
                    $verificationCode = Crypt::decryptString($encryptedCode);
                } catch (\Exception $e) {
                    // If decryption fails, set to null or handle error
                    $verificationCode = null;
                }
            }
            
            return view('Auth.signup', compact('states', 'services', 'verificationCode'));
        }
    }

    public function register(Request $request)
    {
        // Conditional validation: GST certificate is required if GST number is provided
        $gstCertificateRule = 'nullable|image|mimes:jpeg,jpg,png|max:2048';
        if ($request->filled('gst_number') && !empty(trim($request->gst_number))) {
            $gstCertificateRule = 'required|image|mimes:jpeg,jpg,png|max:2048';
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|min:10|max:10|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'pan_number' => 'required|string|unique:users,pan_number',
            'aadhar_number' => 'required|string|unique:users,aadhar_number',
            'verification_code' => 'required|string',
            'address_1' => 'required|string|max:255',
            'address_2' => 'required|string|max:255',
            'landmark' => 'required|string|max:255',
            'state' => 'required|string',
            'district' => 'required|string',
            'pincode' => 'required|string|regex:/^\d{6}$/',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'holder_name' => 'required|string|max:255',
            'account_number' => 'required|string',
            'confirm_account_number' => 'required|string|same:account_number', 
            'ifsc_code' => 'required|string',
            'service_type' => 'required|exists:services,id',
            'aadhar_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'pan_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'passbook_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'gst_number' => 'nullable|string|max:15|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/|unique:users,gst_number',
            'gst_certificate' => $gstCertificateRule,
        ], [
            // Personal Details
            'first_name.required' => 'Channel name is required.',
            'first_name.string' => 'Channel name must be a valid text.',
            'first_name.max' => 'Channel name must not exceed 255 characters.',
            
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address must not exceed 255 characters.',
            'email.unique' => 'This email address is already registered. Please use a different email.',
            
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a valid text.',
            'phone.min' => 'Phone number must be exactly 10 digits.',
            'phone.max' => 'Phone number must be exactly 10 digits.',
            'phone.unique' => 'This phone number is already registered. Please use a different phone number.',
            
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a valid text.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            
            'pan_number.required' => 'PAN card number is required.',
            'pan_number.string' => 'PAN card number must be a valid text.',
            
            'aadhar_number.required' => 'Aadhar number is required.',
            'aadhar_number.string' => 'Aadhar number must be a valid text.',
            
            'verification_code.required' => 'Verification code is required.',
            'verification_code.string' => 'Verification code must be a valid text.',
            
            // Address Details
            'address_1.required' => 'Address line 1 is required.',
            'address_1.string' => 'Address line 1 must be a valid text.',
            'address_1.max' => 'Address line 1 must not exceed 255 characters.',
            
            'address_2.required' => 'Address line 2 is required.',
            'address_2.string' => 'Address line 2 must be a valid text.',
            'address_2.max' => 'Address line 2 must not exceed 255 characters.',
            
            'landmark.required' => 'Landmark is required.',
            'landmark.string' => 'Landmark must be a valid text.',
            'landmark.max' => 'Landmark must not exceed 255 characters.',
            
            'state.required' => 'State is required.',
            'state.string' => 'State must be a valid text.',
            
            'district.required' => 'District is required.',
            'district.string' => 'District must be a valid text.',
            
            'pincode.required' => 'Pincode is required.',
            'pincode.string' => 'Pincode must be a valid text.',
            'pincode.regex' => 'Pincode must be exactly 6 digits.',
            
            // Bank Details
            'bank_name.required' => 'Bank name is required.',
            'bank_name.string' => 'Bank name must be a valid text.',
            'bank_name.max' => 'Bank name must not exceed 255 characters.',
            
            'branch_name.required' => 'Branch name is required.',
            'branch_name.string' => 'Branch name must be a valid text.',
            'branch_name.max' => 'Branch name must not exceed 255 characters.',
            
            'holder_name.required' => 'Account holder name is required.',
            'holder_name.string' => 'Account holder name must be a valid text.',
            'holder_name.max' => 'Account holder name must not exceed 255 characters.',
            
            'account_number.required' => 'Account number is required.',
            'account_number.string' => 'Account number must be a valid text.',
            
            'confirm_account_number.required' => 'Account number confirmation is required.',
            'confirm_account_number.string' => 'Account number confirmation must be a valid text.',
            'confirm_account_number.same' => 'Account number confirmation does not match the account number.',
            
            'ifsc_code.required' => 'IFSC code is required.',
            'ifsc_code.string' => 'IFSC code must be a valid text.',
            
            'service_type.required' => 'Service type is required.',
            'service_type.exists' => 'Selected service type is invalid.',
            
            // File Uploads
            'aadhar_photo.image' => 'Aadhar photo must be an image file.',
            'aadhar_photo.mimes' => 'Aadhar photo must be a JPEG, JPG, or PNG file.',
            'aadhar_photo.max' => 'Aadhar photo size must not exceed 2MB.',
            
            'pan_photo.image' => 'PAN photo must be an image file.',
            'pan_photo.mimes' => 'PAN photo must be a JPEG, JPG, or PNG file.',
            'pan_photo.max' => 'PAN photo size must not exceed 2MB.',

            
            'passbook_photo.image' => 'Passbook photo must be an image file.',
            'passbook_photo.mimes' => 'Passbook photo must be a JPEG, JPG, or PNG file.',
            'passbook_photo.max' => 'Passbook photo size must not exceed 2MB.',
            
            'gst_number.string' => 'GST number must be a valid text.',
            'gst_number.max' => 'GST number must be exactly 15 characters.',
            'gst_number.regex' => 'GST number format is invalid. Please enter a valid 15-character GST number.',
            
            'gst_certificate.required' => 'GST certificate is required when GST number is provided.',
            'gst_certificate.image' => 'GST certificate must be an image file.',
            'gst_certificate.mimes' => 'GST certificate must be a JPEG, JPG, or PNG file.',
            'gst_certificate.max' => 'GST certificate size must not exceed 2MB.',

            'pan_number.unique' => 'This PAN number is already registered. Please use a different PAN number.',
            'aadhar_number.unique' => 'This Aadhar number is already registered. Please use a different Aadhar number.',
            'gst_number.unique' => 'This GST number is already registered. Please use a different GST number.',
        ]);

        // Validate verification code against master code
        $masterCode = MasterCode::where('code',$request->verification_code)->first();
        
        if (!$masterCode || $masterCode->code !== $request->verification_code) {
            $employeeCode = User::where('Emp_Id', $request->verification_code)->first();
            if (!$employeeCode || $employeeCode->Emp_Id != $request->verification_code) {

                return redirect()->back()
                ->withErrors(['verification_code' => 'Invalid verification code. Please contact your administrator.'])
                ->withInput();
            } else {
                $parentId = $employeeCode->id;
            }
        } else {
            $parentId = '';
        }

        // Handle file uploads before transaction (file operations aren't transactional)
        $aadharPhotoPath = null;
        $panPhotoPath = null;
        $passbookPhotoPath = null;
        $gstCertificatePath = null;

        try {
            if ($request->hasFile('aadhar_photo')) {
                $aadharPhotoPath = $request->file('aadhar_photo')->store('uploads/bankdata/aadhar', 'public');
            }

            if ($request->hasFile('pan_photo')) {
                $panPhotoPath = $request->file('pan_photo')->store('uploads/bankdata/pan', 'public');
            }

            if ($request->hasFile('passbook_photo')) {
                $passbookPhotoPath = $request->file('passbook_photo')->store('uploads/bankdata/passbook', 'public');
            }

            if ($request->hasFile('gst_certificate')) {
                $gstCertificatePath = $request->file('gst_certificate')->store('uploads/bankdata/gst', 'public');
            }

            // Wrap all database operations in a transaction
            DB::beginTransaction();

            try {
                $user = User::create([
                    'first_name' => $request->first_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'pan_number' => $request->pan_number,
                    'aadhar_number' => $request->aadhar_number,
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'landmark' => $request->landmark,
                    'state' => $request->state,
                    'district' => $request->district,
                    'pincode' => $request->pincode,
                    'bank_name' => $request->bank_name,
                    'branch_name' => $request->branch_name,
                    'holder_name' => $request->holder_name,
                    'account_number' => $request->account_number,
                    'ifsc_code' => $request->ifsc_code,
                    'service_type' => $request->service_type,
                    'gst_number' => $request->gst_number ? strtoupper($request->gst_number) : null,
                    'gest_certificate' => $gstCertificatePath,
                    'user_type' => 'channel',
                    'status' => 1,
                    'Emp_Id' => $request->state.'_'.$request->district.'_'.$request->first_name,
                    'remember_token' => Str::random(20),

                ]);

                $user->update(['Emp_Id'=> generateEmployeeCode($request->state, $request->district, $request->first_name, $user->id)]);

        
                $channelRole = Role::where('name', 'Channel')->first();
                $associateRole = Role::where('name', 'Associate_Channel')->first();
                $signupAllowedRoles = ['Channel', 'Associate_Channel'];

                $roleToAssign = !empty($masterCode) ? $channelRole : $associateRole;
                if (!$roleToAssign || !in_array($roleToAssign->name, $signupAllowedRoles, true)) {
                    throw new \Exception('Invalid signup role configuration. Please contact administrator.');
                }

                $user->roles()->sync([$roleToAssign->id]);


                $bankData = new BankData();
                $bankData->user_id = $user->id;
                $bankData->bank_name = $request->bank_name;
                $bankData->branch_name = $request->branch_name;
                $bankData->account_number = $request->account_number;
                $bankData->holder_name = $request->holder_name;
                $bankData->ifsc_code = $request->ifsc_code;
                $bankData->pan_number = $request->pan_number;
                $bankData->aadhar_number = $request->aadhar_number;
                $bankData->aadhar_photo = $aadharPhotoPath;
                $bankData->pan_photo = $panPhotoPath;
                $bankData->passbook_photo = $passbookPhotoPath;
                $bankData->is_default = 1; // Set as default bank account
                $bankData->status = 1; // Active status

                $bankData->save();

                //Save associate channel
                if ($parentId) {
                $associateChannel = ChannelUser::create([
                        'channel_id' => $parentId,
                        'associate_channel_id' => $user->id
                    ]);
                }

                // Commit the transaction if everything succeeds
                DB::commit();

                // Send welcome email with terms & conditions and verification link
                try {
                    $verificationLink = url('/verify-email?token=' . Crypt::encryptString($user->remember_token));
                    Mail::to($user->email)->send(new WelcomeMail($user, $verificationLink));
                } catch (\Exception $e) {
                    \Log::error('Failed to send welcome email', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
                

                flash()
                    ->success('Registration successful! Please check your email for terms & conditions and verification link.')
                    ->flash();

                // Redirect to login page
                return redirect('/');
            } catch (\Exception $e) {
                // Rollback the transaction on any error
                DB::rollBack();

                // Clean up uploaded files if transaction fails
                if ($aadharPhotoPath && Storage::disk('public')->exists($aadharPhotoPath)) {
                    Storage::disk('public')->delete($aadharPhotoPath);
                }
                if ($panPhotoPath && Storage::disk('public')->exists($panPhotoPath)) {
                    Storage::disk('public')->delete($panPhotoPath);
                }
                if ($passbookPhotoPath && Storage::disk('public')->exists($passbookPhotoPath)) {
                    Storage::disk('public')->delete($passbookPhotoPath);
                }
                if ($gstCertificatePath && Storage::disk('public')->exists($gstCertificatePath)) {
                    Storage::disk('public')->delete($gstCertificatePath);
                }

                throw $e; // Re-throw to be caught by outer catch block
            }
        } catch (\Exception $e) {

            \Log::error('User registration failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            flash()
                ->error($e->getMessage())
                ->flash();
            
            return redirect()->back()->withInput();
        }
    }

    public function showVerifyOtp()
    {
        $user_id = session('user_id');
        $email = session('email');

        // If no user_id in session, check if it's in the request
        if (!$user_id && request()->has('user_id')) {
            $user_id = request()->get('user_id');
            $email = request()->get('email');
        }

        if (!$user_id || !$email) {
            flash()
                ->error('Invalid verification link. Please login or register again.')
                ->flash();
            return redirect('/');
        }

        // Get user to check OTP status
        $user = User::find($user_id);
        if (!$user || $user->email !== $email) {
            flash()
                ->error('Invalid user information. Please login again.')
                ->flash();
            return redirect('/');
        }

        // Check if OTP exists and is valid
        $hasValidOtp = $user->otp && $user->otp_expires_at && Carbon::now()->lessThanOrEqualTo($user->otp_expires_at);
        
        // If no valid OTP exists, generate and send a new one
        if (!$hasValidOtp) {
            // Generate new OTP
            $otp = generateOTP();
            $otpExpiresAt = Carbon::now()->addMinutes(10);
            
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt
            ]);
            
            // Send OTP email
            try {
                Mail::to($user->email)->send(new OtpVerificationMail($user, $otp));
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return view('Auth.verify-otp', compact('user_id', 'email'));
    }

    public function verifyOtp(Request $request)
    {
        
        $request->validate([
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::findOrFail($request->user_id);
       

        // Verify email matches user
        if ($user->email !== $request->email) {
            return redirect()->back()
                ->withErrors(['email' => 'Email does not match the registered user.'])
                ->withInput();
        }

        // Refresh user to get latest OTP data
        $user->refresh();

        // Check if OTP exists and is not expired
        if (!$user->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'OTP not found. Please request a new one.'])
                ->withInput();
        }
        

        // Check if OTP has expired
        // if (Carbon::now()->greaterThan($user->otp_expires_at)) {
        //     return redirect()->back()
        //         ->withErrors(['otp' => 'OTP has expired. Please request a new one.'])
        //         ->withInput();
        // }

        // Verify OTP
       
        if ($user->otp !== $request->otp) {
            return redirect()->back()
                ->withErrors(['otp' => 'Invalid OTP. Please try again.'])
                ->withInput();
        }

        // OTP verified successfully - clear OTP from database
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'remember_token' => null,
            'status' => 1,
        ]);

       
        // Auto login the user
        Auth::login($user);

        // Clear session data
        session()->forget(['user_id', 'email']);

        flash()
            ->success('Email verified successfully! Welcome to ' . env('APP_NAME') . '.')
            ->flash();

        // Redirect based on user type (similar to login logic)
        $subdomain = explode('.', $request->getHost())[0];
        switch ($subdomain) {
            case 'admin':
                $type = ['admin', 'staff'];
                break;
            case 'parker':
                $type = ['admin', 'staff', 'channel', 'sales','Associate_Channel', 'Maker', 'Checker'];
                break;
            case 'partner':
                $type = ['channel','Associate_Channel', 'Maker', 'Checker'];
                break;
            case 'sales-team':
                $type = ['sales'];
                break;
            default:
                $type = ['admin', 'staff', 'channel', 'sales','Associate_Channel', 'Maker', 'Checker'];
                break;
        }

        if (in_array($user->user_type, $type)) {
            return redirect('/application');
        } else {
            Auth::logout();
            flash()
                ->error('Account access denied.')
                ->flash();
            return redirect('/');
        }
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::findOrFail($request->user_id);

        // Verify email matches user
        if ($user->email !== $request->email) {
            return response()->json([
                'success' => false,
                'message' => 'Email does not match the registered user.'
            ], 400);
        }

        // Generate new OTP
        $otp = generateOTP();
        $otpExpiresAt = Carbon::now()->addMinutes(10);

        // Store OTP in database
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $otpExpiresAt
        ]);

        // Send OTP email
        try {
            Mail::to($user->email)->send(new OtpVerificationMail($user, $otp));

            return response()->json([
                'success' => true,
                'message' => 'OTP has been resent to your email.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to resend OTP email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again later.'
            ], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        try {
            // Decrypt the token
            $decryptedData = Crypt::decryptString($request->token);            
            
            if (!$decryptedData) {
                flash()
                    ->error('Invalid verification link.')
                    ->flash();
                return redirect('/');
            } 
            // Find the user
            $user = User::where('remember_token', $decryptedData)
                        ->first();
            if (!$user) {
                flash()
                    ->error('Invalid verification link. User not found.')
                    ->flash();
                return redirect('/');
            }

            // Check if user is already verified (both OTP fields are null means verified)
            // But we still allow them to request a new OTP if they want to re-verify
            // Only skip if they explicitly have been verified before
            $isAlreadyVerified = ($user->otp === null && $user->otp_expires_at === null);
            
            // Generate OTP with 10 minutes validity
            $otp = generateOTP();
            $otpExpiresAt = Carbon::now()->addMinutes(10);
            
            // Store OTP in database
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'remember_token' => null
            ]);
            
            // Send OTP email
            try {
                Mail::to($user->email)->send(new OtpVerificationMail($user, $otp));
            } catch (\Exception $e) {
                \Log::error('Failed to send OTP email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                flash()
                    ->error('Failed to send OTP. Please try again later.')
                    ->flash();
                return redirect('/');
            }

            // Store user info in session for OTP verification
            session(['user_id' => $user->id, 'email' => $user->email]);

            if ($isAlreadyVerified) {
                flash()
                    ->info('A new OTP has been sent to your email. Please verify it within 10 minutes.')
                    ->flash();
            } else {
                flash()
                    ->success('Verification link clicked successfully! We\'ve sent an OTP to your email. Please verify it within 10 minutes.')
                    ->flash();
            }
            
            return redirect()->route('verify-otp');
        } catch (\Exception $e) {
            \Log::error('Email verification failed', [
                'error' => $e->getMessage(),
                'token' => $request->token
            ]);

            flash()
                ->error('Invalid verification link. Please contact support.')
                ->flash();
            return redirect('/');
        }
    }
}
