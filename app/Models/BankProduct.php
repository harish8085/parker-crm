<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankProduct extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bank_products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['bank_id', 'product_id','auto_generate_lan'];

    /**
     * Get the bank that owns the bank product.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
