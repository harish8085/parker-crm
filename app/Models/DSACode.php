<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DSACode extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dsa_codes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['bank_id','product_id','code', 'group'];

    /**
     * Indicates if the model should be timestamped.
     *
     * Standard Laravel behavior is to expect 'created_at' and 'updated_at' columns.
     * If you do not wish to use these columns, set this property to false.
     */
    public $timestamps = true;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class,'bank_id');
    }
    
}
