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
        'application_id',
        'received_rate',
        'gross_amount',
        'tds',
        'tds_percentage',
        'bank_account_id',
        'amount',
        'utr_number',
        'payment_status',
        'rejection_reason',
        'file_name',
        'transaction_id'
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

    /**
     * The application this distribution relates to.
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    /**
     * The transaction this distribution belongs to (if processed).
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
