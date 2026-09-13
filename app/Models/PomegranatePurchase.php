<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PomegranatePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'pomegranate_load_id', 'supplier_id', 'pricing_unit', 'unit_price',
        'quantity', 'total_amount', 'paid_amount', 'purchased_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:3',
            'quantity' => 'decimal:3',
            'total_amount' => 'decimal:3',
            'paid_amount' => 'decimal:3',
            'purchased_at' => 'date',
        ];
    }

    public function pomegranateLoad(): BelongsTo
    {
        return $this->belongsTo(PomegranateLoad::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function balance(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }
}
