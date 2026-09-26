@extends('layouts.app')

@section('title', 'Taxi Rates & Route Fares | Vaishnavi Tours')
@section('meta_description', 'Browse configured airport, outstation, round-trip, and local taxi rates across Chhattisgarh and nearby cities.')
@section('canonical', route('rates'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Rates & Tariffs', 'item' => route('rates')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3rem 0;">
    <div class="container">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Official Fare Schedule</span>
        <h1 style="color: #fff; font-size: 2.25rem; margin: 0.35rem 0;">Taxi Rates</h1>
        <p style="color: var(--slate-300); max-width: 700px; margin: 0;">Airport, outstation, round-trip, and local packages from Bilaspur, Raipur, Raigarh, Korba, and configured locations.</p>
    </div>
</section>

<section style="padding: 2.5rem 0 4rem; background: var(--slate-50);">
    <div class="container">
        <form method="GET" action="{{ route('rates') }}" class="card" style="padding: 1.25rem; margin-bottom: 2rem;">
            <div class="grid grid-4 gap-3">
                <div class="form-group">
                    <label for="rateFrom" class="form-label">From</label>
                    <select id="rateFrom" name="from" class="form-select">
                        <option value="">Select city</option>
                        @foreach($locations as $location)<option value="{{ $location }}" {{ request('from') === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="rateTo" class="form-label">To</label>
                    <select id="rateTo" name="to" class="form-select">
                        <option value="">Select destination</option>
                        @foreach($locations as $location)<option value="{{ $location }}" {{ request('to') === $location ? 'selected' : '' }}>{{ $location }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="rateTripType" class="form-label">Trip type</label>
                    <select id="rateTripType" name="rate_category" class="form-select">
                        @foreach(['airport' => 'Quick Airport Booking', 'outstation' => 'Outstation Route', 'round_trip' => 'Round Trip', 'local_8h_80km' => 'Local 8 Hours / 80 KM', 'local_4h_40km' => 'Local 4 Hours / 40 KM'] as $key => $label)
                            <option value="{{ $key }}" {{ request('rate_category', 'outstation') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="rateVehicle" class="form-label">Vehicle</label>
                    <select id="rateVehicle" name="vehicle_category" class="form-select">
                        <option value="">All vehicles</option>
                        @foreach($vehicleCategories as $category)<option value="{{ $category }}" {{ request('vehicle_category') === $category ? 'selected' : '' }}>{{ $category }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="d-flex align-center gap-3" style="flex-wrap: wrap; margin-top: 0.75rem;">
                <label for="rateEstimatedKm" id="rateEstimatedKmLabel" class="form-label" style="margin: 0; display: none;">Round-trip distance (KM)</label>
                <input id="rateEstimatedKm" name="estimated_km" type="number" min="0.01" step="0.1" class="form-control" style="max-width: 180px; display: none;" value="{{ request('estimated_km') }}">
                <button type="submit" class="btn btn-primary">Show rate</button>
            </div>
        </form>

        @push('scripts')
        <script>
            const rateTripType = document.querySelector('#rateTripType');
            const estimatedKmLabel = document.querySelector('#rateEstimatedKmLabel');
            const estimatedKm = document.querySelector('#rateEstimatedKm');
            function toggleEstimatedKm() {
                const isRoundTrip = rateTripType.value === 'round_trip';
                estimatedKmLabel.style.display = isRoundTrip ? 'block' : 'none';
                estimatedKm.style.display = isRoundTrip ? 'block' : 'none';
                estimatedKm.required = isRoundTrip;
            }
            rateTripType.addEventListener('change', toggleEstimatedKm);
            toggleEstimatedKm();
        </script>
        @endpush

        @if(request()->filled('from') && request()->filled('to') && request()->filled('rate_category'))
            <section class="card" style="padding: 1.25rem; margin-bottom: 2rem;">
                <h2 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 1rem;">{{ request('from') }} → {{ request('to') }}</h2>
                @forelse($selectedQuotes as $quote)
                    <div style="border-top: 1px solid var(--slate-200); padding: 1rem 0; display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                        <div>
                            <strong>{{ $quote['price']->vehicle_category }}</strong>
                            <div style="color: var(--slate-600); font-size: 0.9rem; margin-top: 0.3rem;">
                                @if($quote['details']['included_km'] !== null){{ $quote['details']['included_km'] }} KM included · @endif
                                @if($quote['details']['included_hours'] !== null){{ rtrim(rtrim(number_format($quote['details']['included_hours'], 2), '0'), '.') }} hours included · @endif
                                @if($quote['details']['extra_km_rate'] !== null)Extra KM ₹{{ number_format($quote['details']['extra_km_rate'], 2) }} · @endif
                                @if($quote['details']['extra_hour_rate'] !== null)Extra hour ₹{{ number_format($quote['details']['extra_hour_rate'], 2) }} · @endif
                                @if($quote['details']['toll_type'])Toll {{ $quote['details']['toll_type'] }} · @endif
                                @if($quote['details']['parking_type'])Parking {{ $quote['details']['parking_type'] }} · @endif
                                @if($quote['details']['border_tax_type'])Border tax {{ $quote['details']['border_tax_type'] }} · @endif
                                @if($quote['details']['night_charge'] !== null)Night charge ₹{{ number_format($quote['details']['night_charge'], 2) }} · @endif
                                @if($quote['details']['driver_food_type'])Driver food {{ $quote['details']['driver_food_type'] }} · @endif
                                @if($quote['details']['gst_applicable'])GST applicable @endif
                            </div>
                        </div>
                        <div style="text-align: right;">
                            @if($quote['details']['vehicle_rent'] !== null)
                                <strong>₹{{ number_format($quote['details']['vehicle_rent'], 2) }} rent + ₹{{ number_format($quote['details']['per_km_rate'], 2) }}/KM</strong>
                                @if($quote['details']['distance_km'] !== null)<div style="font-size: 0.85rem;">Estimated fare ₹{{ number_format($quote['total_amount'], 2) }}</div>@endif
                            @else
                                <strong style="font-size: 1.15rem;">₹{{ number_format($quote['total_amount'], 2) }}{{ $quote['details']['gst_applicable'] ? ' + GST' : '' }}</strong>
                            @endif
                            <div style="margin-top: 0.5rem;">
                                <a class="btn btn-primary btn-sm" href="{{ route('booking', ['rate_category' => request('rate_category'), 'pickup_location' => request('from'), 'destination' => request('to'), 'vehicle_category' => $quote['price']->vehicle_category]) }}">Book this rate</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: var(--slate-600);">Rate not configured. Please contact Vaishnavi Tours.</p>
                @endforelse
            </section>
        @endif

        @foreach(['airport' => 'Quick Airport Bookings', 'outstation' => 'Outstation Routes'] as $key => $heading)
            <section style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.75rem;">{{ $heading }}</h2>
                <div class="table-responsive card" style="padding: 1rem;">
                    <table class="table">
                        <thead><tr><th>Route</th><th>Vehicle</th><th>Fare</th><th>Included</th><th>Extra running</th><th>Other charges</th></tr></thead>
                        <tbody>
                            @foreach($routes->where('category', $key) as $route)
                                @foreach($route->vehiclePrices as $price)
                                    <tr>
                                        <td>{{ $route->origin }} ↔ {{ $route->destination }}</td>
                                        <td>{{ $price->vehicle_category }}</td>
                                        <td>₹{{ number_format((float) $price->base_fare, 2) }}{{ $price->gst_applicable ? ' + GST' : '' }}</td>
                                        <td>{{ $route->km_limit ? $route->km_limit . ' KM' : '' }}{{ $route->km_limit && $route->included_hours ? ' · ' : '' }}{{ $route->included_hours ? rtrim(rtrim(number_format((float) $route->included_hours, 2), '0'), '.') . ' hours' : '' }}</td>
                                        <td>
                                            @if($price->extra_km_rate !== null)₹{{ number_format((float) $price->extra_km_rate, 2) }}/KM @endif
                                            @if($price->extra_hour_rate !== null)₹{{ number_format((float) $price->extra_hour_rate, 2) }}/hour @endif
                                        </td>
                                        <td>@if($price->toll_type)Toll {{ $price->toll_type }}@endif @if($price->parking_type) Parking {{ $price->parking_type }}@endif</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach

        @foreach(['round_trip' => 'Round Trip', 'local_8h_80km' => 'Local Rentals · 8 Hours / 80 KM', 'local_4h_40km' => 'Local Rentals · 4 Hours / 40 KM'] as $key => $heading)
            @php($package = $routes->firstWhere('category', $key))
            @if($package)
                <section style="margin-bottom: 2rem;">
                    <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.75rem;">{{ $heading }}</h2>
                    <div class="table-responsive card" style="padding: 1rem;">
                        <table class="table">
                            <thead><tr><th>Vehicle</th><th>Fare</th><th>Included</th><th>Extra KM</th><th>Extra hour</th><th>Additional charges</th></tr></thead>
                            <tbody>
                                @foreach($package->vehiclePrices as $price)
                                    <tr>
                                        <td>{{ $price->vehicle_category }}</td>
                                        <td>@if($price->vehicle_rent !== null)₹{{ number_format((float) $price->vehicle_rent, 2) }} rent + ₹{{ number_format((float) $price->per_km_rate, 2) }}/KM @else ₹{{ number_format((float) $price->base_fare, 2) }} @endif</td>
                                        <td>{{ $package->included_hours ? rtrim(rtrim(number_format((float) $package->included_hours, 2), '0'), '.') . ' hours' : '' }}{{ $package->km_limit ? ' · ' . $package->km_limit . ' KM' : '' }}</td>
                                        <td>{{ $price->extra_km_rate !== null ? '₹' . number_format((float) $price->extra_km_rate, 2) . '/KM' : '' }}</td>
                                        <td>{{ $price->extra_hour_rate !== null ? '₹' . number_format((float) $price->extra_hour_rate, 2) . '/hour' : '' }}</td>
                                        <td>@if($price->toll_type)Toll {{ $price->toll_type }}@endif @if($price->parking_type) Parking {{ $price->parking_type }}@endif @if($price->border_tax_type) Border tax {{ $price->border_tax_type }}@endif @if($price->night_charge !== null) Night ₹{{ number_format((float) $price->night_charge, 2) }}@endif @if($price->driver_food_type) Driver food {{ $price->driver_food_type }}@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        @endforeach

        @if($routes->isEmpty())
            <div class="card" style="padding: 2rem; text-align: center;">Rate not configured. Please contact Vaishnavi Tours.</div>
        @endif
    </div>
</section>
@endsection
