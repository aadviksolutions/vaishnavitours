<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateVehiclePrice extends Model
{
    protected $fillable = [
        'rate_route_id',
        'vehicle_category',
        'base_fare',
        'gst_applicable',
        'extra_km_rate',
        'extra_hour_rate',
        'vehicle_rent',
        'per_km_rate',
        'night_charge',
        'toll_type',
        'parking_type',
        'border_tax_type',
        'driver_food_type',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'gst_applicable' => 'boolean',
        'extra_km_rate' => 'decimal:2',
        'extra_hour_rate' => 'decimal:2',
        'vehicle_rent' => 'decimal:2',
        'per_km_rate' => 'decimal:2',
        'night_charge' => 'decimal:2',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(RateRoute::class, 'rate_route_id');
    }
}
