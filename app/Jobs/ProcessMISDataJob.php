<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\BankData;
use App\Models\BankMIS;
use App\Models\BankProduct;
use App\Models\Settings;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\BankMisTracker;
use SebastianBergmann\Environment\Console;

class ProcessMISDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bankId;
    public $productId;
    public $status;

    /**
     * Create a new job instance.
     */
    public function __construct($bankId, $productId, $status = 'pending')
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
        log::info($bank_product);
            if ($bank_product->auto_generate_lan) {
                log::info('auto_generate_lan');
                $application = Application::where('customer_name', $record->customer_name)
                    ->where('bank_id', $this->bankId)
                    ->where('product_id', $this->productId)
                    ->where('status', 'pending')
                    ->first();
            } else {
                log::info('app_id');
                $application = Application::where('app_id', $record->app_id)
                    ->where('bank_id', $this->bankId)
                    ->where('product_id', $this->productId)
                    ->where('status', 'pending')
                    ->first();
            }

log::info($application);
            if ($application) {
                log::info('matchprocess');
                $this->processMatching($record, $application, $bank_product->auto_generate_lan);
            }
        }

        // After all matching is done, update the tracker
        app(\App\Http\Controllers\Application\ApplicationController::class)->updateBankMisTrackerFromApplications();
        // echo "Completed processing MIS data for Bank ID: {$this->bankId}, Product ID: {$this->productId}";

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

        // sleep(2);
        // $this->syncFromApplications();

        // Ensure `$application` is a valid model instance before updating
        // if ($application instanceof \Illuminate\Database\Eloquent\Model) {
        $result =  $application->update($updateData);


        // Check if all conditions in `$updateData` (except timestamps and IDs) are true
        $checkKeys = ['app_id_is_matched', 'customer_name_is_matched', 'bank_id_is_matched', 'product_id_is_matched', 'disburse_amount_is_matched'];
        if (collect($updateData)->only($checkKeys)->every(fn($value) => $value === true)) {
            $application->update(['status' => 'in-progress']);
            if ($this->status == 'completed') {
                $application->update(['status' => 'completed']);
                $this->createSettlement($record, $application);
            }
        }

        // }

    }




    private function createSettlement($record, $application)
    {
        // Determine the parent channel: use parent_channel_id if set, otherwise use the application's user_id
        $parentChannelId = $application->parent_channel_id ?? $application->user_id;

        // Commission calculation priority:
        // 1. Application-level sharing_commission (highest priority)
        // 2. Parent channel's user_commission from users table
        $percentage = $this->getCommissionPercentage($application, $parentChannelId);

        if ($percentage === null || $percentage <= 0) {
            Log::warning('No commission percentage found for application: ' . $application->id . ', parent channel: ' . $parentChannelId);
            return;
        }

        Log::info('Commission percentage: ' . $percentage . '% (Application ID: ' . $application->id . ', Parent Channel: ' . $parentChannelId . ')');

        $grossAmount = round(floatval($record->payout_amount), 2);
        $amount = round(floatval($grossAmount) * floatval($percentage) / 100, 2);

        // Check if a non-completed settlement already exists for this parent channel
        $settlement = Settlement::where('user_id', $parentChannelId)
            ->whereNotIn('status', ['completed'])
            ->first();

        if ($settlement) {
            // Add to existing settlement
            $settlement->amount = round(floatval($settlement->amount) + $amount, 2);
            $settlement->gross_amount = round(floatval($settlement->gross_amount) + $grossAmount, 2);
            $settlement->save();
            Log::info('Settlement updated (aggregated) for parent channel: ' . $parentChannelId);
        } else {
            // Create new settlement for this parent channel
            $settlement = new Settlement();
            $settlement->user_id = $parentChannelId;
            $settlement->application_id = $application->id;
            $settlement->status = 'checker';
            $settlement->received_rate = $percentage;
            $settlement->amount = $amount;
            $settlement->gross_amount = $grossAmount;
            $settlement->save();
            Log::info('New settlement created for parent channel: ' . $parentChannelId);
        }

        // Create settlement distribution for this specific application
        $tds_percentage = Settings::where('name', 'TDS')->first()->value;
        $tds = round($amount * $tds_percentage / 100, 2);
        $netAmount = round($amount - $tds, 2);
        $bank_data = BankData::where('user_id', $parentChannelId)->first();

        $settlement_distribution = new SettlementDistribution();
        $settlement_distribution->settlement_id = $settlement->id;
        $settlement_distribution->user_id = $application->user_id;
        $settlement_distribution->application_id = $application->id;
        $settlement_distribution->received_rate = $percentage;
        $settlement_distribution->gross_amount = $grossAmount;
        $settlement_distribution->amount = $netAmount;
        $settlement_distribution->bank_account_id = $bank_data ? $bank_data->id : null;
        $settlement_distribution->tds = $tds;
        $settlement_distribution->save();
        Log::info('Settlement distribution created for application: ' . $application->id);
    }

    /**
     * Get commission percentage based on priority:
     * 1. Application-level sharing_commission (highest priority)
     * 2. Parent channel's user_commission from users table
     */
    private function getCommissionPercentage($application, $parentChannelId)
    {
        // Priority 1: Application-level sharing_commission
        if (!empty($application->sharing_commission) && floatval($application->sharing_commission) > 0) {
            Log::info('Using application sharing_commission: ' . $application->sharing_commission);
            return floatval($application->sharing_commission);
        }

        // Priority 2: Parent channel's user_commission
        $parentChannelCommission = User::where('id', $parentChannelId)->value('user_commission');

        if (!empty($parentChannelCommission) && floatval($parentChannelCommission) > 0) {
            Log::info('Using parent channel user_commission: ' . $parentChannelCommission . ' (Channel ID: ' . $parentChannelId . ')');
            return floatval($parentChannelCommission);
        }

        Log::warning('No commission found - sharing_commission: ' . ($application->sharing_commission ?? 'null') . ', parent channel user_commission: ' . ($parentChannelCommission ?? 'null'));
        return null;
    }
}
