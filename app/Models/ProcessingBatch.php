<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessingBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pomegranate_load_id', 'process_type', 'input_crates_count',
        'input_weight_kg', 'output_weight_kg', 'waste_weight_kg',
        'processed_at', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'input_weight_kg' => 'decimal:3',
            'output_weight_kg' => 'decimal:3',
            'waste_weight_kg' => 'decimal:3',
        ];
    }

    public function load(): BelongsTo { return $this->belongsTo(PomegranateLoad::class, 'pomegranate_load_id'); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
}
