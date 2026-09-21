<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'vehicle_id',
        'driver_id',
        'start_odometer',
        'end_odometer',
        'status',
        'trip_status',
        'started_at',
        'completed_at',
        'route_notes',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($trip) {
            if ($trip->trip_status && !$trip->status) {
                $trip->status = $trip->trip_status;
            } elseif ($trip->status && !$trip->trip_status) {
                $trip->trip_status = $trip->status;
            }
        });
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
