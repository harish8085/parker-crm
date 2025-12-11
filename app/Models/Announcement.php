<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Bank;
use App\Models\Product;
use App\Models\AnnouncementCategory;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'message',
        'created_by',
        'starts_at',
        'expires_at',
        'is_active',
        'message_attachment',
        'bank_id',
        'product_id',
        'announcement_category_id',
    
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AnnouncementCategory::class, 'announcement_category_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(AnnouncementView::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AnnouncementAttachment::class);
    }
}


