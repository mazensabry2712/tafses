<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_product_id',
        'direction',
        'quantity',
        'reason',
        'adjusted_at',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'adjusted_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FinishedProduct::class, 'finished_product_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
