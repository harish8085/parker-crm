<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert new permissions for master code in the permissions table (according to existing structure)
        DB::table('permissions')->insert([
            [
                'name'       => 'master_code',
                'create'     => true,
                'update'     => true,
                'view'       => true,
                'delete'     => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Safely attach the permission to the "channel" role if it exists
        $channelRole = Role::where('name', 'channel')->first();
        $permission  = Permission::where('name', 'master_code')->first();

        if ($channelRole && $permission) {
            $channelRole->permissions()->attach($permission);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
        });
    }
}
