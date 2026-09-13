<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishedProductTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'finished_product_id', 'processing_batch_id', 'type', 'quantity',
        'moved_at', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'moved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(FinishedProduct::class, 'finished_product_id');
    }

    public function processingBatch(): BelongsTo
    {
        return $this->belongsTo(ProcessingBatch::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
