<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        // Retrieve the admin role
        $adminRole = Role::where('name', 'Admin')->first();

        $adminPermissions = [
            [
                'name'   => 'application',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank_mis',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'upload-mis',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'settlement',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'channel',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'product',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'dsa-code',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank-target',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'sales-person',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'staff',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'sheet-matching',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'services',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank-payout',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            // Add more permissions as needed
        ];

        // Insert permissions into the database
        foreach ($adminPermissions as $permissionData) {
            $permission = Permission::create($permissionData);
            $adminRole->permissions()->attach($permission);
        }

        //Channel Role
        $channelRole = Role::where('name', 'Channel')->first();

        $channelPermissions = [
            [
                'name'   => 'application',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank_mis',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'upload-mis',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'settlement',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'sales-person',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'channel',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank',
                'create' => 0,
                'update' => 0,
                'view'   => 1,
                'delete' => 0,
            ],
            [
                'name'   => 'product',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'dsa-code',
                'create' => 0,
                'update' => 0,
                'view'   => 1,
                'delete' => 0,
            ],
            [
                'name'   => 'sheet-matching',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank-target',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'staff',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'services',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank-payout',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            // Add more permissions as needed
        ];

        // Insert permissions into the database
        foreach ($channelPermissions as $permissionData) {
            $permission = Permission::create($permissionData);
            $channelRole->permissions()->attach($permission);
        }

        //Sales Role
        $salesRole = Role::where('name', 'Sales')->first();

        $salesPermissions = [
            [
                'name'   => 'application',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'bank_mis',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'upload-mis',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'settlement',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'channel',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank',
                'create' => 0,
                'update' => 0,
                'view'   => 1,
                'delete' => 0,
            ],
            [
                'name'   => 'product',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'dsa-code',
                'create' => 0,
                'update' => 0,
                'view'   => 1,
                'delete' => 0,
            ],
            [
                'name'   => 'sheet-matching',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank-target',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'sales-person',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            [
                'name'   => 'staff',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'services',
                'create' => 0,
                'update' => 0,
                'view'   => 0,
                'delete' => 0,
            ],
            [
                'name'   => 'bank-payout',
                'create' => 1,
                'update' => 1,
                'view'   => 1,
                'delete' => 1,
            ],
            // Add more permissions as needed
        ];

        foreach ($salesPermissions as $permissionData) {
            $permission = Permission::create($permissionData);
            $salesRole->permissions()->attach($permission);
        }
    }
}
