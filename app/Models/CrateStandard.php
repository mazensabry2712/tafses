<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrateStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'gross_weight_kg', 'tare_weight_kg', 'is_active', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'gross_weight_kg' => 'decimal:3',
            'tare_weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function netWeightKg(): float
    {
        return round((float) $this->gross_weight_kg - (float) $this->tare_weight_kg, 3);
    }

    public function netWeightFor(int $cratesCount): float
    {
        return round($this->netWeightKg() * $cratesCount, 3);
    }
}
