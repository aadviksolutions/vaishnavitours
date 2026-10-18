<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    use HasFactory;

    public const SOURCE_WEBSITE = 'Website';

    public const SOURCE_MANUAL = 'Manual';

    public const SOURCE_CORPORATE = 'Corporate';

    public const SOURCE_THIRD_PARTY = 'Third-Party';

    public const STATUS_NEW = 'New';

    public const STATUS_CONTACTED = 'Contacted';

    public const STATUS_QUOTATION_SENT = 'Quotation Sent';

    public const STATUS_CONVERTED = 'Converted';

    public const STATUS_LOST = 'Lost';

    protected $fillable = [
        'enquiry_id',
        'source',
        'name',
        'email',
        'phone',
        'service_type',
        'booking_type',
        'pickup_location',
        'via_location',
        'destination',
        'travel_date',
        'return_date',
        'duration_days',
        'vehicle_preference',
        'quotation_amount',
        'subject',
        'message',
        'notes',
        'status',
        'assigned_to',
        'converted_booking_id',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'duration_days' => 'integer',
        'quotation_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enquiry) {
            if (empty($enquiry->enquiry_id)) {
                $count = static::count() + 1;
                $enquiry->enquiry_id = sprintf('ENQ-%s-%04d', date('ymd'), $count);
            }
            if (empty($enquiry->source)) {
                $enquiry->source = self::SOURCE_WEBSITE;
            }
            if (empty($enquiry->status)) {
                $enquiry->status = self::STATUS_NEW;
            }
        });
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'converted_booking_id');
    }

    public function scopeWebsite($query)
    {
        return $query->where('source', self::SOURCE_WEBSITE);
    }

    public function scopeManual($query)
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    public function scopeCorporate($query)
    {
        return $query->where('source', self::SOURCE_CORPORATE);
    }

    public function scopeThirdParty($query)
    {
        return $query->where('source', self::SOURCE_THIRD_PARTY);
    }
}
