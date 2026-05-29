<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'profile_photo',
        'gender',
        'address',
        'region_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
        ];
    }

    // Role Helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPedagang(): bool
    {
        return $this->role === 'pedagang';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isApprovedMerchant(): bool
    {
        return $this->isPedagang() && $this->merchantProfile && $this->merchantProfile->status === 'approved';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // Relationships
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function merchantProfile()
    {
        return $this->hasOne(MerchantProfile::class);
    }

    public function merchantStores()
    {
        return $this->hasMany(MerchantStore::class);
    }

    public function approvedStores()
    {
        return $this->hasMany(MerchantStore::class)->where('status', 'approved');
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

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function commentLikes()
    {
        return $this->hasMany(CommentLike::class);
    }
}
