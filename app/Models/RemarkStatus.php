<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemarkStatus extends Model
{
    use HasFactory;

    protected $table = 'remark_status';

    protected $fillable = [
        'title',
        'status',
    ];
}
