<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoadCrateMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'pomegranate_load_id', 'cold_store_id', 'direction', 'crates_count', 'weight_kg',
        'reason', 'moved_at', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
            'weight_kg' => 'decimal:3',
        ];
    }

    public function pomegranateLoad(): BelongsTo
    {
        return $this->belongsTo(PomegranateLoad::class, 'pomegranate_load_id');
    }

    public function coldStore(): BelongsTo
    {
        return $this->belongsTo(ColdStore::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
