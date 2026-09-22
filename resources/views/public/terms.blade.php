@extends('layouts.app')

@section('title', 'Taxi Service Cancellation Policy &amp; Terms &amp; Conditions - Vaishnavi Tours')
@section('meta_description', 'Official Taxi Service Cancellation Policy and Terms &amp; Conditions for Vaishnavi Tours Taxi Service. Read booking confirmation, cancellation, fare rules, and customer responsibilities.')

@push('styles')
<style>
/* ===================================================
   TERMS & CONDITIONS - RESPONSIVE & MOBILE DESIGN
   =================================================== */

/* Hero Section */
.terms-hero {
    background: var(--dark-900);
    color: #ffffff;
    padding: clamp(2.25rem, 5vw, 3.5rem) 0;
    text-align: center;
}
.terms-hero-badge {
    color: var(--primary);
    font-weight: 800;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 1px;
    display: inline-block;
    margin-bottom: 0.35rem;
}
.terms-hero-title {
    color: #ffffff;
    font-size: clamp(26px, 5.5vw, 38px);
    margin: 0.25rem auto 0;
    font-weight: 800;
    line-height: 1.25;
    max-width: 900px;
    word-break: break-word;
}
.terms-hero-subtitle {
    color: var(--slate-300);
    max-width: 720px;
    margin: 0.75rem auto 0;
    font-size: clamp(0.925rem, 2.5vw, 1rem);
    line-height: 1.6;
}
.terms-hero-tags {
    margin-top: 1.25rem;
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Breadcrumb */
.terms-breadcrumb-bar {
    background: #ffffff;
    border-bottom: 1px solid var(--slate-200);
    padding: 0.75rem 0;
}
.terms-breadcrumb-list {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 0.875rem;
    color: var(--slate-500);
    flex-wrap: wrap;
}
.terms-breadcrumb-list a {
    color: var(--slate-600);
    text-decoration: none;
    font-weight: 600;
}
.terms-breadcrumb-list a:hover {
    color: var(--primary-dark);
}
.terms-breadcrumb-list .separator {
    color: var(--slate-400);
}
.terms-breadcrumb-list .current {
    color: var(--dark-900);
    font-weight: 700;
}

/* Main Section Layout */
.terms-content-section {
    padding: clamp(1.75rem, 4vw, 3.5rem) 0 4.5rem;
    background: var(--slate-50);
}
.terms-container {
    max-width: 1120px;
    box-sizing: border-box;
    width: 100%;
}
.terms-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
    align-items: start;
    box-sizing: border-box;
    width: 100%;
}

/* Desktop Sidebar TOC */
.desktop-toc-sidebar {
    position: sticky;
    top: 90px;
}
.desktop-toc-card {
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    border-top: 4px solid var(--primary);
    box-shadow: 0 4px 15px rgba(0,0,0,0.04);
    background: #ffffff;
    border: 1px solid var(--slate-200);
}
.desktop-toc-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--dark-950);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.desktop-toc-links {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    font-size: 0.875rem;
}
.desktop-toc-link {
    color: var(--slate-700);
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    text-decoration: none;
    display: block;
    transition: all 0.2s;
}
.desktop-toc-link:hover, .desktop-toc-link.active {
    color: var(--primary-dark);
    background: rgba(245, 158, 11, 0.08);
    font-weight: 700;
}

