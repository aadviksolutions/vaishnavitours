<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'journey_status',
        'trip_status',
        'started_at',
        'completed_at',
        'closed_at',
        'closed_by',
        'closure_reason',
        'route_notes',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_odometer' => 'integer',
        'end_odometer' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isPaymentPending(): bool
    {
        return $this->booking && $this->booking->payment_status !== 'Paid' && $this->booking->balance_amount > 0;
    }

    public function isFullyClosed(): bool
    {
        return $this->trip_status === 'Closed';
    }

    public function isFinanciallySettled(): bool
    {
        if ($this->trip_status === 'Closed') {
            return true;
        }

        $booking = $this->booking;
        if (! $booking) {
            return false;
        }

        return (float) $booking->balance_amount <= 0.00 || in_array($booking->payment_status, ['Paid', 'Completed'], true);
    }

    public function hasOutstandingBalance(): bool
    {
        return ! $this->isFinanciallySettled();
    }
}
