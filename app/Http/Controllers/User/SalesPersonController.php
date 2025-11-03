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
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SalesPersonController extends Controller
{
    public function index(Request $request)
    {

        $Route = 'manageSales';
        $roleId = 3;
        $user = Auth::user();
        if ($user->roles[0]->id == 1) {
            $sales = User::whereHas('roles', function ($query) use ($roleId) {
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
                
                if ($request->name) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
                }
                
                

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('Emp_Id', function ($row) {
                        return $row->Emp_Id ? $row->Emp_Id : '-';
                    })
                    ->editColumn('name', function ($row) {
                        return $row->name ? $row->first_name .' '. $row->last_name : '-';
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
                            
                                $btn .= "<img class='delete-btn' data-sale-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })

                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
            if ($request->ajax()) {

                $query = User::where('id', $user->id)->whereHas('roles', function ($query) use ($roleId) {
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
                
                if ($request->name) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
                }
                
                

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('Emp_Id', function ($row) {
                        return $row->Emp_Id ? $row->Emp_Id : '-';
                    })
                    ->editColumn('name', function ($row) {
                        return $row->name ? $row->first_name .' '. $row->last_name : '-';
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

                        if (auth()->user()->hasPermission('sales-person', 'view')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('sales-person', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/channel/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('sales-person', 'delete')) {
                            
                                $btn .= "<img class='delete-btn' data-sale-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })

                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
        }


        return view('Frontend.Users.sales-person.index', compact('Route', 'sales'));
    }

    public function add()
    {

        $Route = 'addChannels';
        $states = getState();
        $services = Service::get();
        return view('Frontend.Users.sales-person.add', compact('Route', 'states', 'services'));
    }

    public function store(Request $request)
    {

        $Route = 'storeSales';

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
            'landmark'       => 'required|string|max:255',
            'pincode'        => 'required|string|max:255',
            'holder_name'    => 'required|string|max:255',
            'bank_name'      => 'required|string|max:255',
            'branch_name'    => 'required|string|max:255',
            'account_number' => 'required|numeric|unique:bank_data,account_number',
            'ifsc_code'      => 'required|string|max:255',
            'status'         => 'required|boolean',
            'password'       => 'required|string|min:8',
        ]);

        $roles = Role::where('id', 3)->get();


        // Create a new ChannelPartner instance
        $salesPerson = new User();
        $salesPerson->Emp_Id         = generateEmployeeID($request->state, $request->district, $request->first_name);
        $salesPerson->first_name     = $request->first_name;
        $salesPerson->last_name      = $request->last_name;
        $salesPerson->email          = $request->email;
        $salesPerson->phone          = $request->phone;
        $salesPerson->state          = $request->state;
        $salesPerson->district       = $request->district;
        $salesPerson->pan_number     = $request->pan_number;
        $salesPerson->aadhar_number  = $request->aadhar_number;
        $salesPerson->address_1      = $request->address_1;
        $salesPerson->address_2      = $request->address_2;
        $salesPerson->landmark       = $request->landmark;
        $salesPerson->pincode        = $request->pincode;
        $salesPerson->service_type   = $request->service_type;
        $salesPerson->status         = $request->status;
        $salesPerson->user_type      = 'sales';
        $salesPerson->password       = bcrypt($request->password); // Hash the password for security
        $salesPerson->save();

        $salesPerson->roles()->attach($roles);

        $bankData = new BankData();
        $bankData->user_id = $salesPerson->id;
        $bankData->bank_name = $request->bank_name;
        $bankData->branch_name = $request->branch_name;
        $bankData->account_number = $request->account_number;
        $bankData->holder_name = $request->holder_name;
        $bankData->ifsc_code = $request->ifsc_code;
        $bankData->save();


        return redirect()->to('/sales-person')->with('success', 'Sales Person added successfully.');
    }
    public function exportSales()
    {
        $type = 'sales';
        return Excel::download(new StaffExport($type), 'sales-person.xlsx');
    }

    public function show($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $sale = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $sale->id)->first();
        $states = getState();
        $districts = getState();
        $services = Service::get();

        foreach ($states as $state) {
            if ($state['state_code'] === $sale->state) {
                $districts =  $state['districts'];
            }
        }

        // Pass the staff member data to the edit view
        return view('Frontend.Users.sales-person.show', compact('Route', 'sale', 'roles', 'states', 'districts', 'bank', 'services'));
    }
    public function edit($id)
    {
        $Route = 'Edit Staff';
        // Retrieve the staff member by ID
        $sale = User::with('roles')->findOrFail($id);
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        $bank = BankData::where('user_id', $sale->id)->first();
        $states = getState();
        $districts = getState();
        $services = Service::get();
        foreach ($states as $state) {
            if ($state['state_code'] === $sale->state) {
                $districts =  $state['districts'];
            }
        }

        // Pass the staff member data to the edit view
        return view('Frontend.Users.sales-person.edit', compact('Route', 'sale', 'roles', 'states', 'districts', 'bank', 'services'));
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
        $sales_person = User::findOrFail($id);
        $bank = BankData::findOrFail($data->id);

        // Update the staff member's details
        $sales_person->first_name     = $request->first_name;
        $sales_person->last_name      = $request->last_name;
        $sales_person->email          = $request->email;
        $sales_person->phone          = $request->phone;
        $sales_person->address_1      = $request->address_1;
        $sales_person->address_2      = $request->address_2;
        $sales_person->landmark       = $request->landmark;
        $sales_person->pincode        = $request->pincode;
        $sales_person->service_type   = $request->service_type;
        $sales_person->state          = $request->state;
        $sales_person->district       = $request->district;
        $sales_person->status         = $request->status;
        $sales_person->pan_number     = $request->pan_number;
        $sales_person->aadhar_number  = $request->aadhar_number;
        $bank->holder_name            = $request->holder_name;
        $bank->branch_name            = $request->branch_name;
        $bank->bank_name              = $request->bank_name;
        $bank->ifsc_code              = $request->ifsc_code;
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
            $sales_person->password = bcrypt($request->password); // Hash the password for security
        }


        $sales_person->save();
        $bank->save();

        // Redirect back with a success message
        return redirect()->to('/sales-person')->with('success', 'Sales Person updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->roles()->detach(); // Remove all roles assigned to the user
        $user->delete();
        $user->bankData()->delete();

        flash()
            ->success('Sales Person deleted successfully ')
            ->flash();

        return response()->json(['success' => true]);
    }
}
