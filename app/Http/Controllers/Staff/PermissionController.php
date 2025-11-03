<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    // show staff
    public function index()
    {
        $Route = 'Manage Permission';
        // Retrieve all staff members
        $roles = Role::whereNotIn('id', [1, 2, 3])->where('status', 1)->get();
        // Pass the staff members data to the view
        return view('Frontend.Staff.permission.index', compact('Route', 'roles'));
    }
    public function getPermission(Role $role)
    {
        $permissions = $role->permissions;
        $data = '';
        foreach ($permissions as $permission) {
            $data .=
                '<tr data-permission-id="' . $permission->id . '">
                        <td class="row-color table-row">' . getPermissionName($permission->name) . ' Module</td>
                        <td class="row-color table-row">
                            <div class="form-check form-switch">    
                                <input class="form-check-input" type="checkbox"  name="create"' . ($permission->create ? ' checked' : '') . ' ' . (Auth::user()->hasPermission('staff', 'update') ? '' : 'disabled') . '>
                            </div>
                        </td>

                        <td class="row-color table-row">
                            <div class="form-check form-switch">    
                                <input class="form-check-input" type="checkbox"  name="view" ' . ($permission->view ? ' checked' : '') . ' ' .  ((Auth::user()->hasPermission('staff', 'update') && $permission->name != 'upload-mis') ? '' : 'disabled') . '>
                            </div>
                        </td>
                        <td class="row-color table-row">
                            <div class="form-check form-switch">    
                                <input class="form-check-input" type="checkbox"  name="update" ' . ($permission->update ? ' checked' : '') . ' ' . ((Auth::user()->hasPermission('staff', 'update') && $permission->name != 'upload-mis') ? '' : 'disabled') . '>
                            </div>
                        </td>
                        <td class="row-color table-row">
                            <div class="form-check form-switch">    
                                <input class="form-check-input" type="checkbox"  name="delete" ' . ($permission->delete ? ' checked' : '') . ' ' . ((Auth::user()->hasPermission('staff', 'update') && $permission->name != 'upload-mis') ? '' : 'disabled') . '>
                            </div>
                        </td>
                </tr>
                ';
        }

        return $data;
    }

    public function updatePermission(Request $request)
    {
        $permission = Permission::find($request->input('permission_id'));
        if ($request->input('create') === 'active' || $request->input('create') === 'in-active') {
            $permission->create = ($request->input('create') === 'active') ? 1 : 0;
        }

        if ($request->input('update') === 'active' || $request->input('update') === 'in-active') {
            $permission->update = ($request->input('update') === 'active') ? 1 : 0;
        }

        if ($request->input('view') === 'active' || $request->input('view') === 'in-active') {
            $permission->view = ($request->input('view') === 'active') ? 1 : 0;
        }

        if ($request->input('delete') === 'active' || $request->input('delete') === 'in-active') {
            $permission->delete = ($request->input('delete') === 'active') ? 1 : 0;
        }

        $permission->save();
        flash()
            ->success('Permission updated successfully ')
            ->flash();
        return response()->json(['success' => true]);
    }
}
