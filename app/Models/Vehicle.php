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
        'insurance_number',
        'insurance_expiry',
        'fitness_expiry',
        'permit_expiry',
        'puc_expiry',
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
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
        'permit_expiry' => 'date',
        'puc_expiry' => 'date',
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
            $base = basename($this->image);
            if (file_exists(public_path('assets/vehicles/'.$base))) {
                return asset('assets/vehicles/'.$base);
            }
            if (file_exists(public_path('assets/vehicle/'.$base))) {
                return asset('assets/vehicle/'.$base);
            }
            if (file_exists(public_path($this->image))) {
                return asset($this->image);
            }
            if (file_exists(public_path('assets/'.$this->image))) {
                return asset('assets/'.$this->image);
            }
        }

        // Map by vehicle name to genuine fleet photo
        $name = strtolower($this->name ?? '');
        if (str_contains($name, 'dzire')) {
            return asset('assets/vehicles/maruti-suzuki-dzire.jpg');
        }
        if (str_contains($name, 'ertiga')) {
            return asset('assets/vehicles/maruti-suzuki-ertiga.jpg');
        }
        if (str_contains($name, 'innova')) {
            return asset('assets/vehicles/toyota-innova-crysta.jpg');
        }
        if (str_contains($name, 'traveller') || str_contains($name, 'tempo')) {
            return asset('assets/vehicles/force-tempo-traveller.jpg');
        }
        if (str_contains($name, 'tiago') || str_contains($name, 'wagon')) {
            return asset('assets/vehicles/tata-tiago-wagonr.jpg');
        }

        // Map by vehicle type
        $type = strtolower($this->vehicle_type ?? '');
        if (str_contains($type, 'sedan')) {
            return asset('assets/vehicles/maruti-suzuki-dzire.jpg');
        }
        if (str_contains($type, 'innova') || str_contains($type, 'crysta')) {
            return asset('assets/vehicles/toyota-innova-crysta.jpg');
        }
        if (str_contains($type, 'muv')) {
            return asset('assets/vehicles/maruti-suzuki-ertiga.jpg');
        }
        if (str_contains($type, 'suv')) {
            return asset('assets/vehicles/maruti-suzuki-ertiga.jpg');
        }
        if (str_contains($type, 'tempo') || str_contains($type, 'traveller')) {
            return asset('assets/vehicles/force-tempo-traveller.jpg');
        }
        if (str_contains($type, 'hatchback')) {
            return asset('assets/vehicles/tata-tiago-wagonr.jpg');
        }

        return asset('assets/vehicles/maruti-suzuki-dzire.jpg');
    }

    /**
     * Get list of documents that are expired or expiring within $days.
     */
    public function getExpiringDocuments(int $days = 30): array
    {
        $threshold = now()->addDays($days)->toDateString();
        $today = now()->toDateString();
        $expiring = [];

        $docs = [
            'Insurance' => $this->insurance_expiry,
            'Fitness' => $this->fitness_expiry,
            'Permit' => $this->permit_expiry,
            'PUC' => $this->puc_expiry,
        ];

        foreach ($docs as $name => $expiry) {
            if ($expiry) {
                $expiryDate = $expiry->format('Y-m-d');
                if ($expiryDate < $today) {
                    $expiring[$name] = ['status' => 'expired', 'date' => $expiryDate, 'days' => $expiry->diffInDays(now()) * -1];
                } elseif ($expiryDate <= $threshold) {
                    $expiring[$name] = ['status' => 'expiring_soon', 'date' => $expiryDate, 'days' => now()->diffInDays($expiry)];
                }
            }
        }

        return $expiring;
    }
}
