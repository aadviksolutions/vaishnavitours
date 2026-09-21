<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    public const WORKFLOW_TRANSITIONS = [
        'Pending' => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Vehicle Assigned', 'Cancelled'],
        'Vehicle Assigned' => ['Driver Assigned', 'Cancelled'],
        'Driver Assigned' => ['Trip Started', 'Cancelled'],
        'Trip Started' => ['On The Way', 'Cancelled'],
        'On The Way' => ['Completed', 'Cancelled'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    protected $fillable = [
        'booking_id',
        'customer_id',
        'trip_type',
        'pickup_location',
        'destination',
        'travel_date',
        'travel_time',
        'return_date',
        'vehicle_id',
        'driver_id',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'booking_status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
        'cancellation_status',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public static function generateBookingId(): string
    {
        $latest = static::where('booking_id', 'REGEXP', '^VT-[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(booking_id, 4) AS UNSIGNED) DESC')
            ->first();

        if ($latest && preg_match('/^VT-(\d+)$/', $latest->booking_id, $matches)) {
            $next = max(1001, (int)$matches[1] + 1);
        } else {
            $count = static::count();
            $next = 1001 + $count;
        }

        return 'VT-' . $next;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_id)) {
                $booking->booking_id = static::generateBookingId();
            }
            $booking->recalculateFinancials();
        });

        static::updating(function ($booking) {
            $booking->recalculateFinancials();
        });
    }

    public function recalculateFinancials(): void
    {
        $total = (float)$this->total_amount;
        $paid = (float)$this->paid_amount;
        $this->balance_amount = max(0, $total - $paid);

        if ($this->payment_status !== 'Refunded') {
            if ($total > 0 && $paid >= $total) {
                $this->payment_status = 'Paid';
            } elseif ($paid > 0) {
                $this->payment_status = 'Partial';
            } else {
                $this->payment_status = 'Pending';
            }
        }
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->booking_status === $targetStatus) {
            return true;
        }

        $allowed = self::WORKFLOW_TRANSITIONS[$this->booking_status] ?? [];
        return in_array($targetStatus, $allowed, true);
    }

    public function isCancelled(): bool
    {
        return $this->booking_status === 'Cancelled';
    }

    public function isCompleted(): bool
    {
        return $this->booking_status === 'Completed';
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class)->latest('created_at');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }
}
