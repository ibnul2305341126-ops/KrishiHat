<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_COMPLETED = 'successful';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    const TYPE_ORDER = 'order';
    const TYPE_SUBSCRIPTION = 'subscription';

    const METHOD_COD = 'cod';
    const METHOD_SSLCOMMERZ = 'sslcommerz';
    const METHOD_BKASH = 'bkash';
    const METHOD_NAGAD = 'nagad';
    const METHOD_CARD = 'card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_FREE = 'free';
    const METHOD_MANUAL_ADMIN = 'manual_admin';
    const METHOD_MANUAL = 'manual_admin';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'user_id',
        'payment_type',
        'payment_method',
        'amount',
        'currency',
        'transaction_id',
        'gateway_transaction_id',
        'gateway_session_key',
        'status',
        'payment_details',
        'gateway_response',
        'paid_at',
        'verified_by_admin_id',
        'verified_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_details' => 'array',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_admin_id');
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public static function generateTransactionId(string $prefix = 'KH-TXN'): string
    {
        return strtoupper($prefix . '-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8));
    }

    /**
     * Get Customer (Buyer or Subscriber) for this financial audit record.
     */
    public function getCustomerAttribute(): ?User
    {
        if ($this->payable instanceof Order && $this->payable->buyer) {
            return $this->payable->buyer;
        }

        return $this->user;
    }

    /**
     * Get all supplying Farmers involved in this transaction.
     */
    public function getFarmersAttribute(): \Illuminate\Support\Collection
    {
        if ($this->payable instanceof Order) {
            if (!$this->payable->relationLoaded('items')) {
                $this->payable->load('items.farmer');
            }

            return $this->payable->items
                ->map(fn($item) => $item->farmer)
                ->filter()
                ->unique('id')
                ->values();
        }

        return collect();
    }

    /**
     * Get primary farmer for quick table summaries.
     */
    public function getPrimaryFarmerAttribute(): ?User
    {
        return $this->farmers->first();
    }

    /**
     * Get itemized order products and pricing records.
     */
    public function getOrderItemsAttribute(): \Illuminate\Support\Collection
    {
        if ($this->payable instanceof Order) {
            if (!$this->payable->relationLoaded('items')) {
                $this->payable->load(['items.product', 'items.farmer']);
            }

            return $this->payable->items;
        }

        return collect();
    }

    /**
     * Unique cryptographic security verification audit code.
     */
    public function getSecurityVerificationCodeAttribute(): string
    {
        $payload = $this->id . '|' . $this->transaction_id . '|' . ($this->created_at?->timestamp ?? 0);
        return 'SEC-' . strtoupper(substr(hash('sha256', $payload), 0, 10));
    }

    /**
     * Human-readable Bengali payment method label.
     */
    public function getPaymentMethodBanglaAttribute(): string
    {
        return match (strtolower((string)$this->payment_method)) {
            'bkash' => 'বিকাশ (bKash)',
            'nagad' => 'নগদ (Nagad)',
            'sslcommerz' => 'SSLCOMMERZ গেটওয়ে',
            'card' => 'কার্ড (Card / Visa)',
            'bank_transfer' => 'ব্যাংক ট্রান্সফার',
            'cod' => 'ক্যাশ অন ডেলিভারি (COD)',
            'free' => 'বিনামূল্যে (Free Trial)',
            'manual_admin' => 'ম্যানুয়াল অ্যাডমিন অনুমোদন',
            default => strtoupper((string)$this->payment_method),
        };
    }
}
