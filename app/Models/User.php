<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        'profile_photo_path',
        'verification_status',
        'nid_number',
        'nid_front_path',
        'nid_back_path',
        'trade_license_number',
        'trade_license_path',
        'farm_name',
        'verification_submitted_at',
        'farmer_verified_at',
        'verified_by_admin_id',
        'verification_rejection_reason',
    ];

    const VERIFY_STATUS_UNVERIFIED = 'unverified';
    const VERIFY_STATUS_PENDING = 'pending';
    const VERIFY_STATUS_VERIFIED = 'verified';
    const VERIFY_STATUS_REJECTED = 'rejected';

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
            'farmer_verified_at' => 'datetime',
            'verification_submitted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isFarmerVerified(): bool
    {
        if ($this->role !== 'farmer') {
            return true;
        }

        return $this->verification_status === self::VERIFY_STATUS_VERIFIED
            || $this->farmer_verified_at !== null;
    }

    public function isVerificationPending(): bool
    {
        return $this->role === 'farmer' && $this->verification_status === self::VERIFY_STATUS_PENDING;
    }

    public function isVerificationRejected(): bool
    {
        return $this->role === 'farmer' && $this->verification_status === self::VERIFY_STATUS_REJECTED;
    }

    public function isUnverifiedFarmer(): bool
    {
        return $this->role === 'farmer' && ! $this->isFarmerVerified();
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by_admin_id');
    }
    
    public function products()
    {
        return $this->hasMany(\App\Models\Product::class, 'farmer_id');
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'buyer_id');
    }

    public function conversationsAsBuyer()
    {
        return $this->hasMany(\App\Models\Conversation::class, 'buyer_id');
    }

    public function conversationsAsFarmer()
    {
        return $this->hasMany(\App\Models\Conversation::class, 'farmer_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\UserSubscription::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function addresses()
    {
        return $this->hasMany(\App\Models\UserAddress::class);
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function defaultAddress(): ?\App\Models\UserAddress
    {
        return $this->addresses()->where('is_default', true)->first()
            ?: $this->addresses()->latest()->first();
    }

    public function activeSubscription(): ?\App\Models\UserSubscription
    {
        return $this->subscriptions()
            ->with('plan')
            ->where('status', \App\Models\UserSubscription::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('id')
            ->first();
    }

    public function currentPlan(): ?\App\Models\MembershipPlan
    {
        $activeSub = $this->activeSubscription();
        if ($activeSub && $activeSub->plan) {
            return $activeSub->plan;
        }

        return \App\Models\MembershipPlan::where('slug', 'free')->first()
            ?: new \App\Models\MembershipPlan([
                'name' => 'Free Farmer / সাধারণ অ্যাকাউন্ট',
                'slug' => 'free',
                'price' => 0,
                'active_listing_limit' => 5,
                'can_view_analytics' => false,
                'can_bulk_purchase' => false,
            ]);
    }

    public function activeListingLimit(): int
    {
        return (int) ($this->currentPlan()->active_listing_limit ?? 5);
    }

    public function canCreateMoreListings(): bool
    {
        $limit = $this->activeListingLimit();
        $currentCount = $this->products()->count();
        return $currentCount < $limit;
    }

    public function canAccessAnalytics(): bool
    {
        return (bool) ($this->currentPlan()->can_view_analytics ?? false);
    }

    public function canBulkPurchase(): bool
    {
        return (bool) ($this->currentPlan()->can_bulk_purchase ?? false);
    }
}

