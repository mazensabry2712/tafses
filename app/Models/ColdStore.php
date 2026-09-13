<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColdStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'current_crates_count', 'current_weight_kg', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'current_crates_count' => 'integer',
            'current_weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ColdStoreStock::class);
    }

    public function custodyTransactions(): HasMany
    {
        return $this->hasMany(CustodyTransaction::class);
    }
}
