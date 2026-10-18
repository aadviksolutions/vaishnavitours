<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    public const CATEGORY_CORP = 'CORP';

    public const CATEGORY_WEB = 'WEB';

    public const CATEGORY_MAN = 'MAN';

    public const CATEGORY_TP = 'TP';

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
        'booking_category',
        'customer_id',
        'trip_type',
        'pickup_location',
        'via_location',
        'destination',
        'travel_date',
        'travel_time',
        'return_date',
        'return_time',
        'duration_days',
        'duration_hours',
        'duration_notes',
        'vehicle_id',
        'driver_id',
        'tour_package_id',
        'rate_vehicle_price_id',
        'rate_category',
        'pricing_details',
        'corporate_company_name',
        'corporate_contact_person',
        'corporate_phone',
        'corporate_email',
        'corporate_gstin',
        'corporate_billing_address',
        'corporate_payment_terms',
        'corporate_agreed_rate',
        'travel_partner_name',
        'travel_partner_phone',
        'travel_partner_email',
        'travel_partner_address',
        'travel_partner_commission',
        'travel_partner_notes',
        'taxable_amount',
        'gst_rate',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'gst_amount',
        'is_interstate',
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
        'terms_accepted',
        'terms_accepted_at',
        'terms_version',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'cancelled_at' => 'datetime',
        'duration_days' => 'integer',
        'duration_hours' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'is_interstate' => 'boolean',
        'corporate_agreed_rate' => 'decimal:2',
        'travel_partner_commission' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'terms_accepted' => 'boolean',
        'terms_accepted_at' => 'datetime',
        'pricing_details' => 'array',
    ];

    public static function generateBookingId(?string $category = null): string
    {
        $prefix = strtoupper(trim((string) ($category ?: self::CATEGORY_WEB)));
        if (! in_array($prefix, [self::CATEGORY_CORP, self::CATEGORY_WEB, self::CATEGORY_MAN, self::CATEGORY_TP], true)) {
            $prefix = self::CATEGORY_WEB;
        }

        // Search for highest numeric sequence with this prefix
        $pattern = $prefix.'-%';
        $latest = static::where('booking_id', 'like', $pattern)
            ->orderBy('id', 'desc')
            ->pluck('booking_id')
            ->first();

        $maxSeq = 0;
        if ($latest && preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', (string) $latest, $m)) {
            $maxSeq = (int) $m[1];
        }

        // Also check if any collision exists, increment until free
        do {
            $maxSeq++;
            $candidate = sprintf('%s-%06d', $prefix, $maxSeq);
        } while (static::where('booking_id', $candidate)->exists());

        return $candidate;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_category)) {
                $booking->booking_category = self::CATEGORY_WEB;
            }
            if (empty($booking->booking_id)) {
                $booking->booking_id = static::generateBookingId($booking->booking_category);
            }
            $booking->recalculateFinancials();
        });

        static::updating(function ($booking) {
            $booking->recalculateFinancials();
        });
    }

    public function recalculateFinancials(): void
    {
        $total = (float) $this->total_amount;
        $paid = (float) $this->paid_amount;
        $this->balance_amount = max(0, round($total - $paid, 2));

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

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->booking_category) {
            self::CATEGORY_CORP => 'Corporate Booking',
            self::CATEGORY_WEB => 'Website Booking',
            self::CATEGORY_MAN => 'Manual Booking',
            self::CATEGORY_TP => 'Third-Party Booking',
            default => $this->booking_category ?? 'Website Booking',
        };
    }

    public function getCategoryBadgeAttribute(): array
    {
        return match ($this->booking_category) {
            self::CATEGORY_CORP => ['label' => 'CORP', 'full' => 'Corporate', 'class' => 'bg-purple-100 text-purple-800 border-purple-200'],
            self::CATEGORY_WEB => ['label' => 'WEB', 'full' => 'Website', 'class' => 'bg-blue-100 text-blue-800 border-blue-200'],
            self::CATEGORY_MAN => ['label' => 'MAN', 'full' => 'Manual', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
            self::CATEGORY_TP => ['label' => 'TP', 'full' => 'Partner', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
            default => ['label' => 'WEB', 'full' => 'Website', 'class' => 'bg-gray-100 text-gray-800 border-gray-200'],
        };
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function rateVehiclePrice(): BelongsTo
    {
        return $this->belongsTo(RateVehiclePrice::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function trip(): HasOne
    {
        return $this->hasOne(Trip::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function thirdPartyPayments(): HasMany
    {
        return $this->hasMany(ThirdPartyPayment::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class)->latest('created_at');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
