<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Division extends Model
{
    protected $fillable = [
        'name_en',
        'name_bn',
        'bbs_code',
        'source_url',
        'verified_at',
        'is_verified',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function districts(): HasMany
    {
        return $this->hasMany(District::class)->orderBy('name_en');
    }

    public function upazilas(): HasManyThrough
    {
        return $this->hasManyThrough(Upazila::class, District::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
