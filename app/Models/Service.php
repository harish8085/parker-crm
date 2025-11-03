<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['service_name', 'bank_id'];

    /**
     * Get the bank that owns the service.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get the service details for the service.
     */
    public function details()
    {
        return $this->hasMany(ServiceDetail::class);
    }
}
