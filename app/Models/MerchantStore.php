<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'region_id',
        'market_id',
        'store_name',
        'store_address',
        'shop_photo',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
