<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinishedProduct extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'type', 'unit', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function stock(): HasOne
    {
        return $this->hasOne(FinishedProductStock::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinishedProductTransaction::class);
    }
}
