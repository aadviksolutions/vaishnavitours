<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'route',
        'duration',
        'duration_days',
        'destinations',
        'applicable_vehicles',
        'sedan_price',
        'ertiga_price',
        'innova_price',
        'terms',
        'is_active',
        'route_distance_km',
        'route_duration_text',
        'route_legs',
        'route_geometry',
        'route_locations',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'sedan_price' => 'decimal:2',
        'ertiga_price' => 'decimal:2',
        'innova_price' => 'decimal:2',
        'route_distance_km' => 'decimal:2',
        'route_legs' => 'array',
        'route_locations' => 'array',
        'is_active' => 'boolean',
    ];

    public function getBasePriceAttribute(): float
    {
        return (float) ($this->sedan_price ?? 0);
    }

    public function getRouteSummaryAttribute(): string
    {
        return $this->route ?? '';
    }

    /**
     * @return array<int, string>
     */
    public function getDestinationsListAttribute(): array
    {
        if (empty($this->destinations)) {
            return [];
        }

        if (is_array($this->destinations)) {
            return $this->destinations;
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $this->destinations))));
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
