<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Bid;

class Product extends Model
{
    use HasFactory;

    protected $casts = [
        'auction_start' => 'datetime',
        'auction_end' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo (Category::class,'category_id');
    }
    public function brand()
    {
         return $this->belongsTo (Brand::class,'brand_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function isAuctionActive()
    {
        if (! $this->auction_enabled) return false;
        if ($this->auction_start && now()->lt($this->auction_start)) return false;
        if ($this->auction_end && now()->gte($this->auction_end)) return false;
        return true;
    }

    public function isAuctionClosed()
    {
        if (! $this->auction_enabled) return false;
        if ($this->auction_end && now()->gte($this->auction_end)) return true;
        return false;
    }
}
