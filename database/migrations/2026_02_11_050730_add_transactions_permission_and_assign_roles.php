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
        // Create the transactions permission
        $permissionId = DB::table('permissions')->insertGetId([
            'name' => 'transactions',
            'create' => 1,
            'update' => 1,
            'view' => 1,
            'delete' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign to Admin (1), Channel (2), Maker (35), Checker (36)
        $roles = [1, 2, 35, 36];
        foreach ($roles as $roleId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', 'transactions')->first();
        if ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
