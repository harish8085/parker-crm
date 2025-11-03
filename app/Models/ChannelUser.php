<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelUser extends Model
{
    use HasFactory;
     // Specify the table if it's not the plural form of the model name
    protected $table = 'channel_users';

    // Specify the primary key if it's not 'id'
    protected $primaryKey = 'id';

    // Disable timestamps if not used
    public $timestamps = true;

    // Define fillable properties to allow mass assignment
    protected $fillable = [
        'channel_id','associate_channel_id'
    ];

    // Relationship to get the parent channel
    public function channel()
    {
        return $this->belongsTo(User::class, 'channel_id');
    }

    // Relationship to get the associated channel
    public function associateChannel()
    {
        return $this->belongsTo(User::class, 'associate_channel_id');
    }
}
