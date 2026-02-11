<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'application_id',
        'received_rate',
        'amount',
        'gross_amount',
        'status',
        'settlement_date',
    ];

    /**
     * The parent channel user this settlement belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Settlement distributions (per-application breakdown).
     */
    public function distributions()
    {
        return $this->hasMany(SettlementDistribution::class, 'settlement_id');
    }

    /**
     * The application (kept for backward compatibility).
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
