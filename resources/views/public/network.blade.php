@extends('layouts.app')

@section('title', 'Service Network & Outstation Routes | Bilaspur | Vaishnavi Tours')
@section('meta_description', 'Vaishnavi Tours operates intercity cabs across Chhattisgarh: Bilaspur to Raipur, Korba, Ambikapur, Raigarh, Durg-Bhilai, Jagdalpur & tourist destinations.')
@section('canonical', route('service-network'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Service Network', 'item' => route('service-network')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem;">Regional Connectivity</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">Chhattisgarh Service Network & <span>Outstation Cab Routes</span></h1>
        <p style="color: var(--slate-300); max-width: 600px; margin: 0.5rem auto 0;">Connecting Bilaspur to every district in Chhattisgarh and major tourist & business corridors across India.</p>
    </div>
</section>

<section style="padding: 4rem 0 5rem;">
    <div class="container">
        <div class="grid grid-3 gap-3">
            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="building-2" size="20" class="text-primary" style="margin-right: 8px;" />Bilaspur Central Corridor</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Daily on-demand city and peripheral cab services.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Mangal Chowk Hub</li>
                    <li>• Chhattisgarh High Court Bodri</li>
                    <li>• Bilaspur Junction Railway Station</li>
                    <li>• Tifra & Sirgitti Industrial Area</li>
                    <li>• Ratanpur Mahamaya Temple</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="plane" size="20" class="text-primary" style="margin-right: 8px;" />Raipur & Capital Region</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Frequent expressway transfers with punctual flight pickups.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Swami Vivekananda Airport (RPR)</li>
                    <li>• AIIMS Raipur Medical Complex</li>
                    <li>• Naya Raipur (Atal Nagar)</li>
                    <li>• Durg & Bhilai Steel City</li>
                    <li>• Rajnandgaon Border Zone</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="factory" size="20" class="text-primary" style="margin-right: 8px;" />Industrial Coal & Power Belt</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Corporate car hires for power plants and mining executives.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Korba (NTPC & Balco)</li>
                    <li>• Raigarh (Jindal Steel & Power)</li>
                    <li>• Champa & Janjgir</li>
                    <li>• Gevra & Dipka SECL Mines</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="trees" size="20" class="text-primary" style="margin-right: 8px;" />North Chhattisgarh & Hills</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Senior hill chauffeurs for tourist and official visits.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Ambikapur City</li>
                    <li>• Mainpat Tibetan Settlement</li>
                    <li>• Baikunthpur & Koriya</li>
                    <li>• Jashpur Nagar</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="compass" size="20" class="text-primary" style="margin-right: 8px;" />Bastar & South Chhattisgarh</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Safe, comfortable multi-day packages to the heart of Bastar.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Jagdalpur & Chitrakote Waterfalls</li>
                    <li>• Tirathgarh & Kanger Valley</li>
                    <li>• Kondagaon Bell Metal Craft Belt</li>
                    <li>• Dantewada Danteshwari Temple</li>
                </ul>
            </div>

            <div class="card">
                <h3 style="font-size: 1.2rem; margin-bottom: 0.75rem; color: var(--dark-900);"><x-icon name="sparkles" size="20" class="text-primary" style="margin-right: 8px;" />Wildlife & Pilgrimage Outstation</h3>
                <p style="font-size: 0.875rem; color: var(--slate-500); margin-bottom: 1rem;">Interstate tourist transfers to top national parks.</p>
                <ul style="font-size: 0.9rem; color: var(--slate-700); line-height: 1.8;">
                    <li>• Amarkantak Narmada Udgam (~110 KM)</li>
                    <li>• Kanha National Park (~240 KM)</li>
                    <li>• Bandhavgarh Tiger Reserve (~260 KM)</li>
                    <li>• Nagpur & Varanasi Highway Connections</li>
                </ul>
            </div>
        </div>

        <div style="background: var(--dark-900); color: #fff; border-radius: var(--radius-lg); padding: 2.5rem; text-align: center; margin-top: 3rem;">
            <h3 style="color: #fff; font-size: 1.75rem; margin-bottom: 0.5rem;">Need a Custom Outstation Route?</h3>
            <p style="color: var(--slate-300); margin-bottom: 1.5rem;">We provide customized per-km intercity tours with overnight driver arrangements.</p>
            <a href="{{ route('booking') }}" class="btn btn-primary btn-lg">Plan Your Outstation Route Now</a>
        </div>
    </div>
</section>
@endsection
