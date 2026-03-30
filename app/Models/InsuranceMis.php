<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceMis extends Model
{
    use HasFactory;

    protected $table = 'insurance_mis';

    protected $fillable = [
        'bank_id',
        'company_name',
        'application_no',
        'location',
        'disbursement_date',
        'customer_name',
        'loan_amt',
        'insurance_rate',
        'insurance_amt',
        'payment_status',
        'status',
        'sharing_insurance_commission',
        'uploaded_by',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}

