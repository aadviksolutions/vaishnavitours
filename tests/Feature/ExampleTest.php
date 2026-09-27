<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test home page returns 200 and renders all core elements.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('vaishnavi-tours-logo.png');
        $response->assertSee('hero-taxi.jpg');
        $response->assertSee('24/7 Ambulance Services');
        $response->assertSee('View Ambulance Services');
        $response->assertSee(route('ambulance-services'));
        $response->assertSee('about-travel.jpg');
        $response->assertSee('Book Your Taxi');
        $response->assertSee('Maruti Suzuki Dzire');
        $response->assertSee('Toyota Innova Crysta');
        $response->assertSee('Force Tempo Traveller');
        $response->assertSee('css/style.css');
    }

    /**
     * Test vehicles page returns 200 and renders fleet.
     */
    public function test_vehicles_page_renders_fleet(): void
    {
        $response = $this->get('/vehicles');

        $response->assertStatus(200);
        $response->assertSee('Maruti Suzuki Dzire');
        $response->assertSee('Toyota Innova Crysta');
    }

    /**
     * Test booking page returns 200 and contains vehicle options.
     */
    public function test_booking_page_renders_options(): void
    {
        $response = $this->get('/booking');

        $response->assertStatus(200);
        $response->assertSee('name="vehicle_category"', false);
        $response->assertSee('Sedan');
        $response->assertSee('Innova Crysta');
    }
}
