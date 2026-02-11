<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'settlement_distribution_id',
        'gross_amount',
        'tds',
        'net_amount',
        'advance_amount',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function settlementDistribution()
    {
        return $this->belongsTo(SettlementDistribution::class);
    }
}
