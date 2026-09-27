@php($compact = $compact ?? false)

@pushOnce('styles', 'ambulance-service-card-styles')
<style>
    .ambulance-kicker {
        display: inline-block;
        margin-bottom: 0.45rem;
        color: var(--primary-hover);
        font-size: 0.82rem;
        font-weight: 800;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .ambulance-home-preview {
        padding: 3.5rem 0;
        border-top: 3px solid var(--primary);
        background: var(--dark-900);
    }
    .ambulance-home-heading {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
    }
    .ambulance-home-heading h2 {
        margin-bottom: 0.55rem;
        color: var(--white);
        font-size: 1.9rem;
    }
    .ambulance-home-heading p {
        max-width: 690px;
        color: var(--slate-300);
        line-height: 1.7;
    }
    .ambulance-card-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }
    .ambulance-service-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        padding: 1.5rem;
        border: 1px solid var(--slate-200);
        border-top: 3px solid var(--primary);
        border-radius: var(--radius-md);
        background: var(--white);
        box-shadow: var(--shadow-md);
    }
    .ambulance-service-icon {
        display: grid;
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        place-items: center;
        margin-bottom: 1rem;
        border-radius: var(--radius-md);
        background: var(--primary-light);
        color: var(--dark-900);
    }
    .ambulance-service-card h3 {
        margin-bottom: 0.65rem;
        color: var(--dark-900);
        font-size: 1.15rem;
        overflow-wrap: anywhere;
    }
    .ambulance-service-card-description {
        margin-bottom: 1rem;
        color: var(--slate-600);
        font-size: 0.9rem;
        line-height: 1.65;
    }
    .ambulance-feature-list {
        display: grid;
        gap: 0.55rem;
        margin-top: auto;
        color: var(--slate-700);
        font-size: 0.85rem;
    }
    .ambulance-feature-list li {
        display: flex;
        align-items: flex-start;
        gap: 0.45rem;
        line-height: 1.45;
    }
    .ambulance-feature-list svg {
        flex: 0 0 16px;
        margin-top: 1px;
        color: var(--primary-dark);
    }
    .ambulance-card-grid-compact .ambulance-service-card {
        padding: 1.15rem;
        box-shadow: var(--shadow-sm);
    }
    .ambulance-card-grid-compact .ambulance-service-icon {
        width: 40px;
        height: 40px;
        flex-basis: 40px;
        margin-bottom: 0.75rem;
    }
    .ambulance-card-grid-compact .ambulance-service-card h3 {
        font-size: 1rem;
    }
    .ambulance-card-grid-compact .ambulance-service-card-description {
        margin-bottom: 0;
        font-size: 0.84rem;
    }
    .ambulance-page-hero {
        padding: 4rem 0 3.5rem;
        background: var(--dark-900);
        color: var(--white);
        text-align: center;
    }
    .ambulance-page-hero h1 {
        margin: 0.35rem 0 0.6rem;
        color: var(--white);
        font-size: 2.5rem;
    }
    .ambulance-page-hero p {
        max-width: 760px;
        margin: 0 auto;
        color: var(--slate-300);
        font-size: 1.1rem;
        line-height: 1.65;
    }
    .ambulance-page-intro,
    .ambulance-page-services,
    .ambulance-page-why {
        padding: 3.5rem 0;
    }
    .ambulance-page-intro {
        background: var(--white);
    }
    .ambulance-page-intro p {
        max-width: 860px;
        margin: 0 auto;
        color: var(--slate-700);
        font-size: 1.05rem;
        line-height: 1.8;
        text-align: center;
    }
    .ambulance-page-services {
        background: var(--slate-50);
    }
    .ambulance-page-heading {
        margin-bottom: 1.5rem;
        text-align: center;
    }
    .ambulance-page-heading h2 {
        color: var(--dark-900);
        font-size: 1.9rem;
    }
    .ambulance-page-why {
        background: var(--white);
    }
    .ambulance-reason {
        display: flex;
        min-width: 0;
        align-items: center;
        gap: 0.8rem;
        padding: 1.1rem;
        border: 1px solid var(--slate-200);
        border-radius: var(--radius-md);
        background: var(--white);
        color: var(--dark-800);
        font-weight: 700;
        overflow-wrap: anywhere;
    }
    .ambulance-reason svg {
        flex: 0 0 22px;
        color: var(--primary-dark);
    }
    .ambulance-contact-cta {
        padding: 3.25rem 0;
        border-top: 3px solid var(--primary);
        background: var(--dark-900);
        color: var(--white);
        text-align: center;
    }
    .ambulance-contact-cta h2 {
        margin-bottom: 0.65rem;
        color: var(--white);
        font-size: 1.9rem;
    }
    .ambulance-contact-cta p {
        margin-bottom: 1.4rem;
        color: var(--slate-300);
    }
    .ambulance-cta-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
    }
    @media (max-width: 1024px) {
        .ambulance-card-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 640px) {
        .ambulance-card-grid {
            grid-template-columns: minmax(0, 1fr);
        }
        .ambulance-home-heading {
            align-items: flex-start;
            flex-direction: column;
        }
        .ambulance-home-heading h2,
        .ambulance-page-hero h1 {
            font-size: 1.85rem;
        }
        .ambulance-page-hero {
            padding: 3rem 0 2.75rem;
        }
        .ambulance-page-intro,
        .ambulance-page-services,
        .ambulance-page-why {
            padding: 2.75rem 0;
        }
        .ambulance-cta-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endPushOnce

<div class="ambulance-card-grid {{ $compact ? 'ambulance-card-grid-compact' : '' }}">
    @foreach (config('vaishnavi.ambulance_services', []) as $service)
        <article class="ambulance-service-card">
            <div class="ambulance-service-icon">
                <x-icon :name="$service['icon']" size="24" />
            </div>
            <h3>{{ $service['title'] }}</h3>
            <p class="ambulance-service-card-description">{{ $service['description'] }}</p>
            @if (! $compact)
                <ul class="ambulance-feature-list">
                    @foreach ($service['features'] as $feature)
                        <li><x-icon name="circle-check" size="16" /><span>{{ $feature }}</span></li>
                    @endforeach
                </ul>
            @endif
        </article>
    @endforeach
</div>