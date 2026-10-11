<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'alternate_mobile',
        'license_number',
        'license_expiry',
        'address',
        'status',
        'assigned_vehicle_id',
        'rating',
        'notes',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'rating' => 'decimal:2',
    ];

    public function assignedVehicle()
    {
        return $this->belongsTo(Vehicle::class, 'assigned_vehicle_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function getLicenseStatus(): array
    {
        if (! $this->license_expiry) {
            return ['status' => 'unknown', 'label' => 'Not recorded', 'class' => 'secondary'];
        }

        $today = now()->startOfDay();
        $expiry = $this->license_expiry->startOfDay();

        if ($expiry->isPast()) {
            return [
                'status' => 'expired',
                'label' => 'Expired ('.$expiry->format('d M Y').')',
                'class' => 'danger',
            ];
        }

        $days = (int) $today->diffInDays($expiry);
        if ($days <= 30) {
            return [
                'status' => 'expiring_soon',
                'label' => "Expires in {$days} days",
                'class' => 'warning',
            ];
        }

        return [
            'status' => 'valid',
            'label' => 'Valid till '.$expiry->format('d M Y'),
            'class' => 'success',
        ];
    }
}
