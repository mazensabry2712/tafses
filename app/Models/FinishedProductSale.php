<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinishedProductSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'invoice_number', 'sold_at', 'subtotal', 'total_amount',
        'paid_amount', 'status', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'sold_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FinishedProductSaleItem::class, 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class, 'sale_id');
    }

    public function balance(): float
    {
        return round((float) $this->total_amount - (float) $this->paid_amount, 2);
    }
}
