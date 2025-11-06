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
use App\Models\ChannelUser;
use App\Models\BankData;

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

        if (!empty($request)) {
            $email = $request->email;
            $password = $request->password;


            if (!empty($email) && !empty($password)) {

                // Determine the subdomain and user type
                $subdomain = explode('.', $request->getHost())[0];
                switch ($subdomain) {
                    case 'admin':
                        $type = ['admin', 'staff']; // Search for both admin and staff types
                        break;
                    case 'parker':
                        $type = ['admin', 'staff','channel','sales'];
                        break;
                    case 'partner':
                        $type = ['channel'];
                        break;
                    case 'sales-team':
                        $type = ['sales'];
                        break;
                    default:
                        $type = ['admin', 'staff','channel','sales'];
                        break;
                }
                $userdata = array(
                    'email' => $email,
                    'password' => $password,
                );

                if (Auth::attempt($userdata)) {
                    $user = Auth::user();

                    if (in_array($user->user_type, $type)) {
                        if ($request->has('remember') == null) {
                            setcookie('email', $email, 100);
                            setcookie('password', $password, 100);
                        } else {

                            setcookie('email', $email, time() + 606024100);
                            setcookie('password', $password, time() + 606024100);
                        }
                        session('Login', true);
                        flash()
                            ->success('Logged In successfully.')
                            ->flash();
                        return redirect('/application');
                    } else {
                        Session::flush();
                        Auth::logout();
                        flash()
                            ->error('Account does not exists.')
                            ->flash();
                        return redirect('/');
                    }
                } else {
                    return redirect()->back()->with('error', "Invalid Credential");
                }
            } else {
                return redirect()->back()->with('error', "Invalid Request");
            }
        } else {
            return redirect()->back()->with('error', "Invalid Request");
        }
        return view("Auth.Login");
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
        $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|min:10|max:10|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'pan_number' => 'required|string',
            'aadhar_number' => 'required|string',
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
            $parentId = $masterCode->user_id;
        }

        // Handle file uploads before transaction (file operations aren't transactional)
        $aadharPhotoPath = null;
        $panPhotoPath = null;
        $passbookPhotoPath = null;

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
                    'user_type' => 'channel',
                    'status' => 1,
                    'Emp_Id' => $request->state.'_'.$request->district.'_'.$request->first_name

                ]);

                $user->update(['Emp_Id'=> generateEmployeeCode($request->state, $request->district, $request->first_name, $user->id)]);

                if (!empty($masterCode)) {
                    $user->roles()->sync([2]);
                } else {
                    $user->roles()->sync([6]);
                }

                $bankData = new BankData();
                $bankData->user_id = $user->id;
                $bankData->bank_name = $request->bank_name;
                $bankData->branch_name = $request->branch_name;
                $bankData->account_number = $request->account_number;
                $bankData->holder_name = $request->holder_name;
                $bankData->ifsc_code = $request->ifsc_code;
                $bankData->aadhar_photo = $aadharPhotoPath;
                $bankData->pan_photo = $panPhotoPath;
                $bankData->passbook_photo = $passbookPhotoPath;
                $bankData->is_default = 1; // Set as default bank account
                $bankData->status = 1; // Active status
                $bankData->save();

                //Save associate channel
                $associateChannel = ChannelUser::create([
                    'channel_id' => $parentId,
                    'associate_channel_id' => $user->id
                ]);

                // Commit the transaction if everything succeeds
                DB::commit();

                flash()
                    ->success('Registration successful! Please login with your credentials.')
                    ->flash();
                
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
                ->error('Registration failed. Please try again.')
                ->flash();
            
            return redirect()->back()->withInput();
        }
    }
}
