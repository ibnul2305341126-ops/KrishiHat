<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferEvent extends Model
{
    protected $fillable = [
        'offer_id',
        'actor_id',
        'actor_role',
        'action',
        'price',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
