<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PomegranateLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'load_number', 'supplier_id', 'vehicle_id', 'received_at',
        'loaded_crates_count', 'loaded_weight_kg',
        'on_vehicle_crates_count', 'on_vehicle_weight_kg',
        'available_crates_count', 'available_weight_kg', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'loaded_weight_kg' => 'decimal:3',
            'on_vehicle_weight_kg' => 'decimal:3',
            'available_weight_kg' => 'decimal:3',
        ];
    }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function crateMovements(): HasMany { return $this->hasMany(LoadCrateMovement::class); }
    public function processingBatches(): HasMany { return $this->hasMany(ProcessingBatch::class); }
    public function coldStoreStocks(): HasMany { return $this->hasMany(ColdStoreStock::class); }
    public function purchase(): HasOne { return $this->hasOne(PomegranatePurchase::class); }

    public function refreshStatus(): void
    {
        $this->status = ($this->on_vehicle_crates_count === 0 && round((float) $this->on_vehicle_weight_kg, 3) <= 0)
            ? 'unloaded'
            : 'open';
    }
}
