<?php

namespace App\Exports;

use App\Models\BankData;
use App\Models\StaffAssign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StaffExport implements FromCollection, WithHeadings
{
    protected $counter = 0;
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function collection()
    {
        $roleId = 2;
        $user = Auth::user();
        if ($user->roles[0]->id == 1) {
            $channels = User::whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $channels = [];
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();
        }

        return $channels->map(function ($user) {
            $this->counter++;
            $bankData = BankData::where('user_id', $user->id)->first();
            return [
                'S No.'           => $this->counter,
                'Date Time'       => Carbon::parse($user->created_at)->format('d-m-Y'),
                'Employee ID'     => $user->Emp_Id,
                'Name'            => $user->first_name . ' ' . $user->last_name,
                'Email'           => $user->email,
                'Phone'           => $user->phone,
                'State'           => getStateName($user->state),
                'District'        => $user->district,
                'Pan Number'      => $user->pan_number,
                'Aadhar Number'   => $user->aadhar_number,
                'Address Line 1'  => $user->address_1,
                'Address Line 2'  => $user->address_2,
                'Landmark'        => $user->landmakr,
                'Pincode'         => $user->pincode,
                'Role'            => $user->roles->pluck('name')->implode(', '), // Assuming 'roles' is the relationship between User and Role model
                'Bank Name'       => optional($bankData)->bank_name,
                'Branch Name'     => optional($bankData)->branch_name,
                'Account Number'  => optional($bankData)->account_number,
                'IFSC Code'       => optional($bankData)->ifsc,
                'Status'          => $user->status ? 'Active' : 'In-Active',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S No.',
            'Date Time',
            'Employee ID',
            'Name',
            'Email',
            'Phone',
            'State',
            'District',
            'Pan Number',
            'Aadhar Number',
            'Address',
            'Role',
            'Bank Name',
            'Branch Name',
            'Account Number',
            'IFSC Code',
            'Status'
        ];
    }
}