/* Mobile Collapsible "On This Page" */
.mobile-toc-wrapper {
    display: none;
    margin-bottom: 1.5rem;
    width: 100%;
    box-sizing: border-box;
}
.mobile-toc-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    border: 1.5px solid var(--primary);
    border-radius: var(--radius-md);
    padding: 0.85rem 1.15rem;
    font-family: var(--font-heading);
    font-size: 1rem;
    font-weight: 800;
    color: var(--dark-950);
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    min-height: 48px;
    box-sizing: border-box;
    transition: all 0.2s;
}
.mobile-toc-btn-text {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.mobile-toc-indicator {
    color: var(--primary-dark);
    font-size: 0.85rem;
    transition: transform 0.2s ease;
}
.mobile-toc-menu {
    display: none;
    background: #ffffff;
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-md);
    margin-top: 0.5rem;
    padding: 0.75rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.mobile-toc-menu.show {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.mobile-toc-item {
    display: flex;
    align-items: center;
    padding: 0.65rem 0.75rem;
    color: var(--slate-700);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 600;
    border-radius: 6px;
    transition: background 0.2s;
    min-height: 42px;
    box-sizing: border-box;
}
.mobile-toc-item:hover, .mobile-toc-item:active {
    background: rgba(245, 158, 11, 0.1);
    color: var(--primary-dark);
}

/* Notice Banner */
.terms-notice-banner {
    background: #ffffff;
    border: 1.5px solid var(--slate-200);
    border-radius: var(--radius-lg);
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-sizing: border-box;
    width: 100%;
}
.terms-notice-banner p {
    margin: 0;
    font-size: clamp(0.9rem, 2.5vw, 0.95rem);
    color: var(--slate-700);
    line-height: 1.65;
}

/* Terms Cards & Accordion */
.terms-card {
    background: #ffffff;
    border: 1px solid var(--slate-200);
    border-radius: var(--radius-lg);
    margin-bottom: 1.5rem;
    box-sizing: border-box;
    width: 100%;
    box-shadow: 0 2px 10px rgba(0,0,0,0.03);
    transition: box-shadow 0.2s ease;
}
.terms-card:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}
.terms-card-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: none;
    border: none;
    padding: 1.5rem 1.75rem 1.25rem;
    text-align: left;
    margin: 0;
    box-sizing: border-box;
}
.terms-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.terms-num-badge {
    background: var(--dark-950);
    color: var(--primary);
    font-weight: 800;
    font-size: 0.9rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    flex-shrink: 0;
}
.terms-card-title {
    font-size: clamp(1.15rem, 3.5vw, 1.35rem);
    font-weight: 800;
    color: var(--dark-950);
    margin: 0;
    line-height: 1.3;
}
.accordion-toggle-icon {
    display: none;
}
.terms-card-body {
    padding: 0 1.75rem 1.75rem;
    box-sizing: border-box;
    width: 100%;
}
.terms-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}
.terms-list li {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-size: clamp(0.925rem, 2.5vw, 0.975rem);
    color: var(--slate-700);
    line-height: 1.6;
}
.terms-list .bullet {
    color: var(--primary);
    font-size: 1.1rem;
    line-height: 1.2;
    flex-shrink: 0;
}

/* Timeline grid in section 2 */
.cancellation-timeline-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
    box-sizing: border-box;
    width: 100%;
}
.timeline-tier-card {
    background: #ffffff;
    border: 1px solid var(--slate-200);
    border-radius: 8px;
    padding: 1rem;
    box-sizing: border-box;
}

/* Bottom Support / Questions Section */
.terms-support-card {
    background: #ffffff;
    border: 1.5px solid var(--primary);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    margin-bottom: 1.75rem;
    box-sizing: border-box;
    width: 100%;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
}
.terms-support-header {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
}
.terms-support-icon {
    font-size: 1.75rem;
    flex-shrink: 0;
}
.terms-support-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--dark-950);
    margin: 0;
}
.terms-support-subtitle {
    font-size: 0.875rem;
    color: var(--slate-600);
    margin: 0.2rem 0 0;
}
.terms-support-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.terms-support-item {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.terms-support-label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--dark-900);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.terms-support-actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.terms-call-btn, .terms-wa-btn {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    text-decoration: none;
    font-weight: 700;
    padding: 0.6rem 1.2rem;
    border-radius: var(--radius-md);
}
.terms-wa-btn {
    background: #25D366;
    color: #ffffff;
    border: 1px solid #20BA56;
}
.terms-wa-btn:hover {
    background: #1EBE5D;
    color: #ffffff;
}
.terms-support-address {
    font-size: 0.95rem;
    color: var(--slate-700);
    font-weight: 600;
}

