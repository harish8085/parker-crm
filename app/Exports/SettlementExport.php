<?php

namespace App\Exports;

use App\Models\Application;
use App\Models\BankData;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\StaffAssign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SettlementExport implements FromCollection, WithHeadings
{
    protected $counter = 0;

    public function collection()
    {
        $user = Auth::user();

        if ($user->roles[0]->id == 1) {
            $settlements = Settlement::where('status', 'pending')->pluck('id');
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $settlements = Settlement::where('user_id', Auth::id())->where('status', 'pending')->pluck('id');
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $settlements = Settlement::whereIn('user_id', $channel_assign)->where('status', 'pending')->pluck('id');
        }

        // Filter SettlementDistribution to only include pending settlements
        $results = SettlementDistribution::select('bank_account_id', DB::raw('SUM(amount) as total_amount'))
        ->whereIn('settlement_id', $settlements) // Filter by pending settlement IDs
            ->groupBy('bank_account_id')
            ->get();

        $details = $results->map(function ($result) use ($settlements) {
            $settlementIds = SettlementDistribution::where('bank_account_id', $result->bank_account_id)
                ->whereIn('settlement_id', $settlements)
                ->pluck('id')
                ->unique()
                ->values();

            return [
                'bank_account_id' => $result->bank_account_id,
                'total_amount' => $result->total_amount,
                'settlement_ids' => $settlementIds,
            ];
        });

        return $details->map(function ($settlement_data) {

            $bankdetails = BankData::find($settlement_data['bank_account_id']);
            $user_details = User::where('id', $bankdetails->user_id)->first();
            return [
                "PYMT_PROD_TYPE_CODE" => 'PAB_VENDOR',
                "PYMT_MODE" => 'NEFT',
                "DEBIT_ACC_NO" => '091605001661',
                "BNF_NAME" => $bankdetails->holder_name,
                "BENE_ACC_NO" => $bankdetails->account_number,
                "BENE_IFSC" => $bankdetails->ifsc_code,
                "AMOUNT" => $settlement_data['total_amount'],
                "DEBIT_NARR" => '',
                "CREDIT_NARR" => '',
                "MOBILE_NUM" => $user_details->phone,
                "EMAIL_ID" => $user_details->email,
                "REMARK" => '',
                "PYMT_DATE" => Carbon::now()->format('d-m-Y'),
                "REF_NO" => $settlement_data['settlement_ids'],
                "ADDL_INFO1" => $user_details->address_1 . ',' . $user_details->address_2 . ',' . $user_details->landmark . ',' . $user_details->district . ',' . $user_details->state . '(' . $user_details->pincode . ')',
                "ADDL_INFO2" => '',
                "ADDL_INFO3" => '',
                "ADDL_INFO4" => '',
                "ADDL_INFO5" => '',
                "LEI_NUMBER" => '',
            ];
        });
    }


    public function headings(): array
    {
        return [
            "PYMT_PROD_TYPE_CODE",
            "PYMT_MODE",
            "DEBIT_ACC_NO",
            "BNF_NAME",
            "BENE_ACC_NO",
            "BENE_IFSC",
            "AMOUNT",
            "DEBIT_NARR",
            "CREDIT_NARR",
            "MOBILE_NUM",
            "EMAIL_ID",
            "REMARK",
            "PYMT_DATE",
            "REF_NO",
            "ADDL_INFO1",
            "ADDL_INFO2",
            "ADDL_INFO3",
            "ADDL_INFO4",
            "ADDL_INFO5",
            "LEI_NUMBER"
        ];
    }
}
