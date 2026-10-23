<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UrbanArea extends Model
{
    protected $fillable = [
        'district_id',
        'upazila_id',
        'name_en',
        'name_bn',
        'type', // municipality, paurashava, city_corporation, thana_ward
        'ward_number',
        'source_url',
        'verified_at',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function upazila(): BelongsTo
    {
        return $this->belongsTo(Upazila::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'city_corporation' => 'সিটি কর্পোরেশন (City Corporation)',
            'municipality', 'paurashava' => 'পৌরসভা (Municipality)',
            'thana_ward' => 'থানা ও ওয়ার্ড (Thana / Ward)',
            default => 'পৌরসভা/শহরাঞ্চল',
        };
    }
}
