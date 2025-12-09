<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class AddNewPermissionsToAll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create announcement permission for Admin role with full access
        $adminRole = Role::where('name', 'Admin')->first();
        
        if ($adminRole) {
            $adminPermissionData = [
                'name'   => 'announcements',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ];
            $adminPermission = Permission::create($adminPermissionData);
            $adminRole->permissions()->attach($adminPermission);
        }

        // Create announcement permission for other roles with view-only access
        $otherRoles = Role::where('name', '!=', 'Admin')->get();
        
        foreach ($otherRoles as $role) {
            $permissionData = [
                'name'   => 'announcements',
                'create' => 0,
                'update' => 0,
                'view'   => 1,
                'delete' => 0,
            ];
            $permission = Permission::create($permissionData);
            $role->permissions()->attach($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove announcement permissions from all roles
        $roles = Role::all();
        
        foreach ($roles as $role) {
            $permissions = Permission::where('name', 'announcements')->get();
            foreach ($permissions as $permission) {
                $role->permissions()->detach($permission);
            }
        }
        
        // Delete all announcement permissions
        Permission::where('name', 'announcements')->delete();
    }
}
