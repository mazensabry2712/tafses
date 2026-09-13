<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'pomegranate_purchase_id', 'amount',
        'paid_at', 'payment_method', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'paid_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(PomegranatePurchase::class, 'pomegranate_purchase_id');
    }
}