/* Callout Box at bottom */
.terms-cta-callout {
    background: linear-gradient(135deg, var(--dark-950) 0%, #1e293b 100%);
    border-radius: var(--radius-lg);
    padding: clamp(1.5rem, 4vw, 2.25rem);
    color: #fff;
    text-align: center;
    box-sizing: border-box;
    width: 100%;
}
.terms-cta-title {
    font-size: clamp(1.15rem, 3.5vw, 1.35rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.5rem;
}
.terms-cta-desc {
    font-size: 0.925rem;
    color: var(--slate-300);
    max-width: 580px;
    margin: 0 auto 1.25rem;
    line-height: 1.6;
}
.terms-cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}
.terms-cta-buttons .btn {
    min-height: 48px;
    box-sizing: border-box;
}

/* ===================================================
   RESPONSIVE MEDIA QUERIES (<= 992px & MOBILE PHONES)
   =================================================== */

@media (max-width: 992px) {
    .terms-layout {
        display: block;
        width: 100%;
    }
    .desktop-toc-sidebar {
        display: none !important;
    }
    .mobile-toc-wrapper {
        display: block;
    }
    
    /* Accordion Behavior on Mobile */
    .terms-card-header {
        cursor: pointer;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
        padding: 1.15rem 1.25rem;
        min-height: 52px;
    }
    .accordion-toggle-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(245, 158, 11, 0.15);
        color: var(--primary-dark);
        font-weight: 800;
        font-size: 1.3rem;
        line-height: 1;
        flex-shrink: 0;
        margin-left: 0.5rem;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .terms-card.is-open .accordion-toggle-icon {
        background: var(--primary);
        color: var(--dark-950);
    }
    .terms-card-body {
        padding: 0 1.25rem 1.25rem;
    }
    .terms-card:not(.is-open) .terms-card-body {
        display: none;
    }
    .terms-card.is-open .terms-card-body {
        display: block;
    }

    .terms-cta-buttons {
        flex-direction: column;
        width: 100%;
    }
    .terms-cta-buttons .btn {
        width: 100%;
    }
    .terms-support-actions {
        flex-direction: column;
        width: 100%;
    }
    .terms-support-actions .btn {
        width: 100%;
    }
}

@media (max-width: 640px) {
    .terms-content-section {
        padding: 1.5rem 0 3.5rem;
    }
    .terms-container {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }
    .terms-card-header {
        padding: 1rem 1rem;
    }
    .terms-card-body {
        padding: 0 1rem 1.15rem;
    }
    .terms-notice-banner {
        padding: 1rem 1.15rem;
        margin-bottom: 1.25rem;
    }
    .cancellation-timeline-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    .terms-support-card {
        padding: 1.25rem 1rem;
    }
}

@media (max-width: 480px) {
    .terms-hero {
        padding: 2rem 0;
    }
    .terms-hero-title {
        font-size: 26px;
    }
    .terms-card-title {
        font-size: 1.1rem;
    }
    .terms-list li {
        font-size: 0.925rem;
    }
    .terms-num-badge {
        font-size: 0.8rem;
        padding: 0.2rem 0.5rem;
    }
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<section class="terms-hero">
    <div class="container text-center">
        <span class="terms-hero-badge">Customer Agreement &amp; Policies</span>
        <h1 class="terms-hero-title">Terms &amp; Conditions</h1>
        <p class="terms-hero-subtitle">
            Taxi Service Cancellation Policy &amp; Terms &amp; Conditions for Vaishnavi Tour's Taxi Service. Please read these terms carefully before booking.
        </p>
        <div class="terms-hero-tags">
            <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--primary); border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.85rem; padding: 0.4rem 0.9rem;">Effective Version: 1.0</span>
            <span class="badge" style="background: rgba(255, 255, 255, 0.1); color: #E2E8F0; font-size: 0.85rem; padding: 0.4rem 0.9rem;">Jurisdiction: Bilaspur (C.G.)</span>
        </div>
    </div>
</section>

<!-- Breadcrumb -->
<div class="terms-breadcrumb-bar">
    <div class="container" style="max-width: 1120px;">
        <nav aria-label="breadcrumb">
            <ol class="terms-breadcrumb-list">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li class="separator">/</li>
                <li class="current">Terms &amp; Conditions</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Content Section with Quick Navigation -->
<section class="terms-content-section">
    <div class="container terms-container">
        
        <!-- Mobile Collapsible "On This Page" (Shown on <= 992px) -->
        <div class="mobile-toc-wrapper">
            <button type="button" class="mobile-toc-btn" id="mobileTocBtn" aria-expanded="false">
                <span class="mobile-toc-btn-text">
                    <x-icon name="clipboard-list" size="18" style="margin-right: 6px;" /> On This Page
                </span>
                <span class="mobile-toc-indicator" id="mobileTocIndicator">▼</span>
            </button>
            <div class="mobile-toc-menu" id="mobileTocMenu">
                <a href="#booking-confirmation" class="mobile-toc-item">1. Booking Confirmation</a>
                <a href="#cancellation-policy" class="mobile-toc-item">2. Cancellation Policy</a>
                <a href="#rescheduling" class="mobile-toc-item">3. Rescheduling</a>
                <a href="#waiting-pickup" class="mobile-toc-item">4. Waiting &amp; Pickup</a>
                <a href="#fare-additional-charges" class="mobile-toc-item">5. Fare &amp; Additional Charges</a>
                <a href="#vehicle-driver" class="mobile-toc-item">6. Vehicle &amp; Driver</a>
                <a href="#customer-responsibilities" class="mobile-toc-item">7. Customer Responsibilities</a>
                <a href="#driver-payment-communication" class="mobile-toc-item">8. Driver &amp; Payment Communication</a>
                <a href="#unavoidable-circumstances" class="mobile-toc-item">9. Unavoidable Circumstances</a>
                <a href="#general-terms" class="mobile-toc-item">10. General Terms</a>
                <a href="#privacy-policy" class="mobile-toc-item" style="border-top: 1px dashed var(--slate-200); margin-top: 0.25rem; padding-top: 0.6rem;"><x-icon name="shield" size="15" style="margin-right: 6px;" /> Privacy Policy</a>
            </div>
        </div>

        <div class="terms-layout">
            
            <!-- Sticky Quick Navigation / Table of Contents (Desktop Only) -->
            <div class="desktop-toc-sidebar">
                <div class="desktop-toc-card">
                    <h3 class="desktop-toc-title">
                        <x-icon name="clipboard-list" size="18" style="margin-right: 6px;" /> Table of Contents
                    </h3>
                    <nav class="desktop-toc-links">
                        <a href="#booking-confirmation" class="desktop-toc-link">1. Booking Confirmation</a>
                        <a href="#cancellation-policy" class="desktop-toc-link">2. Cancellation Policy</a>
                        <a href="#rescheduling" class="desktop-toc-link">3. Rescheduling</a>
                        <a href="#waiting-pickup" class="desktop-toc-link">4. Waiting &amp; Pickup</a>
                        <a href="#fare-additional-charges" class="desktop-toc-link">5. Fare &amp; Additional Charges</a>
                        <a href="#vehicle-driver" class="desktop-toc-link">6. Vehicle &amp; Driver</a>
                        <a href="#customer-responsibilities" class="desktop-toc-link">7. Customer Responsibilities</a>
                        <a href="#driver-payment-communication" class="desktop-toc-link">8. Driver &amp; Payment Communication</a>
                        <a href="#unavoidable-circumstances" class="desktop-toc-link">9. Unavoidable Circumstances</a>
                        <a href="#general-terms" class="desktop-toc-link">10. General Terms</a>
                        <a href="#privacy-policy" class="desktop-toc-link" style="border-top: 1px dashed var(--slate-200); margin-top: 0.25rem; padding-top: 0.6rem;"><x-icon name="shield" size="15" style="margin-right: 6px;" /> Privacy Policy</a>
                    </nav>

                    <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--slate-200); text-align: center;">
                        <a href="{{ route('booking') }}" class="btn btn-primary btn-block btn-sm" style="font-weight: 700; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                            Book a Cab Now <x-icon name="arrow-right" size="16" style="margin-left: 4px;" />
                        </a>
                        <a href="{{ route('cancellation-refund-policy') }}" style="display: inline-block; margin-top: 0.6rem; font-size: 0.8rem; color: var(--slate-500); text-decoration: underline;">
                            View Standalone Cancellation Policy
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Legal Policy Content -->
            <div class="terms-main-content">
                
                <!-- Notice Banner -->
                <div class="terms-notice-banner">
                    <p>
                        These terms and conditions apply to all passenger transport and taxi booking services offered by <strong>Vaishnavi Tour's Taxi Service</strong> (headquartered at {{ config('vaishnavi.address') }}). By requesting, reserving, or utilizing any of our taxi vehicles, you acknowledge and agree to abide by the policies detailed below.
                    </p>
                </div>

                <!-- Section 1 -->
                <div id="booking-confirmation" class="terms-card is-open">
                    <div class="terms-card-header" data-target="body-booking-confirmation" role="button" tabindex="0" aria-expanded="true">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">1</span>
                            <h2 class="terms-card-title">Booking Confirmation</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">−</span>
                    </div>
                    <div class="terms-card-body" id="body-booking-confirmation">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>A booking will be considered confirmed only after confirmation from Vaishnavi Tour's Taxi Service.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>An advance payment may be required to confirm the booking.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Vehicle and driver details will be shared as per the booking and availability.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 2 -->
                <div id="cancellation-policy" class="terms-card is-open" style="border-left: 4px solid var(--primary); background: #FFFDF9;">
                    <div class="terms-card-header" data-target="body-cancellation-policy" role="button" tabindex="0" aria-expanded="true">
                        <div class="terms-header-left">
                            <span class="terms-num-badge" style="background: var(--primary); color: var(--dark-950);">2</span>
                            <h2 class="terms-card-title">Cancellation Policy</h2>
                            <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: var(--primary-dark); font-weight: 700; font-size: 0.8rem;">Standard Schedule</span>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">−</span>
                    </div>

                    <div class="terms-card-body" id="body-cancellation-policy">
                        <!-- Highlighted Timeline Grid -->
                        <div class="cancellation-timeline-grid">
                            <div class="timeline-tier-card">
                                <div style="color: #059669; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">≥ 24 Hours Before</div>
                                <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">No cancellation charge, subject to applicable transaction or processing charges, if any.</div>
                            </div>
                            <div class="timeline-tier-card">
                                <div style="color: #D97706; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">12 – 24 Hours Before</div>
                                <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">Up to 25% of the advance amount may be retained.</div>
                            </div>
                            <div class="timeline-tier-card">
                                <div style="color: #EA580C; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">6 – 12 Hours Before</div>
                                <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">Up to 50% of the advance amount may be retained.</div>
                            </div>
                            <div class="timeline-tier-card">
                                <div style="color: #DC2626; font-weight: 800; font-size: 0.95rem; margin-bottom: 0.25rem;">&lt; 6 Hours Before</div>
                                <div style="font-size: 0.85rem; color: var(--slate-600); line-height: 1.4;">The advance amount may be non-refundable.</div>
                            </div>
                        </div>

                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>Cancellation 24 hours or more before the scheduled pickup time:</strong> No cancellation charge, subject to applicable transaction or processing charges, if any.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>Cancellation 12–24 hours before pickup:</strong> Up to 25% of the advance amount may be retained.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>Cancellation 6–12 hours before pickup:</strong> Up to 50% of the advance amount may be retained.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>Cancellation less than 6 hours before pickup:</strong> The advance amount may be non-refundable.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>No-show:</strong> If the customer does not arrive at the pickup location or cannot be contacted, the booking may be treated as a no-show and the advance amount may be non-refundable.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>If the vehicle has already reached the pickup location, applicable waiting or cancellation charges may be payable.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span><strong>Refunds:</strong> Where applicable, refunds will be processed through the original payment method.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 3 -->
                <div id="rescheduling" class="terms-card">
                    <div class="terms-card-header" data-target="body-rescheduling" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">3</span>
                            <h2 class="terms-card-title">Rescheduling</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-rescheduling">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers should inform us as early as possible if they need to change the pickup date or time.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Rescheduling is subject to vehicle availability.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Last-minute changes may attract additional charges.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 4 -->
                <div id="waiting-pickup" class="terms-card">
                    <div class="terms-card-header" data-target="body-waiting-pickup" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">4</span>
                            <h2 class="terms-card-title">Waiting &amp; Pickup</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-waiting-pickup">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers are requested to provide accurate pickup details and a reachable mobile number.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Reasonable waiting time may be provided at the pickup location.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Additional waiting charges may apply after the complimentary waiting period.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Delays caused by traffic, weather, road conditions, flight/train delays or other circumstances may affect the pickup time.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 5 -->
                <div id="fare-additional-charges" class="terms-card">
                    <div class="terms-card-header" data-target="body-fare-additional-charges" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">5</span>
                            <h2 class="terms-card-title">Fare &amp; Additional Charges</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-fare-additional-charges">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>The fare will be based on the quotation/booking confirmation provided to the customer.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Toll tax, parking charges, state/border taxes, permits and other applicable charges may be extra unless specifically included in the quotation.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>For one-way trips, the fare will be as agreed for the selected route.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>For round trips and tour packages, additional kilometres, extra hours, night halt and other applicable charges will be as per the agreed booking terms.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 6 -->
                <div id="vehicle-driver" class="terms-card">
                    <div class="terms-card-header" data-target="body-vehicle-driver" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">6</span>
                            <h2 class="terms-card-title">Vehicle &amp; Driver</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-vehicle-driver">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Vehicles are maintained to provide a safe and comfortable journey.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>In case of mechanical breakdown or unforeseen issues, alternate arrangements will be made as feasible.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Drivers are instructed to follow traffic rules and drive safely.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers are requested to treat drivers courteously and avoid requesting overspeeding or unsafe driving.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 7 -->
                <div id="customer-responsibilities" class="terms-card">
                    <div class="terms-card-header" data-target="body-customer-responsibilities" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">7</span>
                            <h2 class="terms-card-title">Customer Responsibilities</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-customer-responsibilities">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers must provide correct booking information, including pickup location, destination, date and contact number.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers are responsible for their personal belongings. Vaishnavi Tour's Taxi Service is not responsible for belongings left inside the vehicle.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Smoking and consumption of alcohol inside the vehicle may be prohibited.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Any damage caused to the vehicle due to customer negligence or misuse may be chargeable.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 8 -->
                <div id="driver-payment-communication" class="terms-card">
                    <div class="terms-card-header" data-target="body-driver-payment-communication" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">8</span>
                            <h2 class="terms-card-title">Driver &amp; Payment Communication</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-driver-payment-communication">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Customers should make payments only through the agreed payment method or to authorised representatives of Vaishnavi Tour's Taxi Service.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Drivers are not authorised to offer unauthorised discounts, change agreed fares or make independent booking arrangements.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>For any fare, payment or booking-related concern, customers should contact Vaishnavi Tour's Taxi Service directly.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 9 -->
                <div id="unavoidable-circumstances" class="terms-card">
                    <div class="terms-card-header" data-target="body-unavoidable-circumstances" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">9</span>
                            <h2 class="terms-card-title">Unavoidable Circumstances</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-unavoidable-circumstances">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Vaishnavi Tour's Taxi Service will not be responsible for delays or changes caused by circumstances beyond reasonable control, including severe weather, road closures, traffic restrictions, government restrictions, natural events, accidents or other unforeseen circumstances.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Section 10 -->
                <div id="general-terms" class="terms-card">
                    <div class="terms-card-header" data-target="body-general-terms" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge">10</span>
                            <h2 class="terms-card-title">General Terms</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-general-terms">
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>All bookings are subject to vehicle availability and confirmation.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>By confirming a booking, the customer agrees to these Cancellation Policy and Terms &amp; Conditions.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Any special terms agreed at the time of booking will take precedence over the general terms stated above.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Privacy Policy Section -->
                <div id="privacy-policy" class="terms-card terms-privacy-card">
                    <div class="terms-card-header" data-target="body-privacy-policy" role="button" tabindex="0" aria-expanded="false">
                        <div class="terms-header-left">
                            <span class="terms-num-badge" style="background: var(--dark-900); color: #fff;"><x-icon name="lock" size="14" /></span>
                            <h2 class="terms-card-title">Privacy Policy &amp; Passenger Data Protection</h2>
                        </div>
                        <span class="accordion-toggle-icon" aria-hidden="true">+</span>
                    </div>
                    <div class="terms-card-body" id="body-privacy-policy">
                        <p style="font-size: clamp(0.9rem, 2.5vw, 0.95rem); color: var(--slate-700); line-height: 1.7; margin-bottom: 1rem;">
                            Vaishnavi Tour's Taxi Service respects and values the privacy of every traveler. We collect personal details (such as your full name, telephone number, pickup address, travel itinerary, and email address) exclusively to coordinate your taxi booking, send chauffeur dispatch updates, ensure journey safety, and generate legal GST invoices.
                        </p>
                        <ul class="terms-list">
                            <li>
                                <span class="bullet">•</span>
                                <span>Your contact details are shared only with the assigned vehicle driver for seamless pickup coordination.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>We never sell, rent, lease, or distribute passenger contact information to unauthorized third parties or marketing telemarketers.</span>
                            </li>
                            <li>
                                <span class="bullet">•</span>
                                <span>Payment and transaction records are kept strictly confidential in accordance with Indian financial accounting standards.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Support Card (Requirement 9: Need Help? with official contact details) -->
                <div class="terms-support-card">
                    <div class="terms-support-header">
                        <div class="icon-box icon-box-md icon-box-primary"><x-icon name="phone" size="22" /></div>
                        <div>
                            <h3 class="terms-support-title">Need Help?</h3>
                            <p class="terms-support-subtitle">Our booking dispatch team is ready to assist you 24/7</p>
                        </div>
                    </div>
                    <div class="terms-support-details">
                        <div class="terms-support-item">
                            <span class="terms-support-label">Call Us:</span>
                            <div class="terms-support-actions">
                                <a href="tel:{{ config('vaishnavi.phone_primary_tel') }}" class="btn btn-outline terms-call-btn">
                                    <x-icon name="phone" size="16" style="margin-right: 4px;" /> {{ config('vaishnavi.phone_primary') }}
                                </a>
                                <a href="tel:{{ config('vaishnavi.phone_secondary_tel') }}" class="btn btn-outline terms-call-btn">
                                    <x-icon name="phone" size="16" style="margin-right: 4px;" /> {{ config('vaishnavi.phone_secondary') }}
                                </a>
                            </div>
                        </div>
                        <div class="terms-support-item">
                            <span class="terms-support-label">WhatsApp:</span>
                            <div class="terms-support-actions">
                                <a href="{{ config('vaishnavi.whatsapp_link') }}" target="_blank" class="btn terms-wa-btn">
                                    <x-icon.whatsapp size="18" style="margin-right: 6px;" /> Chat on WhatsApp ({{ config('vaishnavi.whatsapp') }})
                                </a>
                            </div>
                        </div>
                        <div class="terms-support-item">
                            <span class="terms-support-label">Address:</span>
                            <span class="terms-support-address"><x-icon name="map-pin" size="16" class="text-primary" style="margin-right: 4px;" /> {{ config('vaishnavi.address') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact &amp; Queries Callout (Requirement 10: Book a Cab CTA) -->
                <div class="terms-cta-callout">
                    <h3 class="terms-cta-title">Have Questions About Our Booking Terms?</h3>
                    <p class="terms-cta-desc">
                        Our dispatch control team in Bilaspur is available 24/7 to answer questions regarding rates, routes, cancellations, or custom tour itineraries.
                    </p>
                    <div class="terms-cta-buttons">
                        <a href="{{ route('contact') }}" class="btn btn-primary" style="font-weight: 700;">
                            Contact Our Team
                        </a>
                        <a href="{{ route('booking') }}" class="btn btn-outline" style="border-color: #fff; color: #fff; font-weight: 700;">
                            Book a Cab Now <x-icon name="arrow-right" size="16" style="margin-left: 4px;" />
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile TOC Toggle
    var tocBtn = document.getElementById('mobileTocBtn');
    var tocMenu = document.getElementById('mobileTocMenu');
    var tocIndicator = document.getElementById('mobileTocIndicator');

    if (tocBtn && tocMenu) {
        tocBtn.addEventListener('click', function () {
            var isOpen = tocMenu.classList.toggle('show');
            tocBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (tocIndicator) {
                tocIndicator.textContent = isOpen ? '▲' : '▼';
            }
        });
    }

    // 2. Smooth scroll and auto-expand target section from TOC links
    var allTocLinks = document.querySelectorAll('.mobile-toc-item, .desktop-toc-link');
    allTocLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            var targetId = link.getAttribute('href').replace('#', '');
            var targetEl = document.getElementById(targetId);
            if (targetEl) {
                e.preventDefault();
                
                // If on mobile/tablet, ensure target section is open!
                if (window.innerWidth <= 992) {
                    targetEl.classList.add('is-open');
                    var icon = targetEl.querySelector('.accordion-toggle-icon');
                    if (icon) icon.textContent = '−';
                    var headerBtn = targetEl.querySelector('.terms-card-header');
                    if (headerBtn) headerBtn.setAttribute('aria-expanded', 'true');
                    
                    // Close mobile TOC menu
                    if (tocMenu) {
                        tocMenu.classList.remove('show');
                        if (tocIndicator) tocIndicator.textContent = '▼';
                        if (tocBtn) tocBtn.setAttribute('aria-expanded', 'false');
                    }
                }
                
                var headerOffset = 85;
                var elementPosition = targetEl.getBoundingClientRect().top;
                var offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // 3. Mobile Accordion Toggle
    var headers = document.querySelectorAll('.terms-card-header');
    headers.forEach(function (header) {
        var toggleCard = function () {
            if (window.innerWidth > 992) return; // Desktop is full view, not accordion
            var card = header.closest('.terms-card');
            if (!card) return;
            var isCurrentlyOpen = card.classList.contains('is-open');
            var icon = header.querySelector('.accordion-toggle-icon');
            
            if (isCurrentlyOpen) {
                card.classList.remove('is-open');
                header.setAttribute('aria-expanded', 'false');
                if (icon) icon.textContent = '+';
            } else {
                card.classList.add('is-open');
                header.setAttribute('aria-expanded', 'true');
                if (icon) icon.textContent = '−';
            }
        };

        header.addEventListener('click', toggleCard);
        header.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleCard();
            }
        });
    });
});
</script>
@endpush
