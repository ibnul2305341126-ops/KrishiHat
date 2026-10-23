<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostOffice extends Model
{
    protected $fillable = [
        'district_id',
        'upazila_id',
        'name_en',
        'name_bn',
        'post_code',
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
}
