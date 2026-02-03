<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankMisTracker extends Model
{
    use HasFactory;
      protected $table = 'bank_mis_tracker';

    protected $fillable = [
        'bank_mis_month',
        'bank',
        'product',
        'status',
        'total_cases',
        'matched_cases',
        'unmatched_cases'
    ];
}
