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

class ProcessMISDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bankId;
    public $productId;
    public $status;

    /**
     * Create a new job instance.
     */
    public function __construct($bankId, $productId, $status ='pending')
    {
        $this->bankId = $bankId;
        $this->productId = $productId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        $misRecords = BankMIS::where('bank_id', $this->bankId)
            ->where('product_id', $this->productId)
            ->get();

            \Log::info($misRecords);
            foreach ($misRecords as $record) {
            $bank_product = BankProduct::where('bank_id', $this->bankId)->where('product_id', $this->productId)->first();
            if ($bank_product->auto_generate_lan) {
                $application = Application::where('customer_name', $record->customer_name)
                    ->where('bank_id', $this->bankId)
                    ->where('product_id', $this->productId)
                    ->where('status', 'pending')
                    ->first();
            } else {
                $application = Application::where('app_id', $record->app_id)
                    ->where('bank_id', $this->bankId)
                    ->where('product_id', $this->productId)
                    ->where('status', 'pending')
                    ->first();
            }


            if ($application) {
                $this->processMatching($record, $application, $bank_product->auto_generate_lan);
            }
        }
    }

    private function processMatching($record, $application, $copy_lan)
    {
        $updateData = [
            'app_id_is_matched' => checkValueAndSetFlag($application, 'app_id', $record->app_id),
            'case_location_is_matched' => checkValueAndSetFlag($application, 'case_location', $record->case_location),
            'customer_name_is_matched' => checkValueAndSetFlag($application, 'customer_name', $record->customer_name),
            'bank_id_is_matched' => checkValueAndSetFlag($application, 'bank_id', $record->bank_id),
            'product_id_is_matched' => checkValueAndSetFlag($application, 'product_id', $record->product_id),
            'group_is_matched' => checkValueAndSetFlag($application, 'group', $record->group),
            'disburse_amount_is_matched' => checkValueAndSetFlag($application, 'disburse_amount', floatval($record->disbAmount ?? 0)),
            'commission_rate_is_matched' => checkValueAndSetFlag($application, 'commission_rate', floatval($record->payout_rate ?? 0)),
            'updated_at' => Carbon::now(),
            'bank_mis_id' => $record->id ?? null
        ];



        if ($copy_lan) {
            BankMIS::where('id', $record->id)->update(['app_id' => $application->app_id]);
            $data = [
                'app_id_is_matched' => 1,
                'app_id_is_value' => $record->app_id,
            ];
            $updateData = array_merge($data, $updateData);
        }

        // Ensure `$application` is a valid model instance before updating
        // if ($application instanceof \Illuminate\Database\Eloquent\Model) {
       $result =  $application->update($updateData);
        

        // Check if all conditions in `$updateData` (except timestamps and IDs) are true
        $checkKeys = ['app_id_is_matched','customer_name_is_matched', 'bank_id_is_matched', 'product_id_is_matched', 'disburse_amount_is_matched'];
        if (collect($updateData)->only($checkKeys)->every(fn($value) => $value === true)) {
            $application->update(['status' => 'in-progress']);
            if($this->status == 'completed'){
                $application->update(['status' => 'completed']);
                $this->createSettlement($record, $application);
            }
        }
        // }

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
            $amount = floatval(floatval($record->payout_amount) * floatval($service_details->percentage) / 100);
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
            $settlement_distribution->amount = round($amount, 2);
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
