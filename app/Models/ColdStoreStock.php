<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColdStoreStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'cold_store_id', 'pomegranate_load_id', 'crates_count', 'weight_kg',
    ];

    protected function casts(): array
    {
        return [
            'crates_count' => 'integer',
            'weight_kg' => 'decimal:3',
        ];
    }

    public function coldStore(): BelongsTo
    {
        return $this->belongsTo(ColdStore::class);
    }

    public function pomegranateLoad(): BelongsTo
    {
        return $this->belongsTo(PomegranateLoad::class);
    }
}
