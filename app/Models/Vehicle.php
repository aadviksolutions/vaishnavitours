<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'vehicle_type',
        'seating_capacity',
        'ac_non_ac',
        'per_km_rate',
        'per_hour_rate',
        'status',
        'image',
        'notes',
    ];

    protected $casts = [
        'per_km_rate' => 'decimal:2',
        'per_hour_rate' => 'decimal:2',
        'seating_capacity' => 'integer',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function assignedDriver()
    {
        return $this->hasOne(Driver::class, 'assigned_vehicle_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function getIconUrlAttribute(): string
    {
        if ($this->image) {
            if (file_exists(public_path($this->image))) {
                return asset($this->image);
            }
            if (file_exists(public_path('assets/' . $this->image))) {
                return asset('assets/' . $this->image);
            }
            if (file_exists(public_path('assets/images/' . $this->image))) {
                return asset('assets/images/' . $this->image);
            }
        }

        $type = strtolower($this->vehicle_type);
        if (str_contains($type, 'sedan')) return asset('assets/images/sedan.jpg');
        if (str_contains($type, 'innova')) return asset('assets/images/muv.jpg');
        if (str_contains($type, 'muv')) return asset('assets/images/muv.jpg');
        if (str_contains($type, 'suv')) return asset('assets/images/suv.jpg');
        if (str_contains($type, 'tempo') || str_contains($type, 'traveller')) return asset('assets/images/traveller.jpg');

        return asset('assets/images/sedan.jpg');
    }
}
