<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedProductSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'finished_product_id', 'quantity', 'unit_price', 'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(FinishedProductSale::class, 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FinishedProduct::class, 'finished_product_id');
    }
}
