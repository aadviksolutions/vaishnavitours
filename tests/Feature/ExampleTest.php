<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test home page returns 200 and renders all core elements.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('vaishnavi-tours-logo.png');
        $response->assertSee('hero-taxi.jpg');
        $response->assertSee('emergency-ambulance.jpg');
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
        $response->assertSee('Maruti Suzuki Dzire');
        $response->assertSee('Toyota Innova Crysta');
    }
}