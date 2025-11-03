<?php

namespace App\Exports;

use App\Models\Application;
use App\Models\StaffAssign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplicationExport implements FromCollection, WithHeadings
{
    protected $counter = 0;

    public function collection()
    {
        $user = Auth::user();

        if ($user->roles[0]->id == 1) {
            $applications = Application::get();
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $applications = Application::where('user_id', Auth::id())->get();
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $applications = Application::whereIn('user_id', $channel_assign)->get();
        }
        return $applications->map(function ($application) {
            $user = User::findOrFail($application->user_id);

            $this->counter++;
            return [
                'S No.'                => $this->counter,
                'Date Time'            => Carbon::parse($application->created_at)->format('d-m-Y'),
                'Channel Name'         => $user->first_name." ". $user->last_name,
                'Application ID'       => $application->app_id,
                'Disbursement Date'    => $application->disbursement_date,
                'Customer Name'        => $application->customer_name,
                'Customer Firm`s Name' => $application->customer_firm_name,
                'Case Location'        => $application->case_location,
                'Case State'           => $application->case_state,
                'District'             => $application->district,
                'Bank Name'            => $application->bank_name,
                'Product Name'         => $application->product_name,
                'Group'                => $application->group,
                'Fresh/BT'             => $application->fresh_or_bt, // Assuming 'roles' is the relationship between User and Role model
                'Any Subvention'       => $application->any_subvention,
                'Disburse Amount'      => $application->disburse_amount,
                'OTC /PDD Status'      => $application->otc_or_pdd_status,
                'PF Taken'             => $application->pf_taken,
                'Banker Name'          => $application->banker_name,
                'Banker Number'        => $application->banker_number,
                'Banker Email'         => $application->banker_email,
                'Status'               => ucwords($application->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S No.',
            'Date Time',
            'Channel Name',
            'Application ID',
            'Disbursement Date',
            'Customer Name',
            'Customer Firm`s Name',
            'Case Location',
            'Case State',
            'District',
            'Bank Name',
            'Product Name',
            'Group',
            'Fresh/BT',
            'Any Subvention',
            'Disburse Amount',
            'OTC /PDD Status',
            'PF Taken',
            'Banker Name',
            'Banker Number',
            'Banker Email',
            'Status'
        ];
    }    
}
