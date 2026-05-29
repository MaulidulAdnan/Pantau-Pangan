<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Market extends Model
{
    use HasFactory;

    protected $fillable = ['region_id', 'name', 'slug', 'address'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function merchantProfiles()
    {
        return $this->hasMany(MerchantProfile::class);
    }
}
