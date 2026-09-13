<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustodyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'custodian_id', 'cold_store_id', 'pomegranate_load_id', 'type',
        'crates_count', 'weight_kg', 'moved_at', 'recorded_by', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'crates_count' => 'integer',
            'weight_kg' => 'decimal:3',
            'moved_at' => 'datetime',
        ];
    }

    public function custodian(): BelongsTo
    {
        return $this->belongsTo(Custodian::class);
    }

    public function coldStore(): BelongsTo
    {
        return $this->belongsTo(ColdStore::class);
    }

    public function pomegranateLoad(): BelongsTo
    {
        return $this->belongsTo(PomegranateLoad::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
