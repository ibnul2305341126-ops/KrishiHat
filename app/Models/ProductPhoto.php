<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPhoto extends Model
{
    protected $fillable = ['path', 'position'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
