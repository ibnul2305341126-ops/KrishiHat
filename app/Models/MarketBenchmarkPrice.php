<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketBenchmarkPrice extends Model
{
    const TYPE_GROWER = 'grower';
    const TYPE_WHOLESALE = 'wholesale';
    const TYPE_RETAIL = 'retail';

    protected $fillable = [
        'crop_name_en',
        'crop_name_bn',
        'crop_variety',
        'category',
        'category_id',
        'market_name',
        'district',
        'upazila',
        'price_type',
        'price',
        'price_min',
        'price_max',
        'unit',
        'observation_date',
        'retrieval_date',
        'source_name',
        'source_url',
        'is_verified',
        'verification_status',
        'notes',
        'raw_data',
    ];

    protected $casts = [
        'price' => 'float',
        'price_min' => 'float',
        'price_max' => 'float',
        'observation_date' => 'date',
        'retrieval_date' => 'date',
        'is_verified' => 'boolean',
        'raw_data' => 'array',
    ];

    public function categoryRecord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function scopeWholesale($query)
    {
        return $query->where('price_type', 'wholesale');
    }

    public function scopeRetail($query)
    {
        return $query->where('price_type', 'retail');
    }

    public function scopeGrower($query)
    {
        return $query->where('price_type', 'grower');
    }

    public function getCropNameAttribute()
    {
        return $this->crop_name_bn ?: $this->crop_name_en;
    }

    public function getMarketAttribute()
    {
        return $this->market_name;
    }

    public function getPriceModalAttribute()
    {
        return $this->price;
    }

    public function getVarietyAttribute()
    {
        return $this->crop_variety;
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }
}
