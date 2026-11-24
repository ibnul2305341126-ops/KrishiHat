<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'recipient_name',
        'phone',
        'division_id',
        'district_id',
        'upazila_id',
        'union_id',
        'urban_area_id',
        'post_office_id',
        'postal_code',
        'street_address',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class);
    }

    public function union()
    {
        return $this->belongsTo(Union::class);
    }

    public function urbanArea()
    {
        return $this->belongsTo(UrbanArea::class);
    }

    public function postOffice()
    {
        return $this->belongsTo(PostOffice::class);
    }

    public function getFormattedAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street_address,
            $this->union?->name_bn ?: $this->union?->name_en,
            $this->urbanArea?->name_bn ?: $this->urbanArea?->name_en,
            $this->upazila?->name_bn ?: $this->upazila?->name_en,
            $this->district?->name_bn ?: $this->district?->name_en,
            $this->division?->name_bn ?: $this->division?->name_en,
            $this->postal_code ? "পোস্ট কোড: {$this->postal_code}" : null,
        ]);

        return implode(', ', $parts) ?: (string) $this->street_address;
    }
}
