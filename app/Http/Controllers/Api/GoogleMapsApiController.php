<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoogleMapsRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleMapsApiController extends Controller
{
    public function __construct(
        protected GoogleMapsRoutingService $routingService
    ) {}

    /**
     * Provide safe client-side Maps configuration (client API key, center coords, default zoom).
     * Server API key is never exposed.
     */
    public function config(): JsonResponse
    {
        $clientKey = $this->routingService->getClientApiKey();

        return response()->json([
            'status' => 'success',
            'has_api_key' => ! empty($clientKey),
            'has_google_key' => ! empty($clientKey),
            'client_api_key' => $clientKey ?: '',
            'center' => [
                'lat' => (float) config('vaishnavi.map_center_lat', 22.0797),
                'lng' => (float) config('vaishnavi.map_center_lng', 82.1409),
            ],
            'zoom' => (int) config('vaishnavi.map_default_zoom', 12),
        ]);
    }

    /**
     * Calculate road route & driving distance for one-way or round-trip journeys.
     */
    public function calculateRoute(Request $request): JsonResponse
    {
        $origin = $request->input('origin', $request->input('pickup_location'));
        $destination = $request->input('destination');
        $via = $request->input('via', $request->input('via_location'));
        $isRoundTrip = $request->boolean('is_round_trip')
            || (strcasecmp((string) $request->input('trip_type'), 'Round-Trip') === 0);
        $finalDrop = $request->input('final_drop', $request->input('final_destination'));

        if (empty($origin) || empty($destination)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Both origin (pickup location) and destination are required.',
            ], 422);
        }

        $result = $this->routingService->calculateRoadRoute(
            origin: $origin,
            destination: $destination,
            via: $via,
            isRoundTrip: $isRoundTrip,
            finalDrop: $finalDrop
        );

        if (! ($result['success'] ?? false)) {
            $result['status'] = 'error';

            return response()->json($result, 422);
        }

        $result['status'] = 'success';

        return response()->json($result);
    }

    /**
     * Geocode an address/place name into coordinates.
     */
    public function geocode(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string|max:300',
        ]);

        $result = $this->routingService->geocodeAddress($request->input('address'));

        if (! $result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not find coordinates for the provided location.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'lat' => $result['lat'],
            'lng' => $result['lng'],
            'latitude' => $result['lat'],
            'longitude' => $result['lng'],
            'formatted_address' => $result['formatted_address'] ?? null,
            'place_id' => $result['place_id'] ?? null,
            'data' => $result,
        ]);
    }

    /**
     * Reverse geocode coordinates (lat, lng) into formatted address and place name.
     */
    public function reverseGeocode(Request $request): JsonResponse
    {
        $lat = $request->input('lat', $request->input('latitude'));
        $lng = $request->input('lng', $request->input('longitude'));

        if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Valid latitude and longitude coordinates are required.',
            ], 422);
        }

        $result = $this->routingService->reverseGeocode((float) $lat, (float) $lng);

        return response()->json([
            'status' => 'success',
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'latitude' => (float) $lat,
            'longitude' => (float) $lng,
            'formatted_address' => $result['formatted_address'] ?? "Point ({$lat}, {$lng})",
            'name' => $result['name'] ?? null,
            'place_id' => $result['place_id'] ?? null,
            'data' => $result,
        ]);
    }
}
