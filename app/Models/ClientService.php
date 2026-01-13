<?php

namespace App\Models;

use App\Enums\ClientServiceStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ClientService extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_variant_id',
        'custom_price',
        'start_date',
        'end_date',
        'status',
        'credentials',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'custom_price' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ClientServiceStatus::class,
        ];
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Encrypt credentials when storing
     */
    protected function credentials(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Get the effective price (custom or base)
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->custom_price) {
            return $this->custom_price;
        }

        $price = $this->product->base_price;

        if ($this->variant) {
            $price += $this->variant->additional_price;
        }

        return $price;
    }

    /**
     * Get formatted effective price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->effective_price, 2);
    }

    /**
     * Check if service is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Scope to active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', ClientServiceStatus::ACTIVE);
    }

    /**
     * Scope to services expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('end_date', [now(), now()->addDays(30)]);
    }
}
