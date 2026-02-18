<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceRequestCase extends Model
{
    protected $table = 'advance_request_cases';

    protected $fillable = [
        'advance_request_id',
        'application_id',
        'product',
        'product_percent',
        'advance_payment_amount',
    ];

    protected $casts = [
        'advance_payment_amount' => 'float',
    ];

    public function request()
    {
        return $this->belongsTo(AdvanceRequest::class, 'advance_request_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}

