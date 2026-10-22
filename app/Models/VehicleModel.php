<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_category_id',
        'name',
        'brand',
        'default_seating_capacity',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_seating_capacity' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_model_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_model_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
