<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'settlement_id',
        'user_id',
        'gross_amount',
        'tds_amount',
        'advance_amount',
        'net_payable',
        'status',
        'rejection_reason',
        'rejected_at',
        'created_by',
        'approved_at',
        'completed_at',
        'completed_by',
    ];

    /**
     * Generate a unique transaction ID in format TXN{YYYYMMDD}{3-digit-seq}
     */
    public static function generateTransactionId()
    {
        $today = now()->format('Ymd');
        $prefix = 'TXN' . $today;

        $lastTransaction = self::where('transaction_id', 'like', $prefix . '%')
            ->orderBy('transaction_id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastSeq = (int) substr($lastTransaction->transaction_id, -3);
            $nextSeq = $lastSeq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function bankAllocations()
    {
        return $this->hasMany(TransactionBankAllocation::class);
    }
}
