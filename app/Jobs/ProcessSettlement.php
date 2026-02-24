<?php

namespace App\Jobs;

use App\Models\BankData;
use App\Models\BankMIS;
use App\Models\Settings;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        Log::info('settlement created');
    }

    private function createSettlement($record, $application)
    {
        // Determine the parent channel: use parent_channel_id if set, otherwise use the application's user_id
        $parentChannelId = $application->parent_channel_id ?? $application->user_id;

        // Commission calculation priority:
        // 1. Application-level sharing_commission (highest priority)
        // 2. Parent channel's user_commission from users table
        // 3. Associates inherit parent channel's user_commission
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
            $settlement->settlement_type = 'commission';
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
        $settlement_distribution->settlement_type = 'commission';
        $settlement_distribution->received_rate = $percentage;
        $settlement_distribution->gross_amount = $amount; // commission amount (not bank payout)
        $settlement_distribution->amount = $netAmount;
        $settlement_distribution->bank_account_id = $bank_data ? $bank_data->id : null;
        $settlement_distribution->tds = $tds;
        $settlement_distribution->tds_percentage = $tds_percentage;
        $settlement_distribution->save();
        Log::info('Settlement distribution created for application: ' . $application->id);
    }

    /**
     * Get commission percentage based on priority:
     * 1. Application-level sharing_commission (highest priority)
     * 2. Parent channel's user_commission from users table
     * 3. Associates inherit parent channel's user_commission
     */
    private function getCommissionPercentage($application, $parentChannelId)
    {
        // Priority 1: Application-level sharing_commission
        if (!empty($application->sharing_commission) && floatval($application->sharing_commission) > 0) {
            Log::info('Using application sharing_commission: ' . $application->sharing_commission);
            return floatval($application->sharing_commission);
        }

        // Priority 2 & 3: Parent channel's user_commission
        // (Associates automatically inherit parent channel's commission since we always look at parentChannelId)
        $parentChannelCommission = User::where('id', $parentChannelId)->value('user_commission');

        if (!empty($parentChannelCommission) && floatval($parentChannelCommission) > 0) {
            Log::info('Using parent channel user_commission: ' . $parentChannelCommission . ' (Channel ID: ' . $parentChannelId . ')');
            return floatval($parentChannelCommission);
        }

        Log::warning('No commission found - sharing_commission: ' . ($application->sharing_commission ?? 'null') . ', parent channel user_commission: ' . ($parentChannelCommission ?? 'null'));
        return null;
    }
}
