<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceAmountLog extends Model
{
    
    protected $table = 'advance_amount_logs';
    protected $fillable = ['advance_id', 'advance_amount', 'advance_date', 'type', 'remark', 'created_by'];
    
    /**
     * Method advance
     *
     * @return void
     */
    public function advance()
    {
        return $this->belongsTo(Advance::class);
    }    
    /**
     * Method createdBy
     *
     * @return void
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paymentCases()
    {
        return $this->hasMany(AdvancePaymentCase::class, 'advance_amount_log_id');
    }

    public static function createAdvanceAmountLog(array $attributes)
    {
        return self::create([
            'advance_id' => $attributes['advance_id'],
            'advance_amount' => $attributes['advance_amount'],
            'advance_date' => $attributes['advance_date'],
            'type' => $attributes['type'],
            'remark' => $attributes['remark'],
            'created_by' => $attributes['created_by'],
            // We no longer store application IDs on the log; they live in advance_payment_cases
            'application_ids' => null,
        ]);
    }
}