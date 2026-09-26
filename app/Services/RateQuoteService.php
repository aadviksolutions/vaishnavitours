<?php

namespace App\Services;

use App\Models\RateRoute;
use App\Models\RateVehiclePrice;

class RateQuoteService
{
    /**
     * @return array{route: RateRoute, price: RateVehiclePrice, total_amount: float, details: array<string, mixed>}|null
     */
    public function quote(
        string $category,
        string $origin,
        string $destination,
        string $vehicleCategory,
        ?float $distanceKm = null
    ): ?array {
        $route = $this->findRoute($category, $origin, $destination);
        if (! $route) {
            return null;
        }

        $price = $route->vehiclePrices
            ->first(fn (RateVehiclePrice $vehiclePrice): bool => strcasecmp($vehiclePrice->vehicle_category, trim($vehicleCategory)) === 0);

        if (! $price) {
            return null;
        }

        $baseFare = $price->base_fare !== null ? (float) $price->base_fare : (float) ($price->vehicle_rent ?? 0);
        $totalAmount = $baseFare;
        if ($price->vehicle_rent !== null && $price->per_km_rate !== null && $distanceKm !== null) {
            $totalAmount += $distanceKm * (float) $price->per_km_rate;
        }

        $details = [
            'category' => $category,
            'origin' => $route->origin ?? $origin,
            'destination' => $route->destination ?? $destination,
            'vehicle_category' => $price->vehicle_category,
            'base_fare' => $baseFare,
            'included_km' => $route->km_limit,
            'included_hours' => $route->included_hours === null ? null : (float) $route->included_hours,
            'extra_km_rate' => $price->extra_km_rate === null ? null : (float) $price->extra_km_rate,
            'extra_hour_rate' => $price->extra_hour_rate === null ? null : (float) $price->extra_hour_rate,
            'vehicle_rent' => $price->vehicle_rent === null ? null : (float) $price->vehicle_rent,
            'per_km_rate' => $price->per_km_rate === null ? null : (float) $price->per_km_rate,
            'distance_km' => $distanceKm,
            'gst_applicable' => $price->gst_applicable,
            'toll_type' => $price->toll_type,
            'parking_type' => $price->parking_type,
            'border_tax_type' => $price->border_tax_type,
            'night_charge' => $price->night_charge === null ? null : (float) $price->night_charge,
            'driver_food_type' => $price->driver_food_type,
        ];

        return [
            'route' => $route,
            'price' => $price,
            'total_amount' => round($totalAmount, 2),
            'details' => $details,
        ];
    }

    private function findRoute(string $category, string $origin, string $destination): ?RateRoute
    {
        if (in_array($category, ['round_trip', 'local_8h_80km', 'local_4h_40km'], true)) {
            return RateRoute::query()
                ->where('category', $category)
                ->whereNull('origin')
                ->whereNull('destination')
                ->where('active', true)
                ->with('vehiclePrices')
                ->first();
        }

        $routes = RateRoute::query()
            ->where('category', $category)
            ->where('active', true)
            ->with('vehiclePrices')
            ->get();

        foreach ($routes as $route) {
            if ($this->sameLocation($route->origin, $origin) && $this->sameLocation($route->destination, $destination)) {
                return $route;
            }
        }

        foreach ($routes as $route) {
            if ($route->return_same_rate && $this->sameLocation($route->origin, $destination) && $this->sameLocation($route->destination, $origin)) {
                return $route;
            }
        }

        return null;
    }

    private function sameLocation(?string $first, string $second): bool
    {
        return $first !== null && strcasecmp(trim($first), trim($second)) === 0;
    }
}
