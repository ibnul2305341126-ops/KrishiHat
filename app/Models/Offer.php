<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Offer extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COUNTERED = 'countered';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'product_id',
        'buyer_id',
        'farmer_id',
        'initial_price',
        'current_price',
        'quantity',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_price' => 'decimal:2',
            'current_price' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(OfferEvent::class)->oldest();
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class, 'accepted_offer_id');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_COUNTERED,
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN,
        ];
    }
}
