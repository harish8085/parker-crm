<?php

namespace Database\Seeders;

use App\Models\BankData;
use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = Role::where('name', 'Admin')->first();

        // Create admin user
        $admin = User::create([
            'Emp_Id' => 'MP_Ind_111',
            'first_name' => 'Ankit',
            'last_name' => 'Chawda',
            'email' => 'admin111@gmail.com',
            'phone' => '7089887760', // Change as needed
            'state' => 'MP', // Change as needed
            'district' => 'Indore', // Change as needed
            'pan_number' => 'BYGMC4912G', // Change as needed
            'aadhar_number' => '724255695534', // Change as needed
            'address_1' => '190 Tilak Nagar Extension', // Change as needed
            'address_2' => 'Main road', // Change as needed
            'landmark' => 'Near Dadawadi', // Change as needed
            'pincode' => '452018', // Change as needed
            'status' => 1, // Assuming the admin role ID is 1, change as needed
            'user_type' => 'admin', // Assuming the admin role ID is 1, change as needed
            'password' => Hash::make('Obligr@123'), // Change as needed
        ]);
        $admin->roles()->attach($adminRole->id);

        BankData::create([
            'user_id' => $adminRole->id,
            'bank_name' => 'HDFC',
            'branch_name' => 'Mahaxlaxmi',
            'holder_name' => 'Ankit Chawda',
            'account_number' => '05250110073780',
            'ifsc_code' => 'HDFC000525',
            'status' => 1,
        ]);
    }
}
