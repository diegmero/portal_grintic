<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'company_id',
        'plan_name',
        'price',
        'billing_cycle', // monthly, yearly
        'next_billing_date',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'next_billing_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
