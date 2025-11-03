<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAssign extends Model
{
    use HasFactory;

    protected $table = 'staff_assign';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'channel_sales_id'
    ];
}
