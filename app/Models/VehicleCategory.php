<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'display_name',
        'description',
        'default_seating_capacity',
        'min_seating_capacity',
        'max_seating_capacity',
        'is_capacity_configurable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_seating_capacity' => 'integer',
        'min_seating_capacity' => 'integer',
        'max_seating_capacity' => 'integer',
        'is_capacity_configurable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_category_id')->orderBy('sort_order');
    }

    public function activeModels(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'vehicle_category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'vehicle_category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
