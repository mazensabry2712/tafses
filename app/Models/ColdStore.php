<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColdStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'crate_standard_id', 'current_crates_count', 'current_weight_kg', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'crate_standard_id' => 'integer',
            'current_crates_count' => 'integer',
            'current_weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function crateStandard(): BelongsTo
    {
        return $this->belongsTo(CrateStandard::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ColdStoreStock::class);
    }

    public function custodyTransactions(): HasMany
    {
        return $this->hasMany(CustodyTransaction::class);
    }

    public function netWeightFor(int $cratesCount): float
    {
        if (!$this->crateStandard) {
            throw new \RuntimeException('A crate standard must be assigned to the cold store.');
        }

        return $this->crateStandard->netWeightFor($cratesCount);
    }
}
