<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\BankData;
use App\Models\BankMIS;
use App\Models\BankProduct;
use App\Models\ServiceDetail;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSettlement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $application;

    /**
     * Create a new job instance.
     */
    public function __construct($application)
    {
        $this->application = $application;
       
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $record = BankMIS::where('id', $this->application->bank_mis_id)
            ->first();

            $this->createSettlement($record, $this->application);

    }

    private function createSettlement($record, $application)
    {
        $serviceType = User::where('id', $application->user_id)->value('service_type');
        $serviceDetails = ServiceDetail::where('service_id', $serviceType)
            ->where('product_name', $application->product_id)
            ->first();
        if ($serviceDetails) {
            if ($serviceDetails->type == 'vairable') {
                $totalMonthlyBusiness = $this->getTotalMonthlyBusiness($application);
                $totalMonthlyBusiness = $totalMonthlyBusiness + $application->disburse_amount;
                $service_details =
                    ServiceDetail::where('min', '<=', $totalMonthlyBusiness)
                    ->where('max', '>=', $totalMonthlyBusiness)
                    ->first();
            } else {
                $service_details = $serviceDetails;
            }
            $percentage = $service_details->percentage;
            $amount = floatval(round(floatval($record->payout_amount) * floatval($service_details->percentage) / 100, 2));
            // Create settlement
            $settlement = new Settlement();
            $settlement->user_id = $application->user_id;
            $settlement->application_id = $application->id;
            $settlement->status = 'checker';
            $settlement->received_rate = $percentage;
            // $settlement->tds = round($tds, 2);
            $settlement->amount = round($amount, 2);
            $settlement->gross_amount = round($record->payout_amount, 2);
            $settlement->save();

            $bank_data = BankData::where('user_id', $application->user_id)->first();
            $settlement_distribution = new SettlementDistribution();
            $settlement_distribution->settlement_id = $settlement->id;
            $settlement_distribution->user_id = $application->user_id;
            $settlement_distribution->amount = round($amount - floatval($amount) * 0.02, 2);
            $settlement_distribution->bank_account_id = $bank_data->id;
            $settlement_distribution->tds = round($amount * 0.02, 2);
            $settlement_distribution->save();
        }
    }

    private function getTotalMonthlyBusiness($application)
    {
        // Assuming you have a model named Application that tracks disbursed amounts
        $totalMonthlyDisbursedAmount = Application::where('user_id', $application->user_id)
            ->whereYear('disbursement_date', Carbon::parse($application->disbursement_date)->year)
            ->whereMonth('disbursement_date', Carbon::parse($application->disbursement_date)->month)
            ->sum('disburse_amount');

        return $totalMonthlyDisbursedAmount;
    }
}
