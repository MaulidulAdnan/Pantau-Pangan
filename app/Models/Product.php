<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'name', 'slug', 'unit', 'image', 'description'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function latestPrice()
    {
        return $this->hasOne(Price::class)->latestOfMany();
    }

    public function averagePrice()
    {
        return $this->prices()
            ->where('is_suspicious', false)
            ->where('created_at', '>=', now()->subDays(7))
            ->avg('price');
    }
}
