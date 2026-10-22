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
            ->first(fn (RateVehiclePrice $vehiclePrice): bool => $this->matchVehicleCategory($vehiclePrice->vehicle_category, $vehicleCategory));

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
            'extra_km_configured' => $price->extra_km_rate !== null,
            'extra_hour_configured' => $price->extra_hour_rate !== null,
            'vehicle_rent' => $price->vehicle_rent === null ? null : (float) $price->vehicle_rent,
            'per_km_rate' => $price->per_km_rate === null ? null : (float) $price->per_km_rate,
            'distance_km' => $distanceKm,
            'gst_applicable' => (bool) $price->gst_applicable,
            'toll_type' => $price->toll_type,
            'parking_type' => $price->parking_type,
            'border_tax_type' => $price->border_tax_type,
            'night_charge' => $price->night_charge === null ? null : (float) $price->night_charge,
            'driver_food_type' => $price->driver_food_type,
            'is_estimate' => $category === 'round_trip',
            'estimate_notice' => $category === 'round_trip'
                ? 'Estimated base: vehicle rent + per-KM tariff. Toll, parking, border tax, driver food & night charges extra.'
                : null,
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
        if ($first === null) {
            return false;
        }

        $f = strtolower(trim($first));
        $s = strtolower(trim($second));

        if ($f === $s) {
            return true;
        }

        $fAirport = str_contains($f, 'airport');
        $sAirport = str_contains($s, 'airport');

        // Do not confuse Raipur Airport with Raipur city
        if ($fAirport !== $sAirport) {
            return false;
        }

        if ($fAirport && $sAirport) {
            $isRaipurF = str_contains($f, 'raipur') || str_contains($f, 'swami vivekananda') || str_contains($f, 'mana') || str_contains($f, 'rpr');
            $isRaipurS = str_contains($s, 'raipur') || str_contains($s, 'swami vivekananda') || str_contains($s, 'mana') || str_contains($s, 'rpr');

            return $isRaipurF && $isRaipurS;
        }

        // Neither is an airport: check if locations refer to the same recognized hub city
        $knownCities = ['bilaspur', 'raipur', 'raigarh', 'korba', 'ambikapur', 'bhilai', 'sambalpur', 'jharsuguda'];
        foreach ($knownCities as $city) {
            $fHas = str_contains($f, $city);
            $sHas = str_contains($s, $city);
            if ($fHas || $sHas) {
                return $fHas && $sHas;
            }
        }

        return str_contains($s, $f) || str_contains($f, $s);
    }

    public function matchVehicleCategory(string $priceCategory, string $requestedCategory): bool
    {
        $priceCat = trim($priceCategory);
        $reqCat = trim($requestedCategory);

        if (strcasecmp($priceCat, $reqCat) === 0) {
            return true;
        }

        $pLower = strtolower($priceCat);
        $rLower = strtolower($reqCat);

        // SUV / MUV — 6+1 Seating <-> Ertiga <-> MUV
        $isSUVReq = str_contains($rLower, 'ertiga') || str_contains($rLower, 'suv') || str_contains($rLower, 'muv');
        $isSUVPriced = str_contains($pLower, 'ertiga') || str_contains($pLower, 'suv') || str_contains($pLower, 'muv');
        if ($isSUVReq && $isSUVPriced) {
            return true;
        }

        // Traveller <-> Tempo Traveller
        $isTravellerReq = str_contains($rLower, 'traveller') || str_contains($rLower, 'tempo');
        $isTravellerPriced = str_contains($pLower, 'traveller') || str_contains($pLower, 'tempo');
        if ($isTravellerReq && $isTravellerPriced) {
            return true;
        }

        // Hatchback fallback to Sedan if route doesn't have dedicated Hatchback tier
        if (str_contains($rLower, 'hatchback') && (str_contains($pLower, 'hatchback') || str_contains($pLower, 'sedan'))) {
            return true;
        }

        // Urbania fallback to Traveller if Urbania not explicitly listed
        if (str_contains($rLower, 'urbania') && (str_contains($pLower, 'urbania') || str_contains($pLower, 'traveller'))) {
            return true;
        }

        // Bus matching
        if (str_contains($rLower, 'bus') && str_contains($pLower, 'bus')) {
            return true;
        }

        return false;
    }
}
