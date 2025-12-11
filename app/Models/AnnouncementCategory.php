<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementCategory extends Model
{
    protected $table = 'announcement_categories';
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
    
}
