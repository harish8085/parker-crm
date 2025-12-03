<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Advance extends Model
{
    
    protected $table = 'advances';
    protected $fillable = ['user_id', 'advance_amount', 'advance_date', 'advance_status', 'advance_remark'];

    /**
     * Get the user that owns the advance.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the advance amount logs for this advance.
     */
    public function advanceAmountLogs()
    {
        return $this->hasMany(AdvanceAmountLog::class);
    }

    /**
     * Helper to create an advance with sane defaults.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function createAdvance(array $attributes)
    {
        try {
            DB::transaction(function () use ($attributes) {
                $advance = self::where('user_id', $attributes['user_id'])->first();
                if(!$advance) {
                    $advance = new Advance();           
                }
                if($attributes['advance_type'] == 'add') {
                    $advance->advance_amount = $advance->advance_amount + $attributes['advance_amount'];
                } else {
                    $advance->advance_amount = $advance->advance_amount - $attributes['advance_amount'];
                }
                $advance->user_id = $attributes['user_id'];
                $advance->advance_amount = $attributes['advance_amount'];
                $advance->advance_date = $attributes['advance_date'] ?? now()->toDateString();
                $advance->advance_status = $attributes['advance_status'] ?? 1;
                $advance->advance_remark = $attributes['advance_remark'] ?? null;
                if($advance->save()) {
                    $advanceAmountLog = AdvanceAmountLog::createAdvanceAmountLog([
                        'advance_id' => $advance->id,
                        'advance_amount' => $attributes['advance_amount'],
                        'advance_date' => $attributes['advance_date'] ?? now()->toDateString(),
                        'type' => $attributes['advance_type'],
                        'remark' => $attributes['advance_remark'] ?? null,
                        'created_by' => $attributes['created_by'],
                    ]);
                    return true;
                } else {
                    return false;
                }
            });
        } catch (\Throwable $e) {
            // Handle the exception, e.g., log the error or return an error response.
            throw $e;
        }


         
    }
}