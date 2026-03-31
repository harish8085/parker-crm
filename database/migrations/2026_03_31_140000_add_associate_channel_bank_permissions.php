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
        $associateRole = DB::table('roles')->where('name', 'Associate_Channel')->first();
        if (!$associateRole) {
            return;
        }

        $permissionNames = ['bank', 'dsa-code', 'bank-target', 'product'];

        foreach ($permissionNames as $name) {
            $permission = DB::table('permissions')->where('name', $name)->first();

            if (!$permission) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $name,
                    'create' => 0,
                    'update' => 0,
                    'view' => 1,
                    'delete' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $permissionId = $permission->id;
                // Ensure associate can view when permission exists.
                DB::table('permissions')->where('id', $permissionId)->update(['view' => 1, 'updated_at' => now()]);
            }

            $exists = DB::table('permission_role')
                ->where('permission_id', $permissionId)
                ->where('role_id', $associateRole->id)
                ->exists();

            if (!$exists) {
                DB::table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $associateRole->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $associateRole = DB::table('roles')->where('name', 'Associate_Channel')->first();
        if (!$associateRole) {
            return;
        }

        $permissionNames = ['bank', 'dsa-code', 'bank-target', 'product'];

        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        DB::table('permission_role')
            ->where('role_id', $associateRole->id)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }
};
