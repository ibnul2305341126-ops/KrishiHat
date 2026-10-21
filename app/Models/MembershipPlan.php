<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'role_requirement',
        'role_eligibility',
        'price',
        'billing_cycle_days',
        'duration_days',
        'billing_interval',
        'active_listing_limit',
        'featured_listings_allowed',
        'is_featured',
        'can_view_analytics',
        'can_bulk_purchase',
        'priority_support',
        'is_active',
        'is_demo',
        'eligibility_notes',
        'features',
    ];

    protected $casts = [
        'price' => 'float',
        'billing_cycle_days' => 'integer',
        'active_listing_limit' => 'integer',
        'featured_listings_allowed' => 'integer',
        'can_view_analytics' => 'boolean',
        'can_bulk_purchase' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
        'is_demo' => 'boolean',
    ];

    public function getDurationDaysAttribute(): int
    {
        return (int) ($this->billing_cycle_days ?? 30);
    }

    public function setDurationDaysAttribute($value): void
    {
        $this->attributes['billing_cycle_days'] = (int) $value;
    }

    public function getRoleEligibilityAttribute(): string
    {
        return $this->role_requirement ?? 'all';
    }

    public function setRoleEligibilityAttribute($value): void
    {
        $this->attributes['role_requirement'] = $value ?: 'all';
    }

    public function getBillingIntervalAttribute(): string
    {
        $days = (int) ($this->billing_cycle_days ?? 30);
        if ($days >= 365) {
            return 'yearly';
        }
        if ($days >= 3650) {
            return 'lifetime';
        }
        return 'monthly';
    }

    public function setBillingIntervalAttribute($value): void
    {
        if ($value === 'yearly') {
            $this->attributes['billing_cycle_days'] = 365;
        } elseif ($value === 'lifetime') {
            $this->attributes['billing_cycle_days'] = 3650;
        }
    }

    public function getIsFeaturedAttribute(): bool
    {
        return (int) ($this->featured_listings_allowed ?? 0) > 0;
    }

    public function setIsFeaturedAttribute($value): void
    {
        $this->attributes['featured_listings_allowed'] = $value ? 5 : 0;
    }

    public function getFeaturesAttribute(): array
    {
        if (!empty($this->eligibility_notes)) {
            $decoded = json_decode($this->eligibility_notes, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            return array_values(array_filter(array_map('trim', explode("\n", $this->eligibility_notes))));
        }
        return [];
    }

    public function setFeaturesAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['eligibility_notes'] = json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
        } else {
            $this->attributes['eligibility_notes'] = $value;
        }
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0.00;
    }
}
