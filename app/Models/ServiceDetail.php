<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'product_name', 'type', 'percentage', 'min_value', 'max_value'];

    /**
     * Get the service that owns the detail.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the product associated with the detail.
     */
}
