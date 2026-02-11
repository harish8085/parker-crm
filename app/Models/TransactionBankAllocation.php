<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionBankAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'bank_account_id',
        'amount',
        'utr_number',
        'payment_status',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankData::class, 'bank_account_id');
    }
}
