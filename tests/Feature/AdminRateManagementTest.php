<?php

namespace Tests\Feature;

use App\Models\RateRoute;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRateManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_route_and_its_vehicle_price(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.rates.store'), [
            'category' => 'outstation',
            'origin' => 'Naya Raipur',
            'destination' => 'Jagdalpur',
            'km_limit' => 300,
            'included_hours' => 6,
            'return_same_rate' => '1',
            'active' => '1',
            'prices' => [
                [
                    'vehicle_category' => 'Sedan',
                    'base_fare' => 7200,
                    'gst_applicable' => '1',
                    'extra_km_rate' => '',
                    'extra_hour_rate' => '',
                    'vehicle_rent' => '',
                    'per_km_rate' => '',
                    'night_charge' => '',
                    'toll_type' => '',
                    'parking_type' => '',
                    'border_tax_type' => '',
                    'driver_food_type' => '',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.rates.index'));
        $this->assertDatabaseHas('rate_routes', [
            'category' => 'outstation',
            'origin' => 'Naya Raipur',
            'destination' => 'Jagdalpur',
            'km_limit' => 300,
            'return_same_rate' => true,
        ]);
        $route = RateRoute::query()->where('origin', 'Naya Raipur')->firstOrFail();
        $this->assertDatabaseHas('rate_vehicle_prices', [
            'rate_route_id' => $route->id,
            'vehicle_category' => 'Sedan',
            'base_fare' => 7200,
            'gst_applicable' => true,
        ]);
    }

    public function test_non_admin_cannot_view_rate_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('admin.rates.index'))
            ->assertForbidden();
    }

    public function test_admin_cannot_save_a_vehicle_rate_without_a_fare_or_complete_kilometer_tariff(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->from(route('admin.rates.create'))->post(route('admin.rates.store'), [
            'category' => 'outstation',
            'origin' => 'Korba',
            'destination' => 'Ambikapur',
            'active' => '1',
            'prices' => [
                ['vehicle_category' => 'Sedan'],
            ],
        ]);

        $response->assertRedirect(route('admin.rates.create'));
        $response->assertSessionHasErrors([
            'prices.0.base_fare' => 'Enter a base fare or both vehicle rent and per-kilometer rate.',
        ]);
        $this->assertDatabaseMissing('rate_routes', [
            'origin' => 'Korba',
            'destination' => 'Ambikapur',
        ]);
    }
}
