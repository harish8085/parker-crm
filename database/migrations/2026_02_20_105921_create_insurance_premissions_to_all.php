<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

class CreateInsurancePremissionsToAll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
             $adminRole = Role::where('name', 'Admin')->first();

        if ($adminRole) {
            $adminPermissionData = [
                'name'   => 'insurance',
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
                'name'   => 'insurance',
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
        // Remove insurance permissions from all roles
        $roles = Role::all();

        foreach ($roles as $role) {
            $permissions = Permission::where('name', 'insurance')->get();
            foreach ($permissions as $permission) {
                $role->permissions()->detach($permission);
            }
        }
    }
}
