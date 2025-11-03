<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankPayout extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bank_id',
        'product_id',
        'rate',
    ];

    /**
     * Get the bank associated with the payout.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }

    /**
     * Get the product associated with the payout.
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
