<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\ProductCategory;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductAddon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'product_category_id', // Replaces 'category' enum eventually
        'category', // Keep for backward compat until full migration
        'type',
        'billing_cycle',
        'base_price',
        'description',
        'is_active',
        'features',
    ];

    protected $casts = [
        'category' => ProductCategory::class,
        'type' => ProductType::class,
        'billing_cycle' => BillingCycle::class,
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function productCategory(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ProductCategory::class);
    }



    public function addons(): HasMany
    {
        return $this->hasMany(ProductAddon::class);
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }

    /**
     * Get formatted price with currency
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->base_price, 2);
    }

    /**
     * Scope to only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
