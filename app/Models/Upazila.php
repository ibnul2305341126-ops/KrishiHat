<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upazila extends Model
{
    protected $fillable = [
        'district_id',
        'name_en',
        'name_bn',
        'bbs_code',
        'is_thana',
        'source_url',
        'verified_at',
        'is_verified',
    ];

    protected $casts = [
        'is_thana' => 'boolean',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function unions(): HasMany
    {
        return $this->hasMany(Union::class)->orderBy('name_en');
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
