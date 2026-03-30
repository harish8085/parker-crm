<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankMIS extends Model
{
    use HasFactory;

    protected $table = 'bank_mis';

    protected $fillable = [
        'bank_id',
        'product_id',
        'company_name',
        'bank_mis_month',
        'app_id',
        'payout_rate',
        'location',
        'payout_amount',
        'customer_firm_name',
        'pf',
        'subvention',
        'roi',
        'insurance',
        'case_location',
        'case_state'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
