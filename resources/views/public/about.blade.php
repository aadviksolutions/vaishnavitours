@extends('layouts.app')

@php
    $owner = [
        'name' => 'Tarun Kumar Soni',
        'designation' => 'Owner / Founder',
        'description' => 'Tarun Kumar Soni stands as a hallmark of excellence in transportation services throughout Bilaspur. With an unwavering commitment to passenger comfort and safety, his reputation precedes him in the local transit ecosystem. His approach to service transcends mere transportation—creating experiences characterized by reliability, punctuality, and a sincere attention to detail. The fleet under his supervision maintains impeccable standards of cleanliness and mechanical integrity. Whether navigating city streets or traversing longer distances, clients consistently praise the refined experience his services provide—a testament to his decade-long dedication to elevating the standard of transit in the region.',
        'image' => 'assets/team/owner.jpg',
    ];

    $teamMembers = [
        ['name' => 'Staff Name', 'designation' => 'Manager', 'description' => 'Dedicated to ensuring smooth operations, coordinating our team, and delivering a seamless and reliable travel experience for every customer.', 'image' => 'assets/team/staff-01.jpg'],
        ['name' => 'Jageshwar Mishra', 'designation' => 'Ambulance Expert', 'description' => 'Experienced in ambulance services, ensuring timely coordination, safe transportation, and dependable assistance during medical emergencies.', 'image' => 'assets/team/staff-02.jpg'],
        ['name' => 'Staff Name', 'designation' => 'Designation', 'description' => 'A short professional description will be added when team details are provided.', 'image' => 'assets/team/staff-03.jpg'],
        ['name' => 'Staff Name', 'designation' => 'Designation', 'description' => 'A short professional description will be added when team details are provided.', 'image' => 'assets/team/staff-04.jpg'],
        ['name' => 'Staff Name', 'designation' => 'Designation', 'description' => 'A short professional description will be added when team details are provided.', 'image' => 'assets/team/staff-05.jpg'],
        ['name' => 'Staff Name', 'designation' => 'Designation', 'description' => 'A short professional description will be added when team details are provided.', 'image' => 'assets/team/staff-06.jpg'],
    ];

    $portraitPlaceholder = 'assets/team/portrait-placeholder.svg';
@endphp

@section('title', 'About Vaishnavi Tours | Taxi Service Across Chhattisgarh')
@section('meta_description', 'Meet Vaishnavi Tours, providing 24/7 local, airport and outstation taxi services across Bilaspur, Raipur, Raigarh, Korba, Ambikapur, Bhilai and nearby destinations.')
@section('canonical', route('about'))

@push('styles')
<style>
    .about-page-content {
        padding: 4rem 0 5rem;
        background: var(--slate-50);
    }
    .about-page-container {
        max-width: 1080px;
    }
    .about-intro-card {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        gap: 2rem;
        align-items: center;
        padding: 2.5rem;
        margin-bottom: 4rem;
    }
    .about-section-kicker {
        color: var(--primary-hover);
        font-size: 0.82rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .about-intro-title {
        margin: 0.4rem 0 1rem;
        color: var(--dark-900);
        font-size: 2rem;
    }
    .about-intro-copy {
        margin-bottom: 1rem;
        color: var(--slate-700);
        font-size: 1rem;
        line-height: 1.8;
    }
    .about-service-areas {
        margin-bottom: 1.25rem;
        color: var(--slate-600);
        font-size: 0.95rem;
        line-height: 1.7;
    }
    .about-owner-section,
    .about-team-section {
        margin-bottom: 4rem;
    }
    .about-section-heading {
        margin-bottom: 1.5rem;
    }
    .about-section-heading h2 {
        margin-top: 0.35rem;
        color: var(--dark-900);
        font-size: 2rem;
    }
    .about-owner-card {
        display: grid;
        grid-template-columns: minmax(260px, 0.82fr) minmax(0, 1.18fr);
        overflow: hidden;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        background: var(--white);
        box-shadow: var(--shadow-xl);
    }
    .about-portrait-frame {
        overflow: hidden;
        aspect-ratio: 4 / 5;
        background: var(--dark-900);
    }
    .about-portrait {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .about-owner-copy {
        align-self: center;
        padding: clamp(1.5rem, 4vw, 3.5rem);
    }
    .about-owner-copy h3 {
        margin: 0.5rem 0 0.35rem;
        color: var(--dark-900);
        font-size: 2.25rem;
        overflow-wrap: anywhere;
    }
    .about-person-designation {
        margin-bottom: 1rem;
        color: var(--primary-dark);
        font-weight: 800;
    }
    .about-person-description {
        color: var(--slate-600);
        font-size: 0.95rem;
        line-height: 1.75;
        overflow-wrap: anywhere;
    }
    .about-team-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
    }
    .about-team-card {
        min-width: 0;
        overflow: hidden;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-lg);
        background: var(--white);
        box-shadow: var(--shadow-md);
    }
    .about-team-card .about-portrait-frame {
        aspect-ratio: 4 / 5;
    }
    .about-team-copy {
        padding: 1.25rem 1.35rem 1.5rem;
    }
    .about-team-copy h3 {
        margin-bottom: 0.35rem;
        color: var(--dark-900);
        font-size: 1.2rem;
        overflow-wrap: anywhere;
    }
    .about-team-copy .about-person-designation {
        margin-bottom: 0.75rem;
        font-size: 0.9rem;
    }
    .about-pillars {
        margin-top: 0;
    }
    @media (max-width: 992px) {
        .about-team-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .about-intro-card {
            grid-template-columns: minmax(0, 1fr) minmax(250px, 0.8fr);
            gap: 1.5rem;
            padding: 2rem;
        }
    }
    @media (max-width: 768px) {
        .about-page-content {
            padding: 3rem 0 4rem;
        }
        .about-intro-card,
        .about-owner-card {
            grid-template-columns: minmax(0, 1fr);
        }
        .about-owner-card .about-portrait-frame {
            max-height: 420px;
        }
        .about-owner-copy h3 {
            font-size: 1.9rem;
        }
        .about-owner-section,
        .about-team-section {
            margin-bottom: 3rem;
        }
    }
    @media (max-width: 480px) {
        .about-page-content {
            padding-top: 2.5rem;
        }
        .about-intro-card {
            padding: 1.25rem;
        }
        .about-section-heading h2,
        .about-intro-title {
            font-size: 1.7rem;
        }
        .about-team-grid {
            grid-template-columns: minmax(0, 1fr);
            gap: 1rem;
        }
    }
