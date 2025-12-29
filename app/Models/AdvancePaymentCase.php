<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancePaymentCase extends Model
{
    use HasFactory;

    protected $table = 'advance_payment_cases';

    protected $fillable = [
        'advance_amount_log_id',
        'application_id',
        'product',
        'product_percent',
        'advance_payment_amount',
        'status',
    ];

    public function advanceAmountLog()
    {
        return $this->belongsTo(AdvanceAmountLog::class, 'advance_amount_log_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
