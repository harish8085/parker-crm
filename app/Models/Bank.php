<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
     // Specify the table if it's not the plural form of the model name
    protected $table = 'banks';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'id';

    // Disable timestamps if not used
    public $timestamps = true;

    // Define fillable properties to allow mass assignment
    protected $fillable = [
        'name','short_name'

    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */

    public function bankProducts()
    {
        return $this->hasMany(BankProduct::class);
    }
}
