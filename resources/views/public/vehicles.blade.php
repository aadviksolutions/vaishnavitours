@extends('layouts.app')

@section('title', 'Vehicles & Fleet - Vaishnavi Tours Bilaspur')
@section('meta_description', 'Explore our modern taxi fleet in Bilaspur: Sedan, SUV, MUV, and Tempo Traveller. Air-conditioned, sanitized, and commercial passenger licensed.')

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Our Vehicle Fleet</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Explore Our Cabs & Vehicles</h1>
        <p style="color: var(--slate-300); max-width: 600px; margin: 0.5rem auto 0;">All vehicles are GPS-enabled, fully commercial passenger insured, and rigorously sanitized before every dispatch.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem; background: var(--slate-50);">
    <div class="container">
        <!-- Category Filter Pills -->
        <div class="d-flex justify-center gap-2 flex-wrap" style="margin-bottom: 3rem;">
            <button class="btn btn-primary btn-sm fleet-filter-btn" onclick="filterFleet('all')">All Vehicles</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('sedan')">Sedan</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('suv')">SUV</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('muv')">MUV</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('traveller')">Traveller</button>
        </div>

        <div class="grid grid-3 gap-3" id="fleetGrid">
            @foreach($vehicles as $vehicle)
                <div class="vehicle-card" data-category="{{ strtolower($vehicle->vehicle_type) }}">
                    <div class="vehicle-card-img" style="height: 200px; padding: 1rem; background: #f8fafc;">
                        <img src="{{ $vehicle->icon_url }}" alt="{{ $vehicle->name }}" style="max-height: 160px; width: auto; object-fit: contain;">
                    </div>
                    <div class="vehicle-card-body">
                        <div class="d-flex justify-between align-center" style="margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark-900);">{{ $vehicle->name }}</h3>
                            <span class="badge {{ $vehicle->status === 'Available' ? 'badge-available' : 'badge-pending' }}">
                                {{ $vehicle->status === 'Available' ? 'Available' : 'Unavailable' }}
                            </span>
                        </div>

                        <div style="display: flex; gap: 0.75rem; font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem; flex-wrap: wrap;">
                            <span class="badge badge-outline" style="font-weight: 700; color: var(--dark-900);">{{ $vehicle->vehicle_type }}</span>
                            <span>👥 {{ $vehicle->seating_capacity }} Seats</span>
                            <span>•</span>
                            <span>❄️ {{ $vehicle->ac_non_ac }}</span>
                        </div>

                        <p style="font-size: 0.875rem; color: var(--slate-600); margin-bottom: 1.25rem; flex: 1; line-height: 1.5;">
                            {{ $vehicle->notes }}
                        </p>

                        <div style="background: var(--slate-50); padding: 0.85rem; border-radius: var(--radius-md); margin-bottom: 1.25rem; border: 1px solid var(--slate-200);">
                            <div class="d-flex justify-between align-center" style="margin-bottom: 0.35rem;">
                                <span style="font-size: 0.8rem; color: var(--slate-500);">Outstation Rate:</span>
                                <strong style="font-size: 1.15rem; color: var(--dark-900);">₹{{ number_format($vehicle->per_km_rate, 2) }} <span style="font-size: 0.8rem; color: var(--slate-500); font-weight: 400;">/ km</span></strong>
                            </div>
                            <div class="d-flex justify-between align-center">
                                <span style="font-size: 0.8rem; color: var(--slate-500);">Local Hourly Rate:</span>
                                <strong style="font-size: 0.95rem; color: var(--slate-700);">₹{{ number_format($vehicle->per_hour_rate, 2) }} <span style="font-size: 0.8rem; color: var(--slate-500); font-weight: 400;">/ hr</span></strong>
                            </div>
                        </div>

                        <a href="{{ route('booking') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-primary" style="width: 100%;">
                            🚖 Book Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
    function filterFleet(cat) {
        const cards = document.querySelectorAll('#fleetGrid .vehicle-card');
        const buttons = document.querySelectorAll('.fleet-filter-btn');

        buttons.forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
        });
        event.target.classList.remove('btn-outline');
        event.target.classList.add('btn-primary');

        cards.forEach(card => {
            const cardCat = card.getAttribute('data-category') || '';
            if (cat === 'all' || cardCat.includes(cat)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }
</script>
@endpush
@endsection
