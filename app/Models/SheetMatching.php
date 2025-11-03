<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SheetMatching extends Model
{
    use HasFactory;
    protected $fillable = [
        'bank_id', 'product_id', 'group', 'app_id', 'property_highlights', 'case_location',
        'customer_name', 'customer_firm_name', 'disbAmount', 'pf', 'subvention', 'roi',
        'insurance', 'otc_pdd_status', 'payout_amount', 'payout_rate','date','month','pf%','kli',
        'kli_payout%','kli_payout','kgi','kgi_payout%','kgi_payout'
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
