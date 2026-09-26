<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Database\Seeders\RateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_booking_uses_configured_fare_instead_of_submitted_amount(): void
    {
        $this->seed(RateSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($admin)->post(route('admin.bookings.store'), [
            'customer_id' => $customer->id,
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'trip_type' => 'Airport Transfer',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raigarh',
            'travel_date' => now()->addDay()->format('Y-m-d'),
            'travel_time' => '09:00',
            'total_amount' => 1,
            'paid_amount' => 0,
            'booking_status' => 'Pending',
            'payment_status' => 'Pending',
        ]);

        $booking = Booking::query()->where('customer_id', $customer->id)->firstOrFail();
        $response->assertRedirect(route('admin.bookings.show', $booking));
        $this->assertSame('One-Way', $booking->trip_type);
        $this->assertSame('3000.00', $booking->total_amount);
        $this->assertSame('outstation', $booking->rate_category);
    }
}
