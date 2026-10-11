@extends('layouts.app')

@section('title', 'Taxi Fleet & Cab Options in Bilaspur | Vaishnavi Tours')
@section('meta_description', 'Explore our verified fleet of rental cabs in Bilaspur: Maruti Dzire, Ertiga MUV, Innova Crysta & Tempo Traveller. Clean, air-conditioned, with experienced chauffeurs.')
@section('canonical', route('vehicles'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Vehicles & Fleet', 'item' => route('vehicles')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section style="background: var(--navy-900); color: #fff; padding: 2.25rem 0; border-bottom: 1px solid var(--navy-800);">
    <div class="container text-center">
        <span style="color: var(--accent-gold); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px;">Verified Fleet</span>
        <h1 style="color: #fff; font-size: 1.65rem; font-weight: 600; margin-top: 0.25rem;">Taxi Fleet &amp; Available Vehicles</h1>
        <p style="color: var(--slate-300); max-width: 580px; margin: 0.35rem auto 0; font-size: 0.875rem;">GPS-tracked commercial passenger vehicles, comprehensively insured and maintained to strict quality standards.</p>
    </div>
</section>

<section style="padding: 2.5rem 0 3.5rem; background: var(--slate-50);">
    <div class="container">
        <!-- Category Filter Pills -->
        <div class="d-flex justify-center gap-2 flex-wrap" style="margin-bottom: 1.75rem;">
            <button class="btn btn-primary btn-sm fleet-filter-btn" onclick="filterFleet('all')">All Vehicles</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('sedan')">Sedan</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('suv')">SUV</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('muv')">MUV</button>
            <button class="btn btn-outline btn-sm fleet-filter-btn" onclick="filterFleet('traveller')">Traveller</button>
        </div>

        <div class="grid grid-3 gap-3" id="fleetGrid">
            @foreach($vehicles as $vehicle)
                <div class="vehicle-card" data-category="{{ strtolower($vehicle->vehicle_type) }}" style="border: 1px solid var(--slate-200); border-radius: var(--radius-md); background: #fff; box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column;">
                    <div class="vehicle-card-img" style="height: 150px; padding: 0.75rem; background: #f8fafc; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid var(--slate-100);">
                        <img src="{{ $vehicle->icon_url }}" alt="{{ $vehicle->name }} - {{ $vehicle->vehicle_type }} Taxi Cab in Bilaspur" style="max-height: 120px; width: auto; object-fit: contain;">
                    </div>
                    <div class="vehicle-card-body" style="padding: 1.15rem; display: flex; flex-direction: column; flex: 1;">
                        <div class="d-flex justify-between align-center" style="margin-bottom: 0.35rem;">
                            <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--dark-900); margin: 0;">{{ $vehicle->name }}</h3>
                            <span class="badge {{ $vehicle->status === 'Available' ? 'badge-available' : 'badge-pending' }}">
                                {{ $vehicle->status === 'Available' ? 'Available' : 'Unavailable' }}
                            </span>
                        </div>

                        <div style="display: flex; gap: 0.5rem; font-size: 0.775rem; color: var(--slate-600); margin-bottom: 0.75rem; flex-wrap: wrap; align-items: center;">
                            <span class="badge badge-outline" style="font-weight: 600; color: var(--dark-900);">{{ $vehicle->vehicle_type }}</span>
                            <span><x-icon name="users" size="13" style="vertical-align: middle; margin-right: 2px;" /> {{ $vehicle->seating_capacity }} Seats</span>
                            <span style="color: var(--slate-300);">•</span>
                            <span><x-icon name="snowflake" size="13" style="vertical-align: middle; margin-right: 2px;" /> {{ $vehicle->ac_non_ac }}</span>
                        </div>

                        <p style="font-size: 0.8rem; color: var(--slate-600); margin-bottom: 1rem; flex: 1; line-height: 1.45;">
                            {{ $vehicle->notes }}
                        </p>

                        <div style="background: var(--slate-50); padding: 0.65rem 0.75rem; border-radius: var(--radius-sm); margin-bottom: 1rem; border: 1px solid var(--slate-200);">
                            <div class="d-flex justify-between align-center" style="margin-bottom: 0.25rem;">
                                <span style="font-size: 0.75rem; color: var(--slate-500);">Outstation Fare:</span>
                                <a href="{{ route('rates') }}" style="font-size: 0.775rem; color: var(--primary); font-weight: 600;">View route rates</a>
                            </div>
                            <div class="d-flex justify-between align-center">
                                <span style="font-size: 0.75rem; color: var(--slate-500);">Local Rental:</span>
                                <span style="font-size: 0.775rem; color: var(--slate-700);">4 Hr &amp; 8 Hr packages</span>
                            </div>
                        </div>

                        <a href="{{ route('booking') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center; height: 34px;">
                            Book This Vehicle <x-icon name="arrow-right" size="14" style="margin-left: 4px;" />
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
