<?php

namespace App\Http\Controllers\Staff;

use App\Exports\StaffExport;
use App\Http\Controllers\Controller;
use App\Models\BankData;
use App\Models\Role;
use App\Models\StaffAssign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class StaffController extends Controller
{
    // show staff
    public function index(Request $request)
    {
        $Route = 'Manage Staff';
        $roleIds = [1, 2, 3];
        $staff = User::whereDoesntHave('roles', function ($query) use ($roleIds) {
            $query->whereIn('id', $roleIds);
        })->with('roles')->get();
        if ($request->ajax()) {

            $query = User::whereDoesntHave('roles', function ($q) use ($roleIds) {
                $q->whereIn('id', $roleIds);
            })->with('roles');

            if ($request->date) {
                $now = Carbon::now();
                if ($request->date == 'today') {
                    $today = Carbon::today()->toDateString();
                    $query = $query->whereDate('created_at', $today);
                } elseif ($request->date == 'yesterday') {
                    $yesterday = Carbon::yesterday()->toDateString();
                    $query = $query->whereDate('created_at', $yesterday);
                } elseif ($request->date == 'this_week') {
                    $weekStartDate = $now->startOfWeek()->toDateString();
                    $weekEndDate = $now->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $weekStartDate)
                        ->whereDate('created_at', '<=', $weekEndDate);
                } elseif ($request->date == 'last_week') {
                    $subWeek = $now->subWeek();
                    $lastWeekStartDate = $subWeek->startOfWeek()->toDateString();
                    $lastWeekEndDate = $subWeek->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastWeekStartDate)
                        ->whereDate('created_at', '<=', $lastWeekEndDate);
                } elseif ($request->date == 'this_month') {
                    $startOfMonth = $now->startOfMonth()->toDateString();
                    $endOfMonth = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                        ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_month') {
                    $subMonth = $now->subMonth();
                    $startOfMonth = $subMonth->startOfMonth()->toDateString();
                    $endOfMonth = $subMonth->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                        ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_3_months') {
                    $thirdLastMonthStart = $now->subMonths(2)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thirdLastMonthStart)
                        ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'last_6_months') {
                    $Last6thMonthStart = $now->subMonths(5)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $Last6thMonthStart)
                        ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'this_year') {
                    $thisYearStart = $now->startOfYear()->toDateString();
                    $thisYearEnd = $now->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thisYearStart)
                        ->whereDate('created_at', '<=', $thisYearEnd);
                } elseif ($request->date == 'last_year') {
                    $lastYear = $now->subYear();
                    $lastYearStart = $lastYear->startOfYear()->toDateString();
                    $lastYearEnd = $lastYear->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastYearStart)
                        ->whereDate('created_at', '<=', $lastYearEnd);
                } elseif ($request->date == 'custom' && isset($request->date_range)) {
                    if (strpos($request->date_range, 'to') !== false) {
                        $dates = explode('to', $request->date_range);
                        $startDate = trim($dates[0]);
                        $endDate = trim($dates[1]);
                        $query = $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    } else {
                        throw new \Exception('Date range is not provided or is incorrectly formatted.');
                    }
                }
            }

            if ($request->name) {
                $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('Emp_Id', function ($row) {
                    return $row->Emp_Id ? $row->Emp_Id : '-';
                })
                ->editColumn('name', function ($row) {
                    return $row->first_name ? $row->first_name . ' ' . $row->last_name : '-';
                })
                ->editColumn('email', function ($row) {
                    return $row->email ? $row->email : '-';
                })
                ->editColumn('phone', function ($row) {
                    return $row->phone ? $row->phone : '-';
                })
                ->editColumn('user_type', function ($row) {
                    return $row->user_type ? $row->user_type : '-';
                })
                ->editColumn('status', function ($row) {
                    $status = "<button class='table-status-btn " . ($row->status ? 'completed' : 'rejected') . "'> " . ($row->status ? 'Active' : 'In-Active') . "</button>";
                    return $status;
                })

                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('application', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/staff/create/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('application', 'update')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/staff/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    // if (auth()->user()->hasPermission('application', 'delete')) {

                    //     $btn .= "<img class='delete-btn' data-application-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                    // }
                    return $btn;
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        // Pass the staff members data to the view
        return view('Frontend.Staff.manage-staff.index', compact('Route', 'staff'));
    }

    public function create()
    {
        $Route = 'Add Staff';
        // Retrieve all staff members
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $states = getState();

        $channelRoleId = [2];
        $channels = User::where('status', 1)->whereHas('roles', function ($query) use ($channelRoleId) {
            $query->whereIn('id', $channelRoleId);
        })->with('roles')->get();

        $salesRoleId = [3];
        $sales = User::where('status', 1)->whereHas('roles', function ($query) use ($salesRoleId) {
            $query->whereIn('id', $salesRoleId);
        })->with('roles')->get();

        // Pass the staff members data to the view
        return view('Frontend.Staff.manage-staff.create', compact('Route', 'roles', 'states', 'channels', 'sales'));
    }

    public function exportStaff()
    {
        $type = 'staff';
        return Excel::download(new StaffExport($type), 'staff.xlsx');
    }

    public function view($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $user->id)->first();
        $states = getState();
        $districts = getState();
        foreach ($states as $state) {
            if ($state['state_code'] === $user->state) {
                $districts =  $state['districts'];
            }
        }

        $channelRoleId = [2];
        $channels = User::where('status', 1)->whereHas('roles', function ($query) use ($channelRoleId) {
            $query->whereIn('id', $channelRoleId);
        })->with('roles')->get();

        $salesRoleId = [3];
        $sales = User::where('status', 1)->whereHas('roles', function ($query) use ($salesRoleId) {
            $query->whereIn('id', $salesRoleId);
        })->with('roles')->get();
        $assignedUser = StaffAssign::where('user_id', $id)->value('channel_sales_id');
        $assignedUser =  json_decode($assignedUser, true);
        // Ensure it's an array before passing to view (to avoid issues with `in_array`)
        if (!is_array($assignedUser)) {
            $assignedUser = [];
        }
        // Pass the staff member data to the edit view
        return view('Frontend.Staff.manage-staff.show', compact('Route', 'user', 'roles', 'states', 'districts', 'bank', 'channels', 'sales', 'assignedUser'));
    }

    public function store(Request $request)
    {

        // Validate the form data
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:10|unique:users,phone',
            // 'state'          => 'required|string|max:255',
            // 'district'       => 'required|string|max:255',
            // 'pan_number'     => 'required|string|max:255|unique:users,pan_number',
            // 'aadhar_number'  => 'required|string|max:255|unique:users,aadhar_number',
            // 'address_1'      => 'required|string|max:255',
            // 'address_2'      => 'required|string|max:255',
            // 'landmark'       => 'required|string|max:255',
            // 'pincode'        => 'required|numeric',
            // 'bank_name'      => 'required|string|max:255',
            // 'branch_name'    => 'required|string|max:255',
            // 'holder_name'    => 'required|string|max:255',
            // 'account_number' => 'required|numeric',
            // 'ifsc_code'      => 'required|string|max:255',
            'status'         => 'required|boolean',
            'role'           => 'required|exists:roles,id',
            'password'       => 'required|string|min:8',
        ]);

        // Create a new staff member
        $user = new User();
        $user->Emp_Id         = generateStaffID($request->first_name);
        $user->first_name     = $request->first_name;
        $user->last_name      = $request->last_name;
        $user->email          = $request->email;
        $user->phone          = $request->phone;
        // $user->address_1      = $request->address_1;
        // $user->address_2      = $request->address_2;
        // $user->landmark       = $request->landmark;
        // $user->pincode        = $request->pincode;
        // $user->state          = $request->state;
        // $user->district       = $request->district;
        // $user->pan_number     = $request->pan_number;
        // $user->aadhar_number  = $request->aadhar_number;
        $user->status         = $request->status;
        $user->password       = bcrypt($request->password); // Hash the password for security
        $user->save();

        $user->roles()->attach($request->role);
        $staffAssign = new StaffAssign();

        $staffAssign->channel_sales_id = json_encode($request->access_id, true);
        $staffAssign->user_id = $user->id;
        $staffAssign->save();
        // $bankData = new BankData();
        // $bankData->user_id = $user->id;
        // $bankData->bank_name = $request->bank_name;
        // $bankData->holder_name = $request->holder_name;
        // $bankData->branch_name = $request->branch_name;
        // $bankData->account_number = $request->account_number;
        // $bankData->ifsc_code = $request->ifsc_code;
        // $bankData->save();

        // Redirect back with a success message
        return redirect()->to('/staff')->with('success', 'Staff member added successfully.');
    }

    public function edit($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $user->id)->first();
        $states = getState();
        $districts = getState();
        $assignedUser = StaffAssign::where('user_id', $id)->value('channel_sales_id');
        $assignedUser =  json_decode($assignedUser, true);
        foreach ($states as $state) {
            if ($state['state_code'] === $user->state) {
                $districts =  $state['districts'];
            }
        }
        $channelRoleId = [2];
        $channels = User::where('status', 1)->whereHas('roles', function ($query) use ($channelRoleId) {
            $query->whereIn('id', $channelRoleId);
        })->with('roles')->get();

        $salesRoleId = [3];
        $sales = User::where('status', 1)->whereHas('roles', function ($query) use ($salesRoleId) {
            $query->whereIn('id', $salesRoleId);
        })->with('roles')->get();

        $assignedUser = StaffAssign::where('user_id', $id)->value('channel_sales_id');
        $assignedUser =  json_decode($assignedUser, true);
        // Ensure it's an array before passing to view (to avoid issues with `in_array`)
        if (!is_array($assignedUser)) {
            $assignedUser = [];
        }
        // Pass the staff member data to the edit view
        return view('Frontend.Staff.manage-staff.edit', compact('Route', 'user', 'roles', 'states', 'districts', 'bank', 'channels', 'sales', 'assignedUser'));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'email'          => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'phone'          => [
                'required',
                'string',
                'max:10',
                Rule::unique('users')->ignore($id),
            ],
            // 'state'          => 'required|string|max:255',
            // 'district'       => 'required|string|max:255',
            // 'pan_number'     => [
            //     'required',
            //     'string',
            //     'max:255',
            //     Rule::unique('users')->ignore($id),
            // ],
            // 'aadhar_number'  => [
            //     'required',
            //     'string',
            //     'max:255',
            //     Rule::unique('users')->ignore($id),
            // ],
            // 'address_1'      => 'required|string|max:255',
            // 'address_2'      => 'required|string|max:255',
            // 'landmark'       => 'required|string|max:255',
            // 'pincode'        => 'required|numeric',
            'status'         => 'required|boolean',
            'role'           => 'required|exists:roles,id',
            // 'bank_name'      => 'required|string|max:255',
            // 'branch_name'    => 'required|string|max:255',
            // 'account_number' =>  [
            //     'required',
            //     'numeric',
            // ],
            // 'ifsc_code'      => 'required|string|max:255',
            // 'holder_name'    => 'required|string|max:255',

        ]);
        // Retrieve the staff member by ID
        $data = BankData::where('user_id', $id)->first();
        $user = User::findOrFail($id);
        // $bank = BankData::findOrFail($data->id);
        $assignedUser = StaffAssign::where('user_id', $id)->update([
            'channel_sales_id' => json_encode($request->access_id, true)
        ]);

        // Update the staff member's details
        $user->first_name     = $request->first_name;
        $user->last_name      = $request->last_name;
        $user->email          = $request->email;
        $user->phone          = $request->phone;
        $user->state          = $request->state;
        $user->district       = $request->district;
        $user->status         = $request->status;
        // $user->pan_number     = $request->pan_number;
        // $user->aadhar_number  = $request->aadhar_number;
        // $user->address_1      = $request->address_1;
        // $user->address_2      = $request->address_2;
        // $user->landmark       = $request->landmark;
        // $user->pincode        = $request->pincode;
        $user->service_type   = $request->service_type;
        // $user->percentage     = $request->percentage;
        // $bank->holder_name    = $request->holder_name;
        // $bank->branch_name    = $request->branch_name;
        // $bank->bank_name      = $request->bank_name;
        // $bank->ifsc_code      = $request->ifsc_code;
        // $check_acc_no = BankData::where('account_number', $request->account_number)
        //     ->where('user_id', '!=', $id)
        //     ->exists();
        // if ($check_acc_no) {
        //     return redirect()->back()->withErrors(['account_number' => 'Account number is not unique.'])->withInput();
        // } else {
        //     $bank->account_number = $request->account_number;
        // }

        // Update the password if provided
        if ($request->password) {
            $user->password = bcrypt($request->password); // Hash the password for security
        }


        $user->save();
        // $bank->save();
        $user->roles()->sync([$request->role]); // Use sync to replace existing roles with the new one

        // Redirect back with a success message
        return redirect()->to('/staff')->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $user)
    {

        StaffAssign::where('user_id', $user->id)->delete();
        $user->roles()->detach(); // Remove all roles assigned to the user
        $user->delete();
        $user->bankData()->delete();
        flash()
            ->success('Staff member deleted successfully')
            ->flash();

        return response()->json(['success' => true]);
    }
}
