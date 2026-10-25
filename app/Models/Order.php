<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const INITIAL_STATUS = 'pending';

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';
    public const PAYMENT_STATUS_PENDING = 'pending';
    public const PAYMENT_STATUS_PAID = 'paid';
    public const PAYMENT_STATUS_FAILED = 'failed';
    public const PAYMENT_STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'status',
        'tracking_code',
        'delivery_address',
        'phone',
        'payment_method',
        'payment_status',
        'driver_name',
        'driver_phone',
        'vehicle_number',
        'current_location_text',
        'current_latitude',
        'current_longitude',
        'estimated_delivery_at',
        'accepted_offer_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'estimated_delivery_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (Order $order) {
            if (! $order->tracking_code) {
                $order->tracking_code = self::generateTrackingCode((int) $order->id);
                $order->saveQuietly();
            }

            if ($order->trackingEvents()->count() === 0) {
                $order->recordTrackingEvent(
                    $order->status,
                    'অর্ডার গ্রহণ করা হয়েছে (Order Placed)',
                    'অর্ডার সফলভাবে ডাটাবেজে রেকর্ড করা হয়েছে।',
                    $order->delivery_address
                );
            }
        });
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function acceptedOffer(): BelongsTo
    {
        return $this->belongsTo(Offer::class, 'accepted_offer_id');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public static function paymentStatusLabels(): array
    {
        return [
            self::PAYMENT_STATUS_UNPAID => 'বকেয়া / অপরিশোধিত (Unpaid)',
            self::PAYMENT_STATUS_PENDING => 'যাচাইনাধীন (Payment Pending)',
            self::PAYMENT_STATUS_PAID => 'পরিশোধিত (Paid)',
            self::PAYMENT_STATUS_FAILED => 'ব্যর্থ (Failed)',
            self::PAYMENT_STATUS_REFUNDED => 'রিফান্ডকৃত (Refunded)',
        ];
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::paymentStatusLabels()[$this->payment_status] ?? ucfirst((string) $this->payment_status);
    }

    public static function paymentMethodLabels(): array
    {
        return [
            'cod' => 'ক্যাশ অন ডেলিভারি (Cash on Delivery)',
            'sslcommerz' => 'SSLCOMMERZ ডিজিটাল গেটওয়ে',
            'bkash' => 'বিকাশ (bKash Mobile Banking)',
            'nagad' => 'নগদ (Nagad Mobile Banking)',
            'card' => 'ভিসা / মাস্টারকার্ড (Debit/Credit Card)',
            'bank_transfer' => 'ব্যাংক ডিপোজিট / ট্রান্সফার (Manual Verification)',
        ];
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::paymentMethodLabels()[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(OrderTrackingEvent::class)->orderBy('created_at', 'asc');
    }

    public static function allStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function knownStatuses(): array
    {
        return collect(self::allStatuses())
            ->merge(static::query()->distinct()->pluck('status'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'অপেক্ষমান (Pending)',
            self::STATUS_CONFIRMED => 'নিশ্চিত (Confirmed)',
            self::STATUS_PROCESSING => 'প্রক্রিয়াধীন (Processing)',
            self::STATUS_SHIPPED => 'সরবরাহ চলমান (Shipped / In Transit)',
            self::STATUS_DELIVERED => 'ডেলিভারি সম্পন্ন (Delivered)',
            self::STATUS_CANCELLED => 'বাতিল (Cancelled)',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public static function generateTrackingCode(int $orderId): string
    {
        return 'KH-TRK-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
    }

    public function recordTrackingEvent(
        string $status,
        string $title,
        ?string $description = null,
        ?string $location = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $actorId = null
    ): OrderTrackingEvent {
        return $this->trackingEvents()->create([
            'status' => $status,
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'actor_id' => $actorId,
        ]);
    }

    public function getDeliveryGoogleMapsUrlAttribute(): ?string
    {
        if (empty($this->delivery_address)) {
            return null;
        }

        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($this->delivery_address . ', Bangladesh');
    }

    public function getDeliveryGoogleMapsDirectionsUrlAttribute(): ?string
    {
        if (empty($this->delivery_address)) {
            return null;
        }

        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($this->delivery_address . ', Bangladesh');
    }

    public function getCurrentLocationGoogleMapsUrlAttribute(): ?string
    {
        if (!is_null($this->current_latitude) && !is_null($this->current_longitude)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode("{$this->current_latitude},{$this->current_longitude}");
        }

        if (!empty($this->current_location_text)) {
            return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($this->current_location_text . ', Bangladesh');
        }

        return null;
    }
}
