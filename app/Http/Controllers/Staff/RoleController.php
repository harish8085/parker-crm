<?php

namespace App\Http\Controllers\Staff;

use App\Exports\RoleExport;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class RoleController extends Controller
{
    // show staff
    public function index(Request $request)
    {
        $Route = 'Manage Role';
        // Retrieve all staff members
        $roles = Role::whereNotIn('id', [1, 2, 3])->get();
        if ($request->ajax()) {

            $query = Role::whereNotIn('id', [1, 2, 3]);

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
                $query->where('name',$request->name);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('Emp_Id', function ($row) {
                    return $row->Emp_Id ? $row->Emp_Id : '-';
                })
                ->editColumn('name', function ($row) {
                    return $row->name ? $row->name : '-';
                })
                ->addColumn('role_id', function ($row) {
                    $count = DB::table('role_user')->where('role_id', $row->id)->count();
                    return $count;
                })
                
                ->editColumn('status', function ($row) {
                    $status = "<button class='table-status-btn " . ($row->status ? 'completed' : 'rejected') . "'> " . ($row->status ? 'Active' : 'In-Active') . "</button>";
                    return $status;
                })
                
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('application', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/staff/view/role/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('application', 'update')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/staff/update/role/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('application', 'delete')) {
                        
                            $btn .= "<img class='delete-role-btn' data-role-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                          
                    }
                    return $btn;
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        // Pass the staff members data to the view
        return view('Frontend.Staff.role.index', compact('Route', 'roles'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);
        $Route = 'Add Role';
        $roleExist = Role::where('name', $request->name)->get();

        if ($roleExist->isEmpty()) {
            $role = new Role();
            $role->name = $request->name;
            $role->status = ($request->status == 'true') ? 1 : 0;
            $role->save();
            $newPermissions = [
                'application',
                'bank_mis',
                'upload-mis',
                'settlement',
                'bank',
                'product',
                'dsa-code',
                'bank-target',
                'channel',
                'sales-person',
                'sheet-matching',
                'staff',
                'services'
            ];
            foreach ($newPermissions as $permissionName) {
                $permission = new Permission();
                $permission->name = $permissionName;
                $permission->save();
                // Attach the new permission to the new role
                $role->permissions()->attach($permission);
            }
            return redirect()->back()->with('success', 'Role added successfully.');
        } else {
            return redirect()->back()->with('error', 'Role name already exist.');
        }
    }

    public function show($role)
    {
        $Route = 'Edit Role';
        $role = Role::findOrFail($role);
        return view('Frontend.Staff.role.show',compact('role'));
    }

    public function getRole($role)
    {
        $role = Role::findOrFail($role);
        $roles = Role::all();
        return view('Frontend.Staff.role.edit',compact('role','roles'));
    }

    public function exportRoles()
    {
        return Excel::download(new RoleExport(), 'roles.xlsx');
    }

    public function update(Request $request, Role $role)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
        ]);
        $role->update($validatedData);
        flash()
            ->success('Role updated successfully ')
            ->flash();

        return redirect()->route('manage-role.index');
    }

    public function destroy(Role $role)
    {
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            flash()
                ->error('Cannot delete role. There are ' . $userCount . ' user(s) assigned to this role.')
                ->flash();
            return response()->json(['success' => true]); // assuming it's an AJAX request
        }

        $role->permissions()->delete();
        $role->delete();
        flash()
            ->success('Role deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
