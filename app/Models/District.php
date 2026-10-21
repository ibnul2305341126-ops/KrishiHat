<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class District extends Model
{
    protected $fillable = [
        'division_id',
        'name_en',
        'name_bn',
        'bbs_code',
        'latitude',
        'longitude',
        'source_url',
        'verified_at',
        'is_verified',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function upazilas(): HasMany
    {
        return $this->hasMany(Upazila::class)->orderBy('name_en');
    }

    public function urbanAreas(): HasMany
    {
        return $this->hasMany(UrbanArea::class)->orderBy('name_en');
    }

    public function postOffices(): HasMany
    {
        return $this->hasMany(PostOffice::class)->orderBy('name_en');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
