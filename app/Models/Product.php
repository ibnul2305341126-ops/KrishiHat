<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'farmer_id',
        'category_id',
        'name',
        'category',
        'description',
        'price',
        'unit',
        'stock',
        'min_order_quantity',
        'image',
        'verification_status',
        'reference_image',
        'reference_image_source',
        'reference_image_license',
        'suggested_correction',
        'last_reviewed_at',
        'district',
        'upazila',
        'latitude',
        'longitude',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'urban_area_id',
        'post_office_id',
        'postal_code',
    ];

    protected $casts = [
        'min_order_quantity' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'last_reviewed_at' => 'datetime',
    ];

    public function getMinOrderQuantityAttribute($value): int
    {
        return max(1, (int) ($value ?? 1));
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class)->orderBy('position');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }

    public function districtRecord(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function upazilaRecord(): BelongsTo
    {
        return $this->belongsTo(Upazila::class, 'upazila_id');
    }

    public function union(): BelongsTo
    {
        return $this->belongsTo(Union::class, 'union_id');
    }

    public function unionRecord(): BelongsTo
    {
        return $this->belongsTo(Union::class, 'union_id');
    }

    public function urbanArea(): BelongsTo
    {
        return $this->belongsTo(UrbanArea::class, 'urban_area_id');
    }

    public function urbanAreaRecord(): BelongsTo
    {
        return $this->belongsTo(UrbanArea::class, 'urban_area_id');
    }

    public function postOffice(): BelongsTo
    {
        return $this->belongsTo(PostOffice::class, 'post_office_id');
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = [];

        if ($this->union) {
            $parts[] = $this->union->name_bn ?: $this->union->name_en;
        }

        if ($this->urbanArea) {
            $parts[] = $this->urbanArea->name_bn ?: $this->urbanArea->name_en;
        }

        if ($this->upazilaRecord) {
            $parts[] = ($this->upazilaRecord->name_bn ?: $this->upazilaRecord->name_en) . ' উপজেলা';
        } elseif (!empty($this->upazila)) {
            $parts[] = $this->upazila;
        }

        if ($this->districtRecord) {
            $parts[] = ($this->districtRecord->name_bn ?: $this->districtRecord->name_en) . ' জেলা';
        } elseif (!empty($this->district)) {
            $parts[] = $this->district;
        }

        if ($this->division) {
            $parts[] = ($this->division->name_bn ?: $this->division->name_en) . ' বিভাগ';
        }

        if (!empty($this->postal_code)) {
            $parts[] = 'পোস্ট কোড: ' . $this->postal_code;
        } elseif ($this->postOffice && !empty($this->postOffice->post_code)) {
            $parts[] = 'পোস্ট কোড: ' . $this->postOffice->post_code;
        }

        return !empty($parts) ? implode(', ', $parts) : 'অবস্থান নির্ধারিত নয়';
    }

    public function hasExactCoordinates(): bool
    {
        return !is_null($this->latitude)
            && !is_null($this->longitude)
            && is_numeric($this->latitude)
            && is_numeric($this->longitude)
            && $this->latitude >= -90
            && $this->latitude <= 90
            && $this->longitude >= -180
            && $this->longitude <= 180;
    }

    public function getGoogleMapsSearchUrlAttribute(): ?string
    {
        if ($this->hasExactCoordinates()) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode("{$this->latitude},{$this->longitude}");
        }

        $addressQuery = trim(($this->upazila ? $this->upazila . ', ' : '') . ($this->district ?? ''));
        if (!empty($addressQuery)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($addressQuery . ', Bangladesh');
        }

        return null;
    }

    public function getGoogleMapsDirectionsUrlAttribute(): ?string
    {
        if ($this->hasExactCoordinates()) {
            return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode("{$this->latitude},{$this->longitude}");
        }

        $addressQuery = trim(($this->upazila ? $this->upazila . ', ' : '') . ($this->district ?? ''));
        if (!empty($addressQuery)) {
            return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($addressQuery . ', Bangladesh');
        }

        return null;
    }

    public function getLocationDisplayAttribute(): string
    {
        if (!empty($this->district) && !empty($this->upazila)) {
            return "{$this->district} ({$this->upazila})";
        }

        if (!empty($this->district)) {
            return $this->district;
        }

        if (!empty($this->upazila)) {
            return $this->upazila;
        }

        return 'অবস্থান দেওয়া হয়নি';
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function averageRating(): float
    {
        return (float) round($this->reviews()->avg('rating') ?: 0, 1);
    }

    public function reviewsCount(): int
    {
        return (int) $this->reviews()->count();
    }
}
