<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['plate_number', 'type', 'driver_name', 'driver_phone', 'notes'];

    public function pomegranateLoads(): HasMany
    {
        return $this->hasMany(PomegranateLoad::class);
    }
}
