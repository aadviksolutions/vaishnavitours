<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'vehicle_type',
        'vehicle_category_id',
        'vehicle_model_id',
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
        'is_third_party',
        'vendor_name',
        'vendor_phone',
        'vendor_email',
        'vendor_agreed_amount',
        'vendor_paid_amount',
        'vendor_balance_amount',
        'vendor_payment_status',
        'ownership_type',
    ];

    public function getOwnershipTypeAttribute(): string
    {
        return $this->is_third_party ? 'third_party' : 'owned';
    }

    public function setOwnershipTypeAttribute($value): void
    {
        $this->attributes['is_third_party'] = ($value === 'third_party');
    }

    protected $casts = [
        'per_km_rate' => 'decimal:2',
        'per_hour_rate' => 'decimal:2',
        'seating_capacity' => 'integer',
        'insurance_expiry' => 'date',
        'fitness_expiry' => 'date',
        'permit_expiry' => 'date',
        'puc_expiry' => 'date',
        'is_third_party' => 'boolean',
        'vendor_agreed_amount' => 'decimal:2',
        'vendor_paid_amount' => 'decimal:2',
        'vendor_balance_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Vehicle $vehicle) {
            if (empty($vehicle->vehicle_type)) {
                if ($vehicle->vehicle_category_id) {
                    $cat = VehicleCategory::find($vehicle->vehicle_category_id);
                    $vehicle->vehicle_type = $cat?->name ?? 'Sedan';
                } else {
                    $vehicle->vehicle_type = 'Sedan';
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function assignedDriver(): HasOne
    {
        return $this->hasOne(Driver::class, 'assigned_vehicle_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function thirdPartyPayments(): HasMany
    {
        return $this->hasMany(ThirdPartyPayment::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'Available');
    }

    public function scopeOwned($query)
    {
        return $query->where('is_third_party', false);
    }

    public function scopeThirdParty($query)
    {
        return $query->where('is_third_party', true);
    }

    public function recalculateVendorBalance(): void
    {
        $agreed = (float) $this->vendor_agreed_amount;
        $paid = (float) $this->thirdPartyPayments()->sum('amount');
        $this->vendor_paid_amount = $paid;
        $this->vendor_balance_amount = max(0, round($agreed - $paid, 2));

        if ($agreed > 0 && $paid >= $agreed) {
            $this->vendor_payment_status = 'Paid';
        } elseif ($paid > 0) {
            $this->vendor_payment_status = 'Partial';
        } else {
            $this->vendor_payment_status = 'Pending';
        }

        $this->saveQuietly();
    }

    /**
     * Check if mandatory documents (Insurance, PUC) are expired.
     * Note: Third-Party vehicles from travel partners do NOT require documents. Only vehicle number is mandatory.
     */
    public function hasExpiredMandatoryDocuments(): bool
    {
        if ($this->is_third_party || ($this->attributes['is_third_party'] ?? false)) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->insurance_expiry && $this->insurance_expiry->startOfDay()->isPast()) {
            return true;
        }

        if ($this->puc_expiry && $this->puc_expiry->startOfDay()->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Get detailed status of a specific document (Valid, Expiring Soon, Expired, Missing).
     */
    public function getDocumentStatus(string $field): array
    {
        $date = $this->{$field};
        if (! $date) {
            if ($this->is_third_party || ($this->attributes['is_third_party'] ?? false)) {
                return ['status' => 'not_required', 'label' => 'Not Required (Partner)', 'class' => 'bg-amber-50 text-amber-800 border-amber-200'];
            }

            return ['status' => 'missing', 'label' => 'Not Uploaded', 'class' => 'bg-gray-100 text-gray-700 border-gray-300'];
        }

        $today = now()->startOfDay();
        $expiry = $date->startOfDay();

        if ($expiry->isPast()) {
            return [
                'status' => 'expired',
                'label' => 'Expired ('.$expiry->format('d M Y').')',
                'class' => 'bg-red-100 text-red-800 border-red-300',
            ];
        }

        $days = (int) $today->diffInDays($expiry);
        if ($days <= 30) {
            return [
                'status' => 'expiring_soon',
                'label' => "Expiring Soon ({$days}d)",
                'class' => 'bg-amber-100 text-amber-800 border-amber-300',
            ];
        }

        return [
            'status' => 'valid',
            'label' => 'Valid till '.$expiry->format('d M Y'),
            'class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        ];
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

        if (str_contains($name, 'urbania')) {
            return asset('assets/vehicles/force-urbania.jpg');
        }
        if (str_contains($name, 'bus') || str_contains($name, 'coach')) {
            return asset('assets/vehicles/luxury-bus-coach.jpg');
        }

        $type = strtolower($this->vehicle_type ?? '');
        if (str_contains($type, 'sedan')) {
            return asset('assets/vehicles/maruti-suzuki-dzire.jpg');
        }
        if (str_contains($type, 'innova') || str_contains($type, 'crysta')) {
            return asset('assets/vehicles/toyota-innova-crysta.jpg');
        }
        if (str_contains($type, 'urbania')) {
            return asset('assets/vehicles/force-urbania.jpg');
        }
        if (str_contains($type, 'muv') || str_contains($type, 'suv')) {
            return asset('assets/vehicles/maruti-suzuki-ertiga.jpg');
        }
        if (str_contains($type, 'tempo') || str_contains($type, 'traveller')) {
            return asset('assets/vehicles/force-tempo-traveller.jpg');
        }
        if (str_contains($type, 'bus') || str_contains($type, 'coach')) {
            return asset('assets/vehicles/luxury-bus-coach.jpg');
        }
        if (str_contains($type, 'hatchback')) {
            return asset('assets/vehicles/tata-tiago-wagonr.jpg');
        }

        return asset('assets/vehicles/maruti-suzuki-dzire.jpg');
    }

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
