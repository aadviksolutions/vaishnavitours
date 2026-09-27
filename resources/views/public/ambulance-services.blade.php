@extends('layouts.app')

@section('title', '24/7 Ambulance Services | Vaishnavi Tours')
@section('meta_description', 'Vaishnavi Tours provides ambulance transportation support including body freezer ambulance, ventilator ambulance, ICU ambulance and deceased-person transfer services.')
@section('canonical', route('ambulance-services'))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Ambulance Services', 'item' => route('ambulance-services')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section class="ambulance-page-hero">
    <div class="container">
        <span class="ambulance-kicker">Vaishnavi Tours Specialized Service</span>
        <h1>24/7 Ambulance Services</h1>
        <p>Reliable Ambulance Support for Emergency &amp; Non-Emergency Transportation</p>
    </div>
</section>

<section class="ambulance-page-intro">
    <div class="container">
        <p>Vaishnavi Tours provides dependable ambulance transportation support for emergency and non-emergency requirements, with services available for local and outstation transfers.</p>
    </div>
</section>

<section class="ambulance-page-services" aria-labelledby="ambulance-services-heading">
    <div class="container">
        <div class="ambulance-page-heading">
            <span class="ambulance-kicker">Transportation Options</span>
            <h2 id="ambulance-services-heading">Our Ambulance Services</h2>
        </div>
        @include('public.partials.ambulance-service-cards', ['compact' => false])
    </div>
</section>

<section class="ambulance-page-why" aria-labelledby="ambulance-why-heading">
    <div class="container">
        <div class="ambulance-page-heading">
            <span class="ambulance-kicker">Vaishnavi Tours</span>
            <h2 id="ambulance-why-heading">Why Choose Our Ambulance Service?</h2>
        </div>
        <div class="grid grid-3 gap-3">
            <div class="ambulance-reason"><x-icon name="clock-3" size="22" /> <span>24/7 Assistance</span></div>
            <div class="ambulance-reason"><x-icon name="route" size="22" /> <span>Local &amp; Outstation Transportation</span></div>
            <div class="ambulance-reason"><x-icon name="heart" size="22" /> <span>Safe &amp; Respectful Handling</span></div>
            <div class="ambulance-reason"><x-icon name="truck" size="22" /> <span>Multiple Ambulance Service Options</span></div>
            <div class="ambulance-reason"><x-icon name="radio" size="22" /> <span>Quick Coordination</span></div>
            <div class="ambulance-reason"><x-icon name="life-buoy" size="22" /> <span>Dedicated Support</span></div>
        </div>
    </div>
</section>

<section class="ambulance-contact-cta" aria-labelledby="ambulance-contact-heading">
    <div class="container">
        <h2 id="ambulance-contact-heading">Need Ambulance Assistance?</h2>
        <p>Contact Vaishnavi Tours for ambulance availability and service details.</p>
        <div class="ambulance-cta-actions">
            <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-primary btn-lg">
                <x-icon name="phone" size="18" style="margin-right: 6px;" /> Call Now
            </a>
            <a href="{{ config('vaishnavi.whatsapp_link') }}" class="btn btn-outline btn-lg" target="_blank" rel="noopener noreferrer" style="color: var(--white); border-color: rgba(255,255,255,0.45);">
                <x-icon.whatsapp size="19" style="margin-right: 6px;" /> WhatsApp Us
            </a>
        </div>
    </div>
</section>
@endsection