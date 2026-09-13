<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Custodian extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function custodyTransactions(): HasMany
    {
        return $this->hasMany(CustodyTransaction::class);
    }
}