</style>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'About Us', 'item' => route('about')],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<section style="background: var(--dark-900); color: #fff; padding: 3.5rem 0;">
    <div class="container text-center">
        <span style="color: var(--primary); font-weight: 800; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;">Company Overview</span>
        <h1 style="color: #fff; font-size: 2.5rem; margin-top: 0.25rem;">About Vaishnavi Tours</h1>
        <p style="color: var(--slate-300); max-width: 720px; margin: 0.5rem auto 0;">Local, airport, and outstation travel across Chhattisgarh and nearby destinations.</p>
    </div>
</section>

<section class="about-page-content">
    <div class="container about-page-container">
        <div class="card about-intro-card">
            <div>
                <span class="about-section-kicker">About Vaishnavi Tours</span>
                <h2 class="about-intro-title">Reliable travel, across the region</h2>
                <p class="about-intro-copy">
                    <strong>Vaishnavi Tours</strong> is a professional taxi and cab service providing reliable 24/7 local, airport and outstation transportation services across Chhattisgarh and nearby destinations.
                </p>
                <p class="about-service-areas">
                    Services are available across Bilaspur, Raipur, Raigarh, Korba, Ambikapur, Bhilai and other destinations.
                </p>
                <div style="background: var(--slate-100); padding: 1rem 1.25rem; border-radius: var(--radius-md); border-left: 3px solid var(--primary); font-size: 0.875rem; color: var(--dark-900);">
                    <x-icon name="map-pin" size="18" class="text-primary" style="margin-right: 6px;" /><strong>Office Address:</strong><br>
                    {{ config('vaishnavi.address') }}
                </div>
            </div>

            <div>
                <div class="about-visual-wrap">
                    <img src="{{ asset('assets/images/about-travel-v2.jpg') }}" alt="Vaishnavi Tours travel service" class="about-visual-img" style="height: 380px;">
                </div>
            </div>
        </div>

        <section class="about-owner-section" aria-labelledby="about-owner-heading">
            <div class="about-section-heading">
                <span class="about-section-kicker">Leadership</span>
                <h2 id="about-owner-heading">Meet Our Owner</h2>
            </div>
            <article class="about-owner-card">
                <div class="about-portrait-frame">
                    @php($ownerPhotoExists = file_exists(public_path($owner['image'])))
                    <img
                        src="{{ asset($ownerPhotoExists ? $owner['image'] : $portraitPlaceholder) }}"
                        alt="{{ $ownerPhotoExists ? $owner['name'] : 'Portrait placeholder for '.$owner['name'] }}"
                        class="about-portrait"
                    >
                </div>
                <div class="about-owner-copy">
                    <span class="about-section-kicker">Founder &amp; Owner</span>
                    <h3>{{ $owner['name'] }}</h3>
                    <p class="about-person-designation">{{ $owner['designation'] }}</p>
                    <p class="about-person-description">{{ $owner['description'] }}</p>
                </div>
            </article>
        </section>

        <section class="about-team-section" aria-labelledby="about-team-heading">
            <div class="about-section-heading">
                <span class="about-section-kicker">The People Behind The Service</span>
                <h2 id="about-team-heading">Meet Our Team</h2>
            </div>
            <div class="about-team-grid">
                @foreach ($teamMembers as $member)
                    @php($memberPhotoExists = file_exists(public_path($member['image'])))
                    <article class="about-team-card">
                        <div class="about-portrait-frame">
                            <img
                                src="{{ asset($memberPhotoExists ? $member['image'] : $portraitPlaceholder) }}"
                                alt="{{ $memberPhotoExists ? $member['name'] : 'Portrait placeholder for '.$member['name'] }}"
                                class="about-portrait"
                                loading="lazy"
                            >
                        </div>
                        <div class="about-team-copy">
                            <h3>{{ $member['name'] }}</h3>
                            <p class="about-person-designation">{{ $member['designation'] }}</p>
                            <p class="about-person-description">{{ $member['description'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="grid grid-3 gap-3 about-pillars">
            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="clock-3" size="28" /></div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">24/7 Cab Service</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Active dispatch available all day and night for scheduled pickups, late night journeys, and emergency travel needs.
                </p>
            </div>

            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="award" size="28" /></div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Professional Drivers</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Verified, skilled chauffeurs who understand passenger safety, regional highway conditions, and courteous service.
                </p>
            </div>

            <div class="card" style="text-align: center; padding: 2rem 1.5rem;">
                <div class="icon-box icon-box-lg icon-box-primary" style="margin-bottom: 1.25rem;"><x-icon name="shield-check" size="28" /></div>
                <h3 style="font-size: 1.2rem; font-weight: 800; margin-bottom: 0.5rem;">Customer-Focused Service</h3>
                <p style="font-size: 0.885rem; color: var(--slate-600); line-height: 1.6;">
                    Clean, air-conditioned cars with clear transparent billing and dependable on-time doorstep arrival.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
