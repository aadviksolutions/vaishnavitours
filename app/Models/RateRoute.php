<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RateRoute extends Model
{
    protected $fillable = [
        'category',
        'origin',
        'destination',
        'km_limit',
        'included_hours',
        'return_same_rate',
        'active',
    ];

    protected $casts = [
        'km_limit' => 'integer',
        'included_hours' => 'decimal:2',
        'return_same_rate' => 'boolean',
        'active' => 'boolean',
    ];

    public function vehiclePrices(): HasMany
    {
        return $this->hasMany(RateVehiclePrice::class);
    }
}
