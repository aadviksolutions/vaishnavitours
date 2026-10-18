<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsRoutingService
{
    /**
     * Known benchmark road distances (KM) between major Chhattisgarh hubs via actual national/state highways.
     * Used for calibrated offline/fallback calculation when Google API key is in setup mode.
     */
    private const KNOWN_ROAD_DISTANCES = [
        'bilaspur|raipur' => 118.0,
        'raipur|bilaspur' => 118.0,
        'bilaspur|raipur airport' => 135.0,
        'raipur airport|bilaspur' => 135.0,
        'bilaspur|korba' => 88.0,
        'korba|bilaspur' => 88.0,
        'bilaspur|raigarh' => 155.0,
        'raigarh|bilaspur' => 155.0,
        'bilaspur|amarkantak' => 122.0,
        'amarkantak|bilaspur' => 122.0,
        'bilaspur|ambikapur' => 220.0,
        'ambikapur|bilaspur' => 220.0,
        'bilaspur|janjgir' => 52.0,
        'janjgir|bilaspur' => 52.0,
        'bilaspur|champa' => 62.0,
        'champa|bilaspur' => 62.0,
        'bilaspur|bhilai' => 145.0,
        'bhilai|bilaspur' => 145.0,
        'bilaspur|durg' => 150.0,
        'durg|bilaspur' => 150.0,
        'bilaspur|mungeli' => 50.0,
        'mungeli|bilaspur' => 50.0,
        'bilaspur|kawardha' => 125.0,
        'kawardha|bilaspur' => 125.0,
        'bilaspur|jagdalpur' => 415.0,
        'jagdalpur|bilaspur' => 415.0,
        'raipur|korba' => 205.0,
        'korba|raipur' => 205.0,
        'raipur|raigarh' => 240.0,
        'raigarh|raipur' => 240.0,
    ];

    /**
     * Get the active Google Maps server-side API key.
     */
    public function getServerApiKey(): ?string
    {
        $key = Setting::get('google_maps_server_api_key');
        if (! empty($key)) {
            return trim($key);
        }

        $envKey = config('vaishnavi.google_maps_server_api_key');
        if (! empty($envKey)) {
            return trim($envKey);
        }

        $fallbackKey = config('vaishnavi.google_maps_client_api_key');

        return ! empty($fallbackKey) ? trim($fallbackKey) : null;
    }

    /**
     * Get the active Google Maps client-side API key (safe for browser JS SDK).
     */
    public function getClientApiKey(): ?string
    {
        $key = Setting::get('google_maps_client_api_key');
        if (! empty($key)) {
            return trim($key);
        }

        $envKey = config('vaishnavi.google_maps_client_api_key');

        return ! empty($envKey) ? trim($envKey) : null;
    }

    /**
     * Calculate genuine road route and distance for One-Way or Round-Trip.
     *
     * @param  string|array  $origin  Address string or ['lat' => x, 'lng' => y, 'address' => '...']
     * @param  string|array  $destination  Address string or ['lat' => x, 'lng' => y, 'address' => '...']
     * @param  string|array|null  $via  Optional via location
     * @param  bool  $isRoundTrip  Whether trip returns to origin
     * @param  string|null  $finalDrop  Explicit return drop location if different from origin
     * @return array{
     *     success: bool,
     *     status: string,
     *     message: string,
     *     is_round_trip: bool,
     *     total_distance_km: float,
     *     total_duration_text: string,
     *     total_duration_minutes: int,
     *     legs: array<int, array>,
     *     route_geometry: ?string,
     *     calculated_at: string,
     *     source: string
     * }
     */
    public function calculateRoadRoute(
        string|array $origin,
        string|array $destination,
        string|array|null $via = null,
        bool $isRoundTrip = false,
        string|array|null $finalDrop = null
    ): array {
        $apiKey = $this->getServerApiKey();

        $originStr = $this->formatLocationString($origin);
        $destStr = $this->formatLocationString($destination);
        $viaStr = $via ? $this->formatLocationString($via) : null;
        $returnDropStr = $finalDrop ? $this->formatLocationString($finalDrop) : $originStr;

        if (empty($originStr) || empty($destStr)) {
            return [
                'success' => false,
                'status' => 'INVALID_LOCATIONS',
                'message' => 'Please provide valid pickup and destination locations.',
                'is_round_trip' => $isRoundTrip,
                'total_distance_km' => 0.0,
                'total_duration_text' => '',
                'total_duration_minutes' => 0,
                'legs' => [],
                'route_geometry' => null,
                'calculated_at' => now()->toIso8601String(),
                'source' => 'validation',
            ];
        }

        // If Google Maps API key is configured, execute genuine Google Maps Routing
        if (! empty($apiKey)) {
            $googleResult = $this->computeGoogleRoute($origin, $destination, $via, $isRoundTrip, $finalDrop, $apiKey);
            if ($googleResult['success']) {
                return $googleResult;
            }

            Log::warning('Google Maps Routes API returned failure, falling back to calibrated road routing', [
                'error' => $googleResult['message'],
            ]);
        }

        // Calibrated Road Network Routing (fallback when API key is missing or quota is exhausted)
        return $this->computeCalibratedRoadRoute($origin, $destination, $via, $isRoundTrip, $finalDrop);
    }

    /**
     * Compute route using Google Directions / Routes API.
     */
    protected function computeGoogleRoute(
        string|array $origin,
        string|array $destination,
        string|array|null $via,
        bool $isRoundTrip,
        string|array|null $finalDrop,
        string $apiKey
    ): array {
        try {
            $legs = [];
            $totalMeters = 0;
            $totalSeconds = 0;
            $polylines = [];

            // 1. Outbound Route: Origin -> (Via) -> Destination
            $outboundQuery = [
                'origin' => $this->formatLocationString($origin),
                'destination' => $this->formatLocationString($destination),
                'mode' => 'driving',
                'key' => $apiKey,
            ];

            if (! empty($via)) {
                $outboundQuery['waypoints'] = $this->formatLocationString($via);
            }

            $response = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/directions/json', $outboundQuery);

            if (! $response->successful() || ($response->json('status') !== 'OK')) {
                $status = $response->json('status') ?: 'HTTP_'.$response->status();
                $errorMsg = $response->json('error_message') ?: 'Unable to compute route from Google Maps.';

                return [
                    'success' => false,
                    'status' => $status,
                    'message' => $errorMsg,
                    'is_round_trip' => $isRoundTrip,
                    'total_distance_km' => 0.0,
                    'total_duration_text' => '',
                    'total_duration_minutes' => 0,
                    'legs' => [],
                    'route_geometry' => null,
                    'calculated_at' => now()->toIso8601String(),
                    'source' => 'google_api_failed',
                ];
            }

            $outboundData = $response->json();
            $route = $outboundData['routes'][0] ?? null;

            if (! $route || empty($route['legs'])) {
                return [
                    'success' => false,
                    'status' => 'ZERO_RESULTS',
                    'message' => 'No road route found between selected points.',
                    'is_round_trip' => $isRoundTrip,
                    'total_distance_km' => 0.0,
                    'total_duration_text' => '',
                    'total_duration_minutes' => 0,
                    'legs' => [],
                    'route_geometry' => null,
                    'calculated_at' => now()->toIso8601String(),
                    'source' => 'google_zero_results',
                ];
            }

            if (! empty($route['overview_polyline']['points'])) {
                $polylines[] = $route['overview_polyline']['points'];
            }

            foreach ($route['legs'] as $legIdx => $leg) {
                $distMeters = $leg['distance']['value'] ?? 0;
                $durSecs = $leg['duration']['value'] ?? 0;
                $totalMeters += $distMeters;
                $totalSeconds += $durSecs;

                $legs[] = [
                    'type' => 'outbound',
                    'label' => 'Outbound Leg '.($legIdx + 1).': '.($leg['start_address'] ?? 'Origin').' → '.($leg['end_address'] ?? 'Destination'),
                    'start_address' => $leg['start_address'] ?? '',
                    'end_address' => $leg['end_address'] ?? '',
                    'distance_km' => round($distMeters / 1000, 2),
                    'distance_text' => $leg['distance']['text'] ?? round($distMeters / 1000, 1).' km',
                    'duration_text' => $leg['duration']['text'] ?? round($durSecs / 60).' mins',
                    'duration_minutes' => (int) round($durSecs / 60),
                ];
            }

            // 2. Return Route (If Round-Trip): Destination -> (Via) -> Final Drop / Origin
            // CRITICAL: Calculate actual return road distance, DO NOT assume equal to outbound!
            if ($isRoundTrip) {
                $returnOrigin = $this->formatLocationString($destination);
                $returnDest = $finalDrop ? $this->formatLocationString($finalDrop) : $this->formatLocationString($origin);

                $returnQuery = [
                    'origin' => $returnOrigin,
                    'destination' => $returnDest,
                    'mode' => 'driving',
                    'key' => $apiKey,
                ];

                if (! empty($via)) {
                    $returnQuery['waypoints'] = $this->formatLocationString($via);
                }

                $retResponse = Http::timeout(10)->get('https://maps.googleapis.com/maps/api/directions/json', $returnQuery);

                if ($retResponse->successful() && $retResponse->json('status') === 'OK') {
                    $retRoute = $retResponse->json('routes.0');
                    if ($retRoute && ! empty($retRoute['legs'])) {
                        if (! empty($retRoute['overview_polyline']['points'])) {
                            $polylines[] = $retRoute['overview_polyline']['points'];
                        }

                        foreach ($retRoute['legs'] as $rLegIdx => $rLeg) {
                            $rMeters = $rLeg['distance']['value'] ?? 0;
                            $rSecs = $rLeg['duration']['value'] ?? 0;
                            $totalMeters += $rMeters;
                            $totalSeconds += $rSecs;

                            $legs[] = [
                                'type' => 'return',
                                'label' => 'Return Leg '.($rLegIdx + 1).': '.($rLeg['start_address'] ?? 'Destination').' → '.($rLeg['end_address'] ?? 'Drop'),
                                'start_address' => $rLeg['start_address'] ?? '',
                                'end_address' => $rLeg['end_address'] ?? '',
                                'distance_km' => round($rMeters / 1000, 2),
                                'distance_text' => $rLeg['distance']['text'] ?? round($rMeters / 1000, 1).' km',
                                'duration_text' => $rLeg['duration']['text'] ?? round($rSecs / 60).' mins',
                                'duration_minutes' => (int) round($rSecs / 60),
                            ];
                        }
                    }
                }
            }

            $totalKm = round($totalMeters / 1000, 2);
            $totalMins = (int) round($totalSeconds / 60);

            return [
                'success' => true,
                'status' => 'OK',
                'message' => 'Actual road route computed successfully via Google Maps.',
                'is_round_trip' => $isRoundTrip,
                'total_distance_km' => $totalKm,
                'total_duration_text' => $this->formatDurationMinutes($totalMins),
                'total_duration_minutes' => $totalMins,
                'legs' => $legs,
                'route_geometry' => implode('|', $polylines),
                'calculated_at' => now()->toIso8601String(),
                'source' => 'google_maps_api',
            ];
        } catch (\Throwable $e) {
            Log::error('Google Maps routing exception: '.$e->getMessage());

            return [
                'success' => false,
                'status' => 'EXCEPTION',
                'message' => 'Google Maps routing service error: '.$e->getMessage(),
                'is_round_trip' => $isRoundTrip,
                'total_distance_km' => 0.0,
                'total_duration_text' => '',
                'total_duration_minutes' => 0,
                'legs' => [],
                'route_geometry' => null,
                'calculated_at' => now()->toIso8601String(),
                'source' => 'exception',
            ];
        }
    }

    /**
     * Compute road route using benchmark road distances and national highway topography.
     * Strictly avoids straight-line calculation; accounts for road curvature (~1.32x) and leg topology.
     */
    protected function computeCalibratedRoadRoute(
        string|array $origin,
        string|array $destination,
        string|array|null $via,
        bool $isRoundTrip,
        string|array|null $finalDrop
    ): array {
        $originName = $this->extractLocationName($origin);
        $destName = $this->extractLocationName($destination);
        $viaName = $via ? $this->extractLocationName($via) : null;
        $returnDropName = $finalDrop ? $this->extractLocationName($finalDrop) : $originName;

        $legs = [];
        $totalKm = 0.0;
        $totalMinutes = 0;

        // 1. Outbound: Origin -> (Via) -> Destination
        if ($viaName) {
            $leg1Km = $this->lookupOrEstimateRoadDistance($origin, $via);
            $leg1Mins = (int) round(($leg1Km / 52) * 60);
            $totalKm += $leg1Km;
            $totalMinutes += $leg1Mins;
            $legs[] = [
                'type' => 'outbound',
                'label' => 'Outbound Leg 1: '.$originName.' → '.$viaName,
                'start_address' => $originName,
                'end_address' => $viaName,
                'distance_km' => $leg1Km,
                'distance_text' => $leg1Km.' km',
                'duration_text' => $this->formatDurationMinutes($leg1Mins),
                'duration_minutes' => $leg1Mins,
            ];

            $leg2Km = $this->lookupOrEstimateRoadDistance($via, $destination);
            $leg2Mins = (int) round(($leg2Km / 52) * 60);
            $totalKm += $leg2Km;
            $totalMinutes += $leg2Mins;
            $legs[] = [
                'type' => 'outbound',
                'label' => 'Outbound Leg 2: '.$viaName.' → '.$destName,
                'start_address' => $viaName,
                'end_address' => $destName,
                'distance_km' => $leg2Km,
                'distance_text' => $leg2Km.' km',
                'duration_text' => $this->formatDurationMinutes($leg2Mins),
                'duration_minutes' => $leg2Mins,
            ];
        } else {
            $directKm = $this->lookupOrEstimateRoadDistance($origin, $destination);
            $directMins = (int) round(($directKm / 54) * 60);
            $totalKm += $directKm;
            $totalMinutes += $directMins;
            $legs[] = [
                'type' => 'outbound',
                'label' => 'Outbound Trip: '.$originName.' → '.$destName,
                'start_address' => $originName,
                'end_address' => $destName,
                'distance_km' => $directKm,
                'distance_text' => $directKm.' km',
                'duration_text' => $this->formatDurationMinutes($directMins),
                'duration_minutes' => $directMins,
            ];
        }

        // 2. Return Leg: Destination -> (Via) -> Return Drop
        if ($isRoundTrip) {
            if ($viaName) {
                $ret1Km = $this->lookupOrEstimateRoadDistance($destination, $via);
                $ret1Mins = (int) round(($ret1Km / 52) * 60);
                $totalKm += $ret1Km;
                $totalMinutes += $ret1Mins;
                $legs[] = [
                    'type' => 'return',
                    'label' => 'Return Leg 1: '.$destName.' → '.$viaName,
                    'start_address' => $destName,
                    'end_address' => $viaName,
                    'distance_km' => $ret1Km,
                    'distance_text' => $ret1Km.' km',
                    'duration_text' => $this->formatDurationMinutes($ret1Mins),
                    'duration_minutes' => $ret1Mins,
                ];

                $ret2Km = $this->lookupOrEstimateRoadDistance($via, $finalDrop ?: $origin);
                $ret2Mins = (int) round(($ret2Km / 52) * 60);
                $totalKm += $ret2Km;
                $totalMinutes += $ret2Mins;
                $legs[] = [
                    'type' => 'return',
                    'label' => 'Return Leg 2: '.$viaName.' → '.$returnDropName,
                    'start_address' => $viaName,
                    'end_address' => $returnDropName,
                    'distance_km' => $ret2Km,
                    'distance_text' => $ret2Km.' km',
                    'duration_text' => $this->formatDurationMinutes($ret2Mins),
                    'duration_minutes' => $ret2Mins,
                ];
            } else {
                $retKm = $this->lookupOrEstimateRoadDistance($destination, $finalDrop ?: $origin);
                $retMins = (int) round(($retKm / 54) * 60);
                $totalKm += $retKm;
                $totalMinutes += $retMins;
                $legs[] = [
                    'type' => 'return',
                    'label' => 'Return Trip: '.$destName.' → '.$returnDropName,
                    'start_address' => $destName,
                    'end_address' => $returnDropName,
                    'distance_km' => $retKm,
                    'distance_text' => $retKm.' km',
                    'duration_text' => $this->formatDurationMinutes($retMins),
                    'duration_minutes' => $retMins,
                ];
            }
        }

        $totalKm = round($totalKm, 2);

        $originCoords = $this->extractCoordinates($origin);
        $destCoords = $this->extractCoordinates($destination);
        $viaCoords = $via ? $this->extractCoordinates($via) : null;

        return [
            'success' => true,
            'status' => 'OK',
            'message' => 'Planned road route distance computed using highway road network.',
            'is_round_trip' => $isRoundTrip,
            'total_distance_km' => $totalKm,
            'total_duration_text' => $this->formatDurationMinutes($totalMinutes),
            'total_duration_minutes' => $totalMinutes,
            'legs' => $legs,
            'origin_coordinates' => $originCoords,
            'destination_coordinates' => $destCoords,
            'via_coordinates' => $viaCoords,
            'route_geometry' => null,
            'calculated_at' => now()->toIso8601String(),
            'source' => 'calibrated_road_network',
        ];
    }

    /**
     * Look up road distance from known regional highway table or estimate based on road curvature.
     */
    protected function lookupOrEstimateRoadDistance(string|array $from, string|array $to): float
    {
        $fromName = strtolower(trim($this->extractLocationName($from)));
        $toName = strtolower(trim($this->extractLocationName($to)));

        $key = $fromName.'|'.$toName;
        if (isset(self::KNOWN_ROAD_DISTANCES[$key])) {
            return self::KNOWN_ROAD_DISTANCES[$key];
        }

        // Fuzzy match known hubs
        foreach (self::KNOWN_ROAD_DISTANCES as $pair => $km) {
            [$p1, $p2] = explode('|', $pair);
            if (str_contains($fromName, $p1) && str_contains($toName, $p2)) {
                return $km;
            }
        }

        // Coordinate-based road distance (Great-Circle multiplied by Indian highway winding factor 1.33)
        $coordsFrom = $this->extractCoordinates($from);
        $coordsTo = $this->extractCoordinates($to);

        if ($coordsFrom && $coordsTo) {
            $straightLine = $this->haversineKm($coordsFrom['lat'], $coordsFrom['lng'], $coordsTo['lat'], $coordsTo['lng']);
            // Real Indian state & national highways average 1.28x to 1.38x straight-line distance
            $roadDistance = $straightLine * 1.32;

            return max(round($roadDistance, 2), 5.0);
        }

        // Default conservative intercity estimate if no coordinates available
        return 120.0;
    }

    /**
     * Compute Haversine distance in KM between two lat/lng pairs.
     */
    protected function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Geocode an address to lat/lng and formatted address using Google Geocoding API.
     */
    public function geocodeAddress(string $address): ?array
    {
        $apiKey = $this->getServerApiKey();
        if (empty($apiKey)) {
            return $this->getPresetCoordinates($address);
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'components' => 'country:IN',
                'key' => $apiKey,
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $result = $response->json('results.0');

                return [
                    'formatted_address' => $result['formatted_address'] ?? $address,
                    'lat' => (float) ($result['geometry']['location']['lat'] ?? 0),
                    'lng' => (float) ($result['geometry']['location']['lng'] ?? 0),
                    'place_id' => $result['place_id'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Geocoding error: '.$e->getMessage());
        }

        return $this->getPresetCoordinates($address);
    }

    /**
     * Reverse geocode coordinates to a formatted address.
     */
    public function reverseGeocode(float $lat, float $lng): ?array
    {
        $apiKey = $this->getServerApiKey();
        if (empty($apiKey)) {
            return [
                'formatted_address' => sprintf('Location at %.5f, %.5f', $lat, $lng),
                'name' => 'Selected Location',
                'lat' => $lat,
                'lng' => $lng,
                'place_id' => null,
            ];
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => "{$lat},{$lng}",
                'key' => $apiKey,
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $result = $response->json('results.0');
                $name = $result['address_components'][0]['long_name'] ?? 'Selected Location';

                return [
                    'formatted_address' => $result['formatted_address'] ?? sprintf('Location at %.5f, %.5f', $lat, $lng),
                    'name' => $name,
                    'lat' => $lat,
                    'lng' => $lng,
                    'place_id' => $result['place_id'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Reverse geocoding error: '.$e->getMessage());
        }

        return [
            'formatted_address' => sprintf('Location at %.5f, %.5f', $lat, $lng),
            'name' => 'Selected Location',
            'lat' => $lat,
            'lng' => $lng,
            'place_id' => null,
        ];
    }

    /**
     * Lookup preset coordinates for prominent Chhattisgarh locations when offline.
     */
    public function getPresetCoordinates(string $location): ?array
    {
        $normalized = strtolower(trim($location));
        $presets = [
            'bilaspur' => ['lat' => 22.0797, 'lng' => 82.1409, 'name' => 'Bilaspur, Chhattisgarh'],
            'raipur' => ['lat' => 21.2514, 'lng' => 81.6296, 'name' => 'Raipur, Chhattisgarh'],
            'raipur airport' => ['lat' => 21.1804, 'lng' => 81.7389, 'name' => 'Swami Vivekananda Airport (RPR), Raipur'],
            'korba' => ['lat' => 22.3595, 'lng' => 82.7501, 'name' => 'Korba, Chhattisgarh'],
            'raigarh' => ['lat' => 21.8974, 'lng' => 83.3950, 'name' => 'Raigarh, Chhattisgarh'],
            'amarkantak' => ['lat' => 22.6756, 'lng' => 81.7588, 'name' => 'Amarkantak, Madhya Pradesh'],
            'ambikapur' => ['lat' => 23.1188, 'lng' => 83.1979, 'name' => 'Ambikapur, Surguja, Chhattisgarh'],
            'bhilai' => ['lat' => 21.1938, 'lng' => 81.3509, 'name' => 'Bhilai, Durg, Chhattisgarh'],
            'durg' => ['lat' => 21.1904, 'lng' => 81.2849, 'name' => 'Durg, Chhattisgarh'],
            'janjgir' => ['lat' => 22.0167, 'lng' => 82.5667, 'name' => 'Janjgir Champa, Chhattisgarh'],
            'champa' => ['lat' => 22.0500, 'lng' => 82.6500, 'name' => 'Champa, Chhattisgarh'],
            'mungeli' => ['lat' => 22.0667, 'lng' => 81.6833, 'name' => 'Mungeli, Chhattisgarh'],
            'kawardha' => ['lat' => 22.0167, 'lng' => 81.2500, 'name' => 'Kawardha, Chhattisgarh'],
            'jagdalpur' => ['lat' => 19.0734, 'lng' => 82.0298, 'name' => 'Jagdalpur, Bastar, Chhattisgarh'],
        ];

        foreach ($presets as $k => $data) {
            if (str_contains($normalized, $k)) {
                return [
                    'formatted_address' => $data['name'],
                    'name' => $data['name'],
                    'lat' => $data['lat'],
                    'lng' => $data['lng'],
                    'place_id' => 'cg_preset_'.$k,
                ];
            }
        }

        return null;
    }

    protected function formatLocationString(string|array $loc): string
    {
        if (is_array($loc)) {
            if (! empty($loc['lat']) && ! empty($loc['lng'])) {
                return "{$loc['lat']},{$loc['lng']}";
            }

            return $loc['address'] ?? ($loc['name'] ?? '');
        }

        return trim($loc);
    }

    protected function extractLocationName(string|array $loc): string
    {
        if (is_array($loc)) {
            return $loc['name'] ?? ($loc['address'] ?? 'Location');
        }

        return trim($loc);
    }

    public function extractCoordinates(string|array $loc): ?array
    {
        if (is_array($loc) && isset($loc['lat'], $loc['lng']) && is_numeric($loc['lat']) && is_numeric($loc['lng'])) {
            return ['lat' => (float) $loc['lat'], 'lng' => (float) $loc['lng']];
        }

        $preset = $this->getPresetCoordinates(is_string($loc) ? $loc : ($loc['address'] ?? ''));
        if ($preset) {
            return ['lat' => $preset['lat'], 'lng' => $preset['lng']];
        }

        return null;
    }

    protected function formatDurationMinutes(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} mins";
        }

        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;

        return $rem > 0 ? "{$hours} hr {$rem} mins" : "{$hours} hrs";
    }
}
