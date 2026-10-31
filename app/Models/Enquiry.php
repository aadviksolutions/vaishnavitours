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
        'pickup_latitude',
        'pickup_longitude',
        'pickup_place_id',
        'via_location',
        'via_latitude',
        'via_longitude',
        'via_place_id',
        'destination',
        'destination_latitude',
        'destination_longitude',
        'destination_place_id',
        'route_distance_km',
        'route_duration_text',
        'route_duration_minutes',
        'route_legs',
        'route_geometry',
        'routed_at',
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
        'routed_at' => 'datetime',
        'duration_days' => 'integer',
        'quotation_amount' => 'decimal:2',
        'route_distance_km' => 'decimal:2',
        'route_duration_minutes' => 'integer',
        'route_legs' => 'array',
        'pickup_latitude' => 'decimal:7',
        'pickup_longitude' => 'decimal:7',
        'via_latitude' => 'decimal:7',
        'via_longitude' => 'decimal:7',
        'destination_latitude' => 'decimal:7',
        'destination_longitude' => 'decimal:7',
    ];

    public static function generateEnquiryId(?string $source = null): string
    {
        $prefix = match (strtolower((string) $source)) {
            'corporate', 'corp' => 'ENQ-CORP',
            'third_party', 'third-party', 'tp' => 'ENQ-TP',
            'manual', 'man' => 'ENQ-MAN',
            default => 'ENQ',
        };
        $date = date('ymd');
        $count = static::count() + 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($enquiry) {
            if (empty($enquiry->enquiry_id)) {
                $enquiry->enquiry_id = static::generateEnquiryId($enquiry->source);
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

    public function assignedUser(): BelongsTo
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
