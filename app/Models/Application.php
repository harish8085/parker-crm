<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;
    protected $table = 'applications';

    protected $fillable = [
        'user_id',
        'app_id',
        'app_id_is_matched',
        'disbursement_date',
        'case_location',
        'case_location_is_matched',
        'case_location_is_value',
        'case_state',
        'customer_name',
        'customer_name_is_matched',
        'customer_name_is_value',
        'customer_firm_name',
        'bank_id',
        'bank_id_is_matched',
        'bank_id_is_value',
        'product_id',
        'product_id_is_matched',
        'product_id_is_value',
        'group',
        'group_is_matched',
        'fresh_or_bt',
        'any_subvention',
        'disburse_amount',
        'disburse_amount_is_matched',
        'disburse_amount_is_value',
        'commission_rate',
        'commission_rate_is_matched',
        'commission_rate_is_value',
        'otc_or_pdd_status',
        'pf_taken',
        'banker_name',
        'banker_number',
        'banker_email',
        'created_by',
        'status',
        'remark',
        'bank_mis_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
    public function bankData()
    {
        return $this->belongsTo(BankMIS::class, 'bank_mis_id');
    }
}
