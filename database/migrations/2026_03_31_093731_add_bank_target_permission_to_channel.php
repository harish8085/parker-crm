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
        $channelRole = DB::table('roles')->where('name', 'Channel')->first();
        if (!$channelRole) {
            return;
        }

        $permission = DB::table('permissions')->where('name', 'bank-target')->first();

        if (!$permission) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'bank-target',
                'create' => 0,
                'update' => 0,
                'view' => 1,
                'delete' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $permissionId = $permission->id;
            // Ensure channel can view bank-target
            DB::table('permissions')->where('id', $permissionId)->update(['view' => 1, 'updated_at' => now()]);
        }

        $exists = DB::table('permission_role')
            ->where('permission_id', $permissionId)
            ->where('role_id', $channelRole->id)
            ->exists();

        if (!$exists) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $channelRole->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $channelRole = DB::table('roles')->where('name', 'Channel')->first();
        if (!$channelRole) {
            return;
        }

        $permission = DB::table('permissions')->where('name', 'bank-target')->first();
        if ($permission) {
            DB::table('permission_role')
                ->where('permission_id', $permission->id)
                ->where('role_id', $channelRole->id)
                ->delete();
        }
    }
};
