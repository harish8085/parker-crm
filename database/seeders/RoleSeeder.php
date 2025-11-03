<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Define the roles you want to create
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'Channel'],
            ['name' => 'Sales'],
            ['name' => 'Associate_Channel'],
            // Add more roles as needed
        ];

        // Insert the roles into the database
        Role::insert($roles);
    }
}
