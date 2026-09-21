<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_name',
        'vehicle_type',
        'trip_type',
        'rate',
        'extra_km_rate',
        'waiting_charge',
        'night_charge',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'extra_km_rate' => 'decimal:2',
        'waiting_charge' => 'decimal:2',
        'night_charge' => 'decimal:2',
    ];
}
