<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $existingPermission = DB::table('permissions')->where('name', 'report')->first();

        if ($existingPermission) {
            $permissionId = $existingPermission->id;
        } else {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'report',
                'create' => 1,
                'update' => 1,
                'view' => 1,
                'delete' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Admin', 'Maker', 'Checker', 'Channel'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', 'report')->first();

        if (!$permission) {
            return;
        }

        DB::table('permission_role')->where('permission_id', $permission->id)->delete();
        DB::table('permissions')->where('id', $permission->id)->delete();
    }
};
