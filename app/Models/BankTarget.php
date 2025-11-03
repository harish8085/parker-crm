<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankTarget extends Model
{
    use HasFactory;

    protected $table = 'bank_targets'; // Explicitly defining the table name is optional if the naming follows Laravel's convention

    protected $fillable = [
        'bank_id', 'target_amount' // Specify any other fields that should be mass assignable
    ];

    /**
     * Get the bank associated with the target.
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
