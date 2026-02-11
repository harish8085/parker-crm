<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'settlement_id',
        'user_id',
        'gross_amount',
        'tds_amount',
        'advance_amount',
        'net_payable',
        'status',
        'created_by',
        'approved_at',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
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
