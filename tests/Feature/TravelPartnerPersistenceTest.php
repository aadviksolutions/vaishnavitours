<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\RateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelPartnerPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_partner_details_are_permanently_saved_when_creating_tp_booking(): void
    {
        $this->seed(RateSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $passenger = User::factory()->create(['role' => 'customer', 'name' => 'John Doe', 'phone' => '9111122222']);

        $response = $this->actingAs($admin)->post(route('admin.bookings.store'), [
            'booking_category' => 'TP',
            'customer_id' => $passenger->id,
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'trip_type' => 'Airport Transfer',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raigarh',
            'travel_date' => now()->addDay()->format('Y-m-d'),
            'travel_time' => '10:00',
            'total_amount' => 3000,
            'paid_amount' => 500,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Partial',
            'travel_partner_name' => 'Sunrise Holiday Travels',
            'travel_partner_phone' => '9876543210',
            'travel_partner_email' => 'partner@sunrisetravels.com',
            'travel_partner_address' => 'Shop 12, Main Road, Bilaspur',
            'travel_partner_commission' => 750.00,
            'travel_partner_notes' => 'Payout after trip completion',
        ]);

        $booking = Booking::latest()->firstOrFail();
        $response->assertRedirect(route('admin.bookings.show', $booking));

        // 1. Booking record should contain travel partner referral details
        $this->assertSame('TP', $booking->booking_category);
        $this->assertSame('Sunrise Holiday Travels', $booking->travel_partner_name);
        $this->assertSame('9876543210', $booking->travel_partner_phone);
        $this->assertSame('partner@sunrisetravels.com', $booking->travel_partner_email);
        $this->assertSame(750.0, (float) $booking->travel_partner_commission);

        // 2. Travel Partner must be permanently stored in customers table as travel_partner
        $savedPartner = Customer::where('customer_type', 'travel_partner')
            ->where('partner_name', 'Sunrise Holiday Travels')
            ->first();

        $this->assertNotNull($savedPartner);
        $this->assertSame('750', (string) $savedPartner->partner_commission);
        $this->assertSame('Shop 12, Main Road, Bilaspur', $savedPartner->address);
        $this->assertSame('Payout after trip completion', $savedPartner->notes);

        // 3. User linked to partner should exist with phone and email
        $partnerUser = $savedPartner->user;
        $this->assertNotNull($partnerUser);
        $this->assertSame('9876543210', $partnerUser->phone);
        $this->assertSame('partner@sunrisetravels.com', $partnerUser->email);

        // 4. When opening create booking page, partner should be in $travelPartners list
        $createResponse = $this->actingAs($admin)->get(route('admin.bookings.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('Sunrise Holiday Travels');
        $createResponse->assertSee('9876543210');
        $createResponse->assertSee('Saved Travel Partners');
    }

    public function test_travel_partner_details_can_be_updated_from_booking_edit(): void
    {
        $this->seed(RateSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);
        $passenger = User::factory()->create(['role' => 'customer', 'name' => 'Jane Doe', 'phone' => '9333344444']);

        $booking = Booking::create([
            'booking_id' => 'TP-'.rand(1000, 9999),
            'booking_category' => 'TP',
            'customer_id' => $passenger->id,
            'rate_category' => 'outstation',
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '11:00',
            'total_amount' => 2500,
            'paid_amount' => 500,
            'balance_amount' => 2000,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Partial',
            'travel_partner_name' => 'Initial Partner Agency',
            'travel_partner_phone' => '9988776655',
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.bookings.update', $booking), [
            'customer_id' => $passenger->id,
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => now()->addDays(2)->format('Y-m-d'),
            'travel_time' => '11:00',
            'total_amount' => 2500,
            'paid_amount' => 2500,
            'payment_status' => 'Paid',
            'booking_status' => 'Confirmed',
            'travel_partner_name' => 'Initial Partner Agency Updated',
            'travel_partner_phone' => '9988776655',
            'travel_partner_email' => 'updated@partner.com',
            'travel_partner_address' => 'Updated Office Address',
            'travel_partner_commission' => 600.00,
            'travel_partner_notes' => 'Updated terms agreement',
        ]);

        $updateResponse->assertRedirect(route('admin.bookings.show', $booking));

        $savedPartner = Customer::where('customer_type', 'travel_partner')
            ->where('partner_name', 'Initial Partner Agency Updated')
            ->first();

        $this->assertNotNull($savedPartner);
        $this->assertSame('600', (string) $savedPartner->partner_commission);
        $this->assertSame('Updated Office Address', $savedPartner->address);
    }

    public function test_creating_booking_with_travel_partner_mode_automatically_sets_partner_as_customer(): void
    {
        $this->seed(RateSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        // Travel Partner booking where customer details are NOT provided by partner
        // and Travel Partner itself is chosen as the customer (default behavior)
        $response = $this->actingAs($admin)->post(route('admin.bookings.store'), [
            'booking_category' => 'TP',
            'customer_mode' => 'partner',
            'rate_category' => 'outstation',
            'vehicle_category' => 'Sedan',
            'trip_type' => 'One-Way',
            'pickup_location' => 'Bilaspur',
            'destination' => 'Raipur',
            'travel_date' => now()->addDay()->format('Y-m-d'),
            'travel_time' => '09:00',
            'total_amount' => 2200,
            'paid_amount' => 0,
            'booking_status' => 'Confirmed',
            'payment_status' => 'Pending',
            'travel_partner_name' => 'Goibibo Partner Hub',
            'travel_partner_phone' => '9826099887',
            'travel_partner_email' => 'partner@goibibo.com',
            'travel_partner_address' => 'Raipur Airport Desk',
            'travel_partner_commission' => 350.00,
            'partner_passenger_name' => 'Mr. Alok Verma & family',
        ]);

        $booking = Booking::latest()->firstOrFail();
        $response->assertRedirect(route('admin.bookings.show', $booking));

        // Booking customer should be the travel partner
        $this->assertSame('TP', $booking->booking_category);
        $this->assertSame('Goibibo Partner Hub', $booking->travel_partner_name);
        $this->assertSame('9826099887', $booking->travel_partner_phone);
        $this->assertSame('Goibibo Partner Hub', $booking->customer->name);
        $this->assertSame('9826099887', $booking->customer->phone);

        // Guest name should be saved in booking notes
        $this->assertStringContainsString('Mr. Alok Verma & family', (string) $booking->notes);

        // Partner record should exist in customers table
        $partnerCustomer = Customer::where('customer_type', 'travel_partner')
            ->where('partner_name', 'Goibibo Partner Hub')
            ->first();

        $this->assertNotNull($partnerCustomer);
        $this->assertSame('350', (string) $partnerCustomer->partner_commission);
    }
}
