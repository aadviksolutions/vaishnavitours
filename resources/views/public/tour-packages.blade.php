@extends('layouts.app')

@section('title', 'Tour Packages & Sightseeing Trips | Vaishnavi Tours')
@section('meta_description', 'Explore curated holiday tour packages, temple yatras, and heritage tours across Chhattisgarh, Amarkantak, Sirpur, and Kanha National Park.')
@section('canonical', route('tour-packages'))

@section('content')
<div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 3.5rem 0 3rem; text-align: center;">
    <div class="container">
        <span style="color: var(--accent-gold); font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; display: block; margin-bottom: 0.5rem;">
            Curated Sightseeing & Holiday Circuits
        </span>
        <h1 style="font-size: 2.25rem; font-weight: 800; margin: 0 0 0.75rem 0; color: #fff;">Vaishnavi Tour Packages</h1>
        <p style="color: var(--slate-300); max-width: 650px; margin: 0 auto; font-size: 15px; line-height: 1.5;">
            Explore spiritual, wildlife, and heritage destinations with clean chauffeur-driven sedans and SUVs. Customized itineraries and transparent quotes.
        </p>
    </div>
</div>

<section style="padding: 3rem 0;">
    <div class="container">
        <div class="grid grid-3 gap-3">
            @forelse($packages as $pkg)
                <div class="card" style="border: 1px solid var(--slate-200); border-radius: 10px; overflow: hidden; background: #fff; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: transform 0.2s ease;">
                    <div style="padding: 1.25rem 1.5rem;">
                        <div class="d-flex justify-between align-center mb-2">
                            <span class="badge badge-primary" style="font-size: 11px; padding: 3px 8px;">
                                <x-icon name="clock" size="12" class="mr-1 inline" /> {{ $pkg->duration_days }} {{ Str::plural('Day', $pkg->duration_days) }}
                            </span>
                            @if($pkg->base_price > 0)
                                <span style="font-size: 14px; font-weight: 700; color: #059669;">
                                    From ₹{{ number_format($pkg->base_price, 0) }}
                                </span>
                            @else
                                <span style="font-size: 12px; font-weight: 600; color: #D97706;">Quotation upon request</span>
                            @endif
                        </div>

                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--dark-900); margin: 0 0 0.5rem 0;">
                            {{ $pkg->name }}
                        </h3>

                        <div style="font-size: 12.5px; color: #2563EB; font-weight: 600; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 4px;">
                            <x-icon name="route" size="14" /> {{ $pkg->route_summary ?: 'Custom Tour Route' }}
                        </div>

                        <p style="font-size: 13px; color: var(--slate-600); line-height: 1.5; margin-bottom: 1rem;">
                            {{ Str::limit($pkg->description, 140) }}
                        </p>

                        @if(!empty($pkg->destinations_list))
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--slate-400); margin-bottom: 0.25rem;">Destinations Covered:</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($pkg->destinations_list as $dest)
                                        <span style="background: var(--slate-100); color: var(--slate-700); font-size: 11px; padding: 2px 7px; border-radius: 4px; font-weight: 500;">
                                             {{ $dest }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div style="padding: 1rem 1.5rem; background: var(--slate-50); border-top: 1px solid var(--slate-100); display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11.5px; color: var(--slate-500);">GST & toll applicable</span>
                        <a href="{{ route('enquiry', ['subject' => 'Tour Package: ' . $pkg->name]) }}" class="btn btn-primary btn-sm" style="font-size: 12px; font-weight: 600;">
                            Request Quotation →
                        </a>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem; background: #fff; border: 1px dashed var(--slate-300); border-radius: 8px;">
                    <x-icon name="compass" size="36" class="text-slate-400 mb-2" />
                    <h3 style="font-size: 16px; font-weight: 700; color: var(--dark-900);">Custom Holiday & Tour Itineraries Available</h3>
                    <p style="color: var(--slate-500); font-size: 13.5px; max-width: 500px; margin: 0.5rem auto 1rem;">
                        We arrange customized tours to Amarkantak, Sirpur, Chitrakote Waterfalls, Mainpat, and Kanha Tiger Reserve.
                    </p>
                    <a href="{{ route('enquiry') }}" class="btn btn-primary btn-sm">Send Custom Tour Enquiry</a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
