<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContestMis extends Model
{
    use HasFactory;

    protected $table = 'contest_mis';

    protected $fillable = [
        'bank_id',
        'application_no',
        'location',
        'disbursement_date',
        'customer_name',
        'loan_amt',
        'contest_rate',
        'contest_amt',
        'payment_status',
        'uploaded_by',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
