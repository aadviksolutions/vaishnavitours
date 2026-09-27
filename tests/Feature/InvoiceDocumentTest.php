<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_invoice_uses_booking_details_and_successful_payment_records(): void
    {
        [$customer, $booking, $invoice] = $this->createInvoiceRecords();
        Setting::create(['key' => 'bank_account_name', 'value' => 'Configured Account']);
        Setting::create(['key' => 'bank_name', 'value' => 'Configured Bank']);
        Setting::create(['key' => 'bank_account_number', 'value' => 'ACCOUNT-TEST']);
        Setting::create(['key' => 'bank_ifsc_code', 'value' => 'IFSC-TEST']);
        Setting::create(['key' => 'booking_terms', 'value' => 'Configured booking terms']);

        $response = $this->actingAs($customer)->get(route('invoice.show', $booking));

        $response->assertOk();
        $response->assertSee('TAX INVOICE');
        $response->assertSee('ORIGINAL FOR RECIPIENT');
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Invoice Date:');
        $response->assertSee('Sample Customer');
        $response->assertSee('10 Sample Street, Raipur, Chhattisgarh, 492001');
        $response->assertSee('9820000000');
        $response->assertSee('Sample Driver');
        $response->assertSee('CG-04-AB-1234');
        $response->assertSee('Raipur to Bilaspur Oneway Taxi Service with Sedan');
        $response->assertSee('₹350.00');
        $response->assertSee('₹1,960.00');
        $response->assertSee('Two Thousand Three Hundred Ten Rupees Only');
        $response->assertSee('Configured Account');
        $response->assertSee('Configured Bank');
        $response->assertSee('Configured booking terms');
        $response->assertSee('Print Invoice');
        $response->assertSee('Download PDF');
        $response->assertSee('@page { size: A4 portrait; margin: 7mm; }', false);
        $response->assertDontSee('GST @ 5%');
        $response->assertDontSee('null');

        $customerInvoiceResponse = $this->get(route('customer.invoices.show', $invoice));

        $customerInvoiceResponse->assertOk();
        $customerInvoiceResponse->assertSee('ORIGINAL FOR RECIPIENT');
    }

    public function test_invoice_does_not_estimate_gst_without_saved_tax_amounts(): void
    {
        [$customer, $booking] = $this->createInvoiceRecords();
        $booking->update(['pricing_details' => ['gst_applicable' => true]]);

        $response = $this->actingAs($customer)->get(route('invoice.show', $booking));

        $response->assertOk();
        $response->assertSee('GST is marked applicable for this booking');
        $response->assertSee('Not recorded');
        $response->assertSee('₹2,310.00');
        $response->assertDontSee('GST @ 5%');
    }

    public function test_invoice_uses_saved_cgst_and_sgst_amounts(): void
    {
        [$customer, $booking] = $this->createInvoiceRecords();
        $booking->update(['pricing_details' => [
            'gst_applicable' => true,
            'cgst_amount' => 58.00,
            'sgst_amount' => 58.00,
        ]]);

        $response = $this->actingAs($customer)->get(route('invoice.show', $booking));

        $response->assertOk();
        $response->assertSee('₹2,194.00');
        $response->assertSee('₹58.00');
        $response->assertDontSee('GST is marked applicable for this booking');
    }

    public function test_invoice_balance_is_total_less_received_even_when_overpaid(): void
    {
        [$customer, $booking] = $this->createInvoiceRecords();
        Payment::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'amount' => 2000.00,
            'status' => 'Success',
        ]);

        $response = $this->actingAs($customer)->get(route('invoice.show', $booking));

        $response->assertOk();
        $response->assertSee('₹-40.00');
    }

    public function test_admin_invoice_uses_the_same_standalone_printable_document(): void
    {
        [$customer, , $invoice] = $this->createInvoiceRecords();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.invoices.show', $invoice));

        $response->assertOk();
        $response->assertSee('TAX INVOICE');
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Print Invoice');
        $response->assertDontSee('admin-sidebar');
        $this->assertNotSame($customer->id, $admin->id);
    }

    public function test_other_customer_cannot_view_a_booking_invoice(): void
    {
        [, $booking] = $this->createInvoiceRecords();
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($otherCustomer)
            ->get(route('invoice.show', $booking))
            ->assertForbidden();
    }

    public function test_admin_can_configure_invoice_bank_details_in_company_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'bank_account_name' => 'Vaishnavi Tours',
            'bank_name' => 'Configured Bank',
            'bank_account_number' => '1234567890',
            'bank_ifsc_code' => 'BANK0001234',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'bank_account_name',
            'value' => 'Vaishnavi Tours',
        ]);
        $this->assertDatabaseHas('settings', [
            'key' => 'bank_ifsc_code',
            'value' => 'BANK0001234',
        ]);
    }

    /**
     * @return array{0: User, 1: Booking, 2: Invoice}
     */
    private function createInvoiceRecords(): array
    {
        $customer = User::factory()->create([
            'name' => 'Sample Customer',
            'phone' => '9820000000',
            'role' => 'customer',
        ]);

        Customer::create([
            'user_id' => $customer->id,
            'address' => '10 Sample Street',
            'city' => 'Raipur',
            'state' => 'Chhattisgarh',
            'pincode' => '492001',
        ]);

        $vehicle = Vehicle::create([
            'name' => 'Sedan',
            'registration_number' => 'CG-04-AB-1234',
            'vehicle_type' => 'Sedan',
            'seating_capacity' => 4,
            'ac_non_ac' => 'AC',
            'status' => 'Available',
        ]);

        $driver = Driver::create([
            'name' => 'Sample Driver',
            'mobile' => '9810000000',
            'license_number' => 'SAMPLE-LICENSE-1',
        ]);

        $booking = Booking::create([
            'booking_id' => 'VT-TEST-1001',
            'customer_id' => $customer->id,
            'trip_type' => 'Oneway',
            'pickup_location' => 'Raipur',
            'destination' => 'Bilaspur',
            'travel_date' => '2026-09-18',
            'travel_time' => '10:00',
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'total_amount' => 2310.00,
            'paid_amount' => 1234.00,
            'payment_status' => 'Partial',
            'booking_status' => 'Completed',
            'pricing_details' => ['gst_applicable' => false],
            'notes' => 'Collect the passenger at the main entrance.',
        ]);

        $invoice = Invoice::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'total_amount' => 2310.00,
            'paid_amount' => 1234.00,
            'balance_amount' => 1076.00,
            'status' => 'Partially Paid',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'amount' => 250.00,
            'status' => 'Success',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'amount' => 100.00,
            'status' => 'Success',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'customer_id' => $customer->id,
            'amount' => 900.00,
            'status' => 'Pending',
        ]);

        return [$customer, $booking, $invoice];
    }
}
