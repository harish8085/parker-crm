<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAnnouncementPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('permissions')->insert([
            [
                'name'       => 'announcements',
                'create'     => true,
                'update'     => true,
                'view'       => true,
                'delete'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $adminRole = Role::where('name', 'Admin')->first();
        $permission = Permission::where('name', 'announcements')->first();

        if ($adminRole && $permission) {
            $adminRole->permissions()->attach($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission = Permission::where('name', 'announcements')->first();

        if ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        }
    }
}


