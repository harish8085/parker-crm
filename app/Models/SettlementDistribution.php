<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettlementDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_id',
        'user_id',
        'tds',                             
        'bank_account_id',
        'amount',
        'utr_number',
        'payment_status',
        'rejection_reason',
        'file_name'
    ];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankData::class);
    }
}
