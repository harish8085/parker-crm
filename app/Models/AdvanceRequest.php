<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceRequest extends Model
{
    protected $table = 'advance_requests';

    protected $fillable = [
        'user_id',
        'requested_by',
        'case_type',
        'requested_amount',
        'advance_remark',
        'status',
        'admin_action_by',
        'admin_action_at',
        'admin_remark',
        'approved_log_id',
    ];

    protected $casts = [
        'requested_amount' => 'float',
        'admin_action_at' => 'datetime',
    ];

    public function channelUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function adminActionByUser()
    {
        return $this->belongsTo(User::class, 'admin_action_by');
    }

    public function approvedLog()
    {
        return $this->belongsTo(AdvanceAmountLog::class, 'approved_log_id');
    }

    public function requestCases()
    {
        return $this->hasMany(AdvanceRequestCase::class, 'advance_request_id');
    }
}

