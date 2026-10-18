@props([
    'booking' => null,
    'enquiry' => null,
    'editable' => false,
    'title' => 'Calculated Road Route & Driving Distance',
    'mapHeight' => '320px',
    'mapId' => 'routeMapCardCanvas'
])

@php
    $sourceModel = $booking ?? $enquiry;
    $hasRoute = $sourceModel && !empty($sourceModel->route_distance_km);
    $distanceKm = $sourceModel?->route_distance_km;
    $durationText = $sourceModel?->route_duration_text;
    $legs = $sourceModel?->route_legs ?? [];
    $geometry = $sourceModel?->route_geometry;
    $pickup = $sourceModel?->pickup_location;
    $via = $sourceModel?->via_location;
    $destination = $sourceModel?->destination;
    $isRoundTrip = ($sourceModel?->trip_type === 'Round-Trip' || $sourceModel?->rate_category === 'round_trip' || $sourceModel?->booking_type === 'Round-Trip');
    $routedAt = $sourceModel?->routed_at;
@endphp

@once
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endonce

<div class="card route-map-card mb-4" id="routeMapCardWrap" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
    
    <!-- Header -->
    <div style="padding: 0.9rem 1.25rem; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 18px;">🛣️</span>
            <div>
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: #0F172A;">
                    {{ $title }}
                </h4>
                <div style="font-size: 11px; color: #64748B;">
                    Actual highway road path • {{ $isRoundTrip ? 'Round-Trip (Outbound + Return)' : 'One-Way Journey' }}
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            @if($distanceKm)
                <div style="display: flex; align-items: center; gap: 6px; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 4px 10px; border-radius: 6px;">
                    <span style="font-size: 11px; font-weight: 700; color: #1E40AF; text-transform: uppercase;">Road Distance:</span>
                    <strong style="color: #1D4ED8; font-size: 14px;">{{ number_format($distanceKm, 1) }} KM</strong>
                </div>
            @endif

            @if($durationText)
                <div style="display: flex; align-items: center; gap: 4px; background: #F1F5F9; border: 1px solid #E2E8F0; padding: 4px 10px; border-radius: 6px; font-size: 12px; color: #334155;">
                    <span>⏱️</span>
                    <strong>{{ $durationText }}</strong>
                </div>
            @endif

            @if($booking && $editable)
                <form action="{{ route('admin.bookings.recalculate-route', $booking->id) }}" method="POST" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline" style="padding: 4px 10px; font-size: 11px; border-color: #93C5FD; color: #2563EB;">
                        🔄 Recalculate Road Route
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Map Canvas -->
    <div style="position: relative; width: 100%; height: {{ $mapHeight }}; background: #E2E8F0;">
        <div id="{{ $mapId }}" style="width: 100%; height: 100%;"></div>

        <!-- Route Status Floating Pill -->
        <div id="{{ $mapId }}_status" style="position: absolute; bottom: 10px; left: 10px; background: rgba(15, 23, 42, 0.85); color: #fff; padding: 4px 10px; border-radius: 6px; font-size: 11px; z-index: 500; display: flex; align-items: center; gap: 6px;">
            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10B981; display: inline-block;"></span>
            <span>Road Route Active</span>
            @if($routedAt)
                <span style="color: #94A3B8; font-size: 10px;">• Calculated {{ \Carbon\Carbon::parse($routedAt)->diffForHumans() }}</span>
            @endif
        </div>
    </div>

    <!-- Route Leg Breakdown -->
    <div style="padding: 0.85rem 1.25rem; background: #FAFAFA; border-top: 1px solid #E2E8F0;">
        <div class="grid grid-3 gap-2" style="font-size: 12px;">
            <!-- Pickup Point -->
            <div style="background: #fff; padding: 8px 10px; border-radius: 6px; border: 1px solid #E2E8F0;">
                <div style="font-size: 10.5px; font-weight: 700; color: #16A34A; text-transform: uppercase; margin-bottom: 2px;">
                    🟢 Pickup Origin
                </div>
                <div style="font-weight: 700; color: #0F172A;" id="{{ $mapId }}_display_pickup">{{ $pickup ?: 'Pickup not set' }}</div>
                @if($sourceModel?->pickup_latitude && $sourceModel?->pickup_longitude)
                    <div style="font-size: 10.5px; color: #64748B; font-family: monospace;">{{ round($sourceModel->pickup_latitude, 4) }}, {{ round($sourceModel->pickup_longitude, 4) }}</div>
                @endif
            </div>

            <!-- Via Point (if any) -->
            <div style="background: #fff; padding: 8px 10px; border-radius: 6px; border: 1px solid #E2E8F0;">
                <div style="font-size: 10.5px; font-weight: 700; color: #D97706; text-transform: uppercase; margin-bottom: 2px;">
                    🟡 Via Intermediate Hub
                </div>
                <div style="font-weight: 700; color: #0F172A;" id="{{ $mapId }}_display_via">{{ $via ?: 'Direct Highway (No Halt)' }}</div>
                @if($sourceModel?->via_latitude && $sourceModel?->via_longitude)
                    <div style="font-size: 10.5px; color: #64748B; font-family: monospace;">{{ round($sourceModel->via_latitude, 4) }}, {{ round($sourceModel->via_longitude, 4) }}</div>
                @endif
            </div>

            <!-- Drop Destination -->
            <div style="background: #fff; padding: 8px 10px; border-radius: 6px; border: 1px solid #E2E8F0;">
                <div style="font-size: 10.5px; font-weight: 700; color: #DC2626; text-transform: uppercase; margin-bottom: 2px;">
                    🔴 Final Destination
                </div>
                <div style="font-weight: 700; color: #0F172A;" id="{{ $mapId }}_display_dest">{{ $destination ?: 'Destination not set' }}</div>
                @if($sourceModel?->destination_latitude && $sourceModel?->destination_longitude)
                    <div style="font-size: 10.5px; color: #64748B; font-family: monospace;">{{ round($sourceModel->destination_latitude, 4) }}, {{ round($sourceModel->destination_longitude, 4) }}</div>
                @endif
            </div>
        </div>

        @if(!empty($legs) && count($legs) > 0)
            <div style="margin-top: 0.65rem; padding-top: 0.65rem; border-top: 1px dashed #E2E8F0;">
                <div style="font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; margin-bottom: 4px;">
                    Route Legs Breakdown:
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    @foreach($legs as $leg)
                        <div style="background: #fff; border: 1px solid #CBD5E1; padding: 4px 8px; border-radius: 4px; font-size: 11.5px; display: flex; align-items: center; gap: 6px;">
                            <strong style="color: #2563EB;">Leg {{ $leg['leg_index'] ?? ($loop->iteration) }}:</strong>
                            <span>{{ $leg['from_name'] ?? 'Start' }} &rarr; {{ $leg['to_name'] ?? 'End' }}</span>
                            <span style="background: #F1F5F9; padding: 1px 5px; border-radius: 3px; font-weight: 700; color: #0F172A;">{{ $leg['distance_km'] ?? 0 }} KM</span>
                            @if(!empty($leg['duration_text']))
                                <span style="color: #64748B;">({{ $leg['duration_text'] }})</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapId = '{{ $mapId }}';
    const canvas = document.getElementById(mapId);
    if (!canvas) return;

    const pickupLat = {{ $sourceModel?->pickup_latitude ?? 22.0797 }};
    const pickupLng = {{ $sourceModel?->pickup_longitude ?? 82.1409 }};
    const destLat = {{ $sourceModel?->destination_latitude ?? 21.2514 }};
    const destLng = {{ $sourceModel?->destination_longitude ?? 81.6296 }};
    const viaLat = {{ $sourceModel?->via_latitude ? (float) $sourceModel->via_latitude : 'null' }};
    const viaLng = {{ $sourceModel?->via_longitude ? (float) $sourceModel->via_longitude : 'null' }};

    if (typeof L !== 'undefined') {
        const routeMap = L.map(canvas).setView([pickupLat, pickupLng], 8);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap | Vaishnavi Tours'
        }).addTo(routeMap);

        const points = [];
        // Pickup marker
        L.marker([pickupLat, pickupLng], {
            title: 'Pickup: {{ addslashes($pickup ?? "Pickup") }}'
        }).addTo(routeMap).bindPopup('<strong>Pickup:</strong> {{ addslashes($pickup ?? "Pickup") }}');
        points.push([pickupLat, pickupLng]);

        // Via marker (if any)
        if (viaLat && viaLng) {
            L.marker([viaLat, viaLng], {
                title: 'Via: {{ addslashes($via ?? "Via") }}'
            }).addTo(routeMap).bindPopup('<strong>Via:</strong> {{ addslashes($via ?? "Via") }}');
            points.push([viaLat, viaLng]);
        }

        // Destination marker
        L.marker([destLat, destLng], {
            title: 'Drop: {{ addslashes($destination ?? "Destination") }}'
        }).addTo(routeMap).bindPopup('<strong>Destination:</strong> {{ addslashes($destination ?? "Destination") }}');
        points.push([destLat, destLng]);

        @if($isRoundTrip)
            // Return back to pickup
            points.push([pickupLat, pickupLng]);
        @endif

        // Draw polyline
        const routeLine = L.polyline(points, {
            color: '#2563EB',
            weight: 5,
            opacity: 0.85,
            dashArray: '{{ $isRoundTrip ? "8, 6" : "0" }}'
        }).addTo(routeMap);

        routeMap.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

        // Ensure proper render on tab/modal display
        setTimeout(() => routeMap.invalidateSize(), 300);
    }
});
</script>
