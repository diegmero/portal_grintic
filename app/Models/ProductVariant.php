<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'name',
        'additional_price',
        'specs',
        'is_active'
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
        'specs' => 'array',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted additional price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->additional_price > 0) {
            return '+ $' . number_format($this->additional_price, 2);
        }
        return '';
    }
}
