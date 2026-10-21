<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['buyer_id', 'farmer_id', 'product_id', 'offer_id', 'context_key'];
    public function buyer(): BelongsTo { return $this->belongsTo(User::class, 'buyer_id'); }
    public function farmer(): BelongsTo { return $this->belongsTo(User::class, 'farmer_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function offer(): BelongsTo { return $this->belongsTo(Offer::class); }
    public function messages(): HasMany { return $this->hasMany(ConversationMessage::class); }
    public function includes(int $userId): bool { return in_array($userId, [$this->buyer_id, $this->farmer_id], true); }
}
