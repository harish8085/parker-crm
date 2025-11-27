<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankData extends Model
{
    use HasFactory;

    protected $table = 'bank_data';

    protected $fillable = [
        'user_id',
        'bank_name',
        'branch_name',
        'account_number',        
        'holder_name',
        'ifsc_code',
        'pan_photo',
        'aadhar_photo',
        'passbook_photo',
        'status',
        'is_default',
        'verification_status',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
