<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankDataController extends Controller
{
    /**
     * Display a listing of the user's bank accounts.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $Route = 'Link Banks';
        $user = Auth::user();
        
        if ($request->ajax()) {
            $query = BankData::where('user_id', $user->id)->orderBy('id', 'desc');           

            if ($request->bank_name) {
                $query->where('bank_name', $request->bank_name);
            }

            if ($request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('account_holder_name', function ($row) {
                    return $row->holder_name ? $row->holder_name : '-';
                })
                ->editColumn('account_number', function ($row) {
                    return $row->account_number ? $row->account_number : '-';
                })
                ->editColumn('ifsc_code', function ($row) {
                    return $row->ifsc_code ? $row->ifsc_code : '-';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ? $row->bank_name : '-';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status == 1 ? 'checked' : '';
                    $status = '<label class="toggle-switch">
                        <input type="checkbox" class="status-toggle" data-bank-id="' . $row->id . '" ' . $checked . '>
                        <span class="toggle-slider"></span>
                    </label>';
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= "<a href='" . url('/link-bank/view/' . $row->id) . "' title='View Details' style='cursor: pointer; display: inline-block;'>";
                    $btn .= "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='color: #007bff;'>";
                    $btn .= "<path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle>";
                    $btn .= "</svg></a>";
                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        $banks = Bank::all();
        $bankNames = BankData::where('user_id', $user->id)->distinct()->pluck('bank_name');

        return view('Frontend.link-banks.index', compact('Route', 'banks'));
    }

    /**
     * Show the form for creating a new bank account.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $Route = 'Add Bank Account';
        $banks = Bank::all();
        return view('Frontend.link-banks.create', compact('Route', 'banks'));
    }

    /**
     * Store a newly created bank account in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'holder_name' => 'required|string|max:255',
            'account_number'      => 'required|string|max:64|regex:/^[0-9]+$/',
            'confirm_account_number' => 'required|string|max:64|regex:/^[0-9]+$/|same:account_number',
            'ifsc_code'           => 'required|string|max:32',
            'bank_name'           => 'required|string|max:255',
            'pan_photo'           => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'aadhar_photo'        => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'passbook_photo'      => 'required|image|mimes:jpeg,jpg,png|max:4096',
            'branch_name'         => 'required|string|max:255',
        ], [
            'account_number.regex' => 'The account number must contain only digits.',
            'confirm_account_number.regex' => 'The confirm account number must contain only digits.',
            'confirm_account_number.same' => 'The account number and confirm account number must match.',
            'pan_photo.mimes' => 'PAN photo must be a JPEG, JPG, or PNG image.',
            'aadhar_photo.mimes' => 'Aadhar photo must be a JPEG, JPG, or PNG image.',
            'passbook_photo.mimes' => 'Passbook photo must be a JPEG, JPG, or PNG image.',
        ]);

        $bankData = new BankData();
        $bankData->user_id = Auth::id();
        $bankData->holder_name = $request->holder_name;
        $bankData->account_number = $request->account_number;
        $bankData->ifsc_code = $request->ifsc_code;
        $bankData->bank_name = $request->bank_name;
        $bankData->branch_name = $request->branch_name;
        
        // Handle file uploads
        if ($request->hasFile('pan_photo')) {
            $bankData->pan_photo = $request->file('pan_photo')->store('uploads/bankdata/pan', 'public');
        }
        if ($request->hasFile('aadhar_photo')) {
            $bankData->aadhar_photo = $request->file('aadhar_photo')->store('uploads/bankdata/aadhar', 'public');
        }
        if ($request->hasFile('passbook_photo')) {
            $bankData->passbook_photo = $request->file('passbook_photo')->store('uploads/bankdata/passbook', 'public');
        }

        $bankData->status = 0; // Default inactive
        $bankData->save();

        return redirect()->route('link-bank.index')->with('success', 'Bank account added successfully! Awaiting activation.');
    }

    /**
     * Display the specified bank account details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $Route = 'View Bank Account';
        $bank = BankData::where('user_id', Auth::id())->findOrFail($id);

        return view('Frontend.link-banks.show', compact('Route', 'bank'));
    }

    /**
     * Show the form for editing the specified bank account.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $Route = 'Edit Bank Account';
        $bank = BankData::where('user_id', Auth::id())->findOrFail($id);

        return view('Frontend.link-banks.edit', compact('Route', 'bank'));
    }

    /**
     * Update the specified bank account in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $bankData = BankData::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'account_holder_name' => 'required|string|max:255',
            'account_number'      => 'required|string|max:64',
            'ifsc_code'           => 'required|string|max:32',
            'bank_name'           => 'required|string|max:255',
            'pan_photo'           => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:4096',
            'aadhar_photo'        => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:4096',
            'passbook_photo'      => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $bankData->account_holder_name = $request->account_holder_name;
        $bankData->account_number = $request->account_number;
        $bankData->ifsc_code = $request->ifsc_code;
        $bankData->bank_name = $request->bank_name;

        // Handle file uploads
        if ($request->hasFile('pan_photo')) {
            if(!empty($bankData->pan_photo)) {
                Storage::disk('public')->delete($bankData->pan_photo);
            }
            $bankData->pan_photo = $request->file('pan_photo')->store('uploads/bankdata/pan', 'public');
        }
        if ($request->hasFile('aadhar_photo')) {
            if(!empty($bankData->aadhar_photo)) {
                Storage::disk('public')->delete($bankData->aadhar_photo);
            }
            $bankData->aadhar_photo = $request->file('aadhar_photo')->store('uploads/bankdata/aadhar', 'public');
        }
        if ($request->hasFile('passbook_photo')) {
            if(!empty($bankData->passbook_photo)) {
                Storage::disk('public')->delete($bankData->passbook_photo);
            }
            $bankData->passbook_photo = $request->file('passbook_photo')->store('uploads/bankdata/passbook', 'public');
        }

        $bankData->save();

        return redirect()->route('link-bank.index')->with('success', 'Bank account updated successfully!');
    }

    /**
     * Activate the specified bank account.
     * Only one bank account can be active at a time for the user
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id)
    {
        $userId = Auth::id();

        // Deactivate all other bank accounts
        BankData::where('user_id', $userId)->update(['status' => 0]);
        // Activate this one
        $bank = BankData::where('user_id', $userId)->findOrFail($id);
        $bank->status = 1;
        $bank->save();

        return response()->json(['success' => true, 'message' => 'Bank account activated successfully.']);
    }

    /**
     * Inactivate the specified bank account.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate($id)
    {
        $userId = Auth::id();
        $bank = BankData::where('user_id', $userId)->findOrFail($id);
        $bank->status = 0;
        $bank->save();

        return response()->json(['success' => true, 'message' => 'Bank account deactivated.']);
    }

    /**
     * Remove the specified bank account from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destory($id)
    {
        $userId = Auth::id();
        $bank = BankData::where('user_id', $userId)->findOrFail($id);

        // Delete files if they exist
        if (!empty($bank->pan_photo)) {
            Storage::disk('public')->delete($bank->pan_photo);
        }
        if (!empty($bank->aadhar_photo)) {
            Storage::disk('public')->delete($bank->aadhar_photo);
        }
        if (!empty($bank->passbook_photo)) {
            Storage::disk('public')->delete($bank->passbook_photo);
        }

        $bank->delete();

        return response()->json(['success' => true, 'message' => 'Bank account deleted successfully.']);
    }

 
    
}
