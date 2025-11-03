<?php

namespace App\Http\Controllers\User;

use App\Exports\StaffExport;
use App\Http\Controllers\Controller;
use App\Models\BankData;
use App\Models\Role;
use App\Models\Service;
use App\Models\StaffAssign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Stringable;
use Carbon\Carbon;

use Yajra\DataTables\Facades\DataTables;

class ChannelPartnerController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'manageChannels';
        $roleId = 2;
        $user = Auth::user();
        $channels = User::whereHas('roles', function ($query) use ($roleId) {
            $query->where('id', $roleId);
        });
        if ($user->roles[0]->id == 1) {
            $channels = User::whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
            if ($request->ajax()) {

                $query = User::whereHas('roles', function ($q) use ($roleId) {
                    $q->where('id', $roleId);
                });

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
                
                if ($request->channel_name) {
                        $query->where('first_name', $request->channel_name);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('Emp_Id', function ($row) {
                        return $row->Emp_Id ? $row->Emp_Id : '-';
                    })
                    ->editColumn('first_name', function ($row) {
                        return $row->first_name ? $row->first_name : '-';
                    })
                    ->editColumn('email', function ($row) {
                        return $row->email ? $row->email : '-';
                    })
                    ->editColumn('phone', function ($row) {
                        return $row->phone ? $row->phone : '-';
                    })
                    ->editColumn('status', function ($row) {
                        $status = "<button class='table-status-btn " . ($row->status ? 'completed' : 'rejected') . "'> " . ($row->status ? 'Active' : 'In-Active') . "</button>";
                        return $status;
                    })
                    
                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('channel', 'view')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('channel', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('channel', 'delete')) {
                            
                                $btn .= "<img class='delete-btn' data-channel-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })

                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            if ($channel_assign == null) {
                $channel_assign = '[]';
            }
            $channel_assign = json_decode($channel_assign, true);
            $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
            if ($request->ajax()) {

                $query = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($roleId) {
                    $query->where('id', $roleId);
                });

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
                
                if ($request->channel_name) {
                        $query->where('first_name', $request->channel_name);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('Emp_Id', function ($row) {
                        return $row->Emp_Id ? $row->Emp_Id : '-';
                    })
                    ->editColumn('channel_name', function ($row) {
                        return $row->first_name ? $row->first_name : '-';
                    })
                    ->editColumn('email', function ($row) {
                        return $row->email ? $row->email : '-';
                    })
                    ->editColumn('phone', function ($row) {
                        return $row->phone ? $row->phone : '-';
                    })
                    ->editColumn('status', function ($row) {
                        $status = "<button class='table-status-btn " . ($row->status ? 'completed' : 'rejected') . "'> " . ($row->status ? 'Active' : 'In-Active') . "</button>";
                        return $status;
                    })
                    
                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'view')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('application', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('application', 'delete')) {
                            
                                $btn .= "<img class='delete-btn' data-channel-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })

                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
        }

        return view('Frontend.Users.channel-partner.index', compact('Route', 'channels'));
    }

    public function add()
    {
        $Route = 'addChannels';
        $states = getState();
        $services = Service::get();

        return view('Frontend.Users.channel-partner.add', compact('Route', 'states', 'services'));
    }

    public function store(Request $request)
    {

        $Route = 'storeChannels';

        // Validate the request data
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:10|unique:users,phone',
            'state'          => 'required|string|max:255',
            'district'       => 'required|string|max:255',
            'pan_number'     => 'required|string|max:255|unique:users,pan_number',
            'aadhar_number'  => 'required|string|max:255|unique:users,aadhar_number',
            'service_type'   => 'required|string|max:255',
            'address_1'      => 'required|string|max:255',
            'address_2'      => 'required|string|max:255',
            'landmark'       => 'required|string',
            'pincode'        => 'required|numeric',
            'bank_name'      => 'required|string|max:255',
            'branch_name'    => 'required|string|max:255',
            'holder_name'    => 'required|string|max:255',
            'account_number' => 'required|numeric|unique:bank_data,account_number',
            'ifsc_code'      => 'required|string|max:255',
            'status'         => 'required|boolean',
            'password'       => 'required|string|min:8',
        ]);

        $roles = Role::where('id', 2)->get();

        // Create a new ChannelPartner instance
        $channelPartner = new User();

        $channelPartner->Emp_Id         = generateEmployeeID($request->state, $request->district, $request->first_name);
        $channelPartner->first_name     = $request->first_name;
        $channelPartner->last_name      = $request->last_name;
        $channelPartner->email          = $request->email;
        $channelPartner->phone          = $request->phone;
        $channelPartner->state          = $request->state;
        $channelPartner->district       = $request->district;
        $channelPartner->pan_number     = $request->pan_number;
        $channelPartner->aadhar_number  = $request->aadhar_number;
        $channelPartner->address_1      = $request->address_1;
        $channelPartner->address_2      = $request->address_2;
        $channelPartner->landmark       = $request->landmark;
        $channelPartner->pincode        = $request->pincode;
        $channelPartner->service_type   = $request->service_type;
        $channelPartner->status         = $request->status;
        $channelPartner->user_type      = 'channel';
        $channelPartner->password       = bcrypt($request->password); // Hash the password for security
        $channelPartner->save();

        $channelPartner->roles()->attach($roles);

        $bankData = new BankData();
        $bankData->user_id = $channelPartner->id;
        $bankData->bank_name = $request->bank_name;
        $bankData->branch_name = $request->branch_name;
        $bankData->account_number = $request->account_number;
        $bankData->holder_name = $request->holder_name;
        $bankData->ifsc_code = $request->ifsc_code;
        $bankData->save();


        if (Auth::user()->roles[0]->id == 2) {
            $userId = Auth::user()->id;
            $staffAssign = StaffAssign::where('user_id', $userId)->first();

            if ($staffAssign == null) {
                $data = [$channelPartner->id];

                $staffAssign = new StaffAssign();
                $staffAssign->channel_sales_id = json_encode($data);
                $staffAssign->user_id = $userId;
                $staffAssign->save();
            } else {
                $data = json_decode($staffAssign->channel_sales_id, true);
                array_push($data, $channelPartner->id);
                $staffAssign->channel_sales_id = json_encode($data);
                $staffAssign->save();
            }
        }


        return redirect()->to('/channel')->with('success', 'Channel Partner added successfully.');
    }

    public function exportChannel()
    {
        $type = 'channel';
        return Excel::download(new StaffExport($type), 'channel-partner.xlsx');
    }
    public function show($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $channelPartner = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $channelPartner->id)->first();
        $states = getState();
        $districts = getState();
        $services = Service::get();
        foreach ($states as $state) {
            if ($state['state_code'] === $channelPartner->state) {
                $districts =  $state['districts'];
            }
        }

        // Pass the staff member data to the show view

        return view('Frontend.Users.channel-partner.show', compact('Route', 'channelPartner', 'roles', 'states', 'districts', 'bank', 'services'));
    }
    public function edit($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $channelPartner = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $channelPartner->id)->first();
        $states = getState();
        $districts = getState();
        $services = Service::get();
        foreach ($states as $state) {
            if ($state['state_code'] === $channelPartner->state) {
                $districts =  $state['districts'];
            }
        }

        // Pass the staff member data to the edit view

        return view('Frontend.Users.channel-partner.edit', compact('Route', 'channelPartner', 'roles', 'states', 'districts', 'bank', 'services'));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
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
            'state'          => 'required|string|max:255',
            'district'       => 'required|string|max:255',
            'pan_number'     => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'aadhar_number'  => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->ignore($id),
            ],
            'service_type'   => 'required|string|max:255',
            'address_1'      => 'required|string|max:255',
            'address_2'      => 'required|string|max:255',
            'landmark'       => 'required|string',
            'pincode'        => 'required|numeric',
            'status'         => 'required|boolean',
            'bank_name'      => 'required|string|max:255',
            'branch_name'    => 'required|string|max:255',
            'holder_name' => 'required|string|max:255',
            'account_number' =>  [
                'required',
                'numeric',
            ],
            'ifsc_code'      => 'required|string|max:255',
        ]);
        // Retrieve the staff member by ID
        $data = BankData::where('user_id', $id)->first();
        $channelPartner = User::findOrFail($id);
        $bank = BankData::findOrFail($data->id);

        // Update the staff member's details
        $channelPartner->first_name     = $request->first_name;
        $channelPartner->email          = $request->email;
        $channelPartner->phone          = $request->phone;
        $channelPartner->address_1      = $request->address_1;
        $channelPartner->address_2      = $request->address_2;
        $channelPartner->landmark       = $request->landmark;
        $channelPartner->pincode        = $request->pincode;
        $channelPartner->service_type   = $request->service_type;
        $channelPartner->state          = $request->state;
        $channelPartner->district       = $request->district;
        $channelPartner->status         = $request->status;
        $channelPartner->pan_number     = $request->pan_number;
        $channelPartner->aadhar_number  = $request->aadhar_number;
        $bank->branch_name              = $request->branch_name;
        $bank->bank_name                = $request->bank_name;
        $bank->holder_name              = $request->holder_name;
        $bank->ifsc_code                = $request->ifsc_code;
        $check_acc_no = BankData::where('account_number', $request->account_number)
            ->where('user_id', '!=', $id)
            ->exists();
        if ($check_acc_no) {
            return redirect()->back()->withErrors(['account_number' => 'Account number is not unique.'])->withInput();
        } else {
            $bank->account_number      = $request->account_number;
        }

        // Update the password if provided
        if ($request->password) {
            $channelPartner->password = bcrypt($request->password); // Hash the password for security
        }


        $channelPartner->save();
        $bank->save();

        // Redirect back with a success message
        return redirect()->to('/channel')->with('success', 'Channel Partner updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->roles()->detach(); // Remove all roles assigned to the user
        $user->delete();
        $user->bankData()->delete();
        flash()
            ->success('Channel Partner deleted successfully ')
            ->flash();

        return response()->json(['success' => true]);
    }
}
