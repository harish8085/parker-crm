<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{

    public function index()
    {
        $Route = 'Dashboard';
        $states = getState();

        $user_id = Auth::id();
        $user = User::findOrFail($user_id);
        $banks = BankData::where('user_id', $user->id)->get();

        $districts = getState();
        foreach ($states as $state) {
            if ($state['state_code'] === $user->state) {
                $districts =  $state['districts'];
            }
        }
        return view('Frontend.Profile.index', compact('Route', 'user', 'states', 'districts', 'banks'));
    }

    public function update(Request $request)
    {
        $Route = 'Dashboard';
        $user_id = Auth::id();

        // Validate the form data
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => [
                'required',
                'email',
                Rule::unique('users')->ignore($user_id),
            ],
            'phone'          => [
                'required',
                'string',
                'max:10',
                Rule::unique('users')->ignore($user_id),
            ],
            'state'          => 'required|string|max:255',
            'district'       => 'required|string|max:255',
            'pan_number'     => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user_id),
            ],
            'aadhar_number'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user_id),
            ],
            'address_1'        => 'required|string|max:255',
            'address_2'        => 'required|string|max:255',
            'landmark'        => 'required|string|max:255',
            'pincode'        => 'required|string|max:255',
        ]);

        $user = User::findOrFail($user_id);
        $user->first_name     = $request->first_name;
        $user->last_name      = $request->last_name;
        $user->email          = $request->email;
        $user->phone          = $request->phone;
        $user->address_1      = $request->address_1;
        $user->address_2      = $request->address_2;
        $user->landmark       = $request->landmark;
        $user->pincode        = $request->pincode;
        $user->state          = $request->state;
        $user->district       = $request->district;
        $user->pan_number     = $request->pan_number;
        $user->aadhar_number  = $request->aadhar_number;
        $user->save();
        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function updateBank(Request $request)
    {
        $user_id = Auth::id();
        BankData::where('user_id', $user_id)->delete();
        // Loop through the submitted bank detail inputs
        foreach ($request->bank_name as $index => $bankName) {
            // Create bank data for each set of bank details

            $account_check = BankData::where('account_number', $request->account_number[$index])->get();
            if ($account_check->isEmpty()) {
                $bank = BankData::create([
                    'user_id'        => $user_id,
                    'bank_name'      => $bankName,
                    'branch_name'    => $request->branch_name[$index],
                    'account_number' => $request->account_number[$index],
                    'holder_name'    => $request->holder_name[$index], // Assuming you have holder_name field
                    'ifsc_code'      => $request->ifsc_code[$index],
                ]);
            }
        }

        return response()->json(['success' => 'Profile updated successfully', 'code' => 200]);
    }

    public function addBank(Request $request)
    {
        $user_id = Auth::id();
        $validatedData = $request->validate([
            'bank_name'       => 'required|string|max:255',
            'branch_name'     => 'required|string|max:255',
            'account_number'  => 'required|string|max:255',
            'account_number'  => 'required|string|max:255',
            'ifsc_code'       => 'required|string|max:255',

        ]);
        $account_check = BankData::where('account_number', $request->account_number)->get();
        if ($account_check->isEmpty()) {
            $bank = BankData::create([
                'user_id'        => $request->user_id,
                'bank_name'      => $request->bank_name,
                'branch_name'    => $request->branch_name,
                'account_number' => $request->account_number,
                'holder_name'    => $request->holder_name, // Assuming you have holder_name field
                'ifsc_code'      => $request->ifsc_code,
            ]);
        }else{
            return response()->json(['error' => 'Account already exist', 'code' => 201]);

        }


        return response()->json(['success' => 'Profile updated successfully', 'code' => 200]);
    }


    public function updatePassword(Request $request)
    {
        $Route = 'Dashboard';
        $user_id = Auth::id();
        $user = Auth::user();

        $oldPassword = $request->old_password;
        $newPassword = $request->new_password;

        if (!Hash::check($oldPassword, $user->password)) {
            flash()
                ->error('Old password is incorrect')
                ->flash();
            return redirect()->back();
        }

        User::where('id', $user_id)->update([
            'password' => Hash::make($newPassword)
        ]);
        flash()
            ->success('Password updated successfully')
            ->flash();
        return redirect()->back();
    }
}
