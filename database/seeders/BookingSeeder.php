<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Driver;
use App\Models\Feedback;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            return;
        }

        $admin = User::where('role', 'admin')->first();
        $vehicles = Vehicle::all()->keyBy('vehicle_type');
        $drivers = Driver::all()->keyBy('name');

        $c1 = $customers[0]; // Rajesh Sharma
        $c2 = $customers[1]; // Amit Patel
        $c3 = $customers[2]; // Priya Verma
        $c4 = $customers[3]; // Vikramaditya Singh
        $c5 = $customers[4]; // Neha Dewangan

        $dzire = $vehicles->get('Sedan') ?? Vehicle::first();
        $ertiga = $vehicles->get('SUV') ?? $vehicles->get('MUV') ?? Vehicle::skip(1)->first();
        $innova = $vehicles->get('Innova Crysta') ?? Vehicle::skip(2)->first();
        $tempo = $vehicles->get('Tempo Traveller') ?? Vehicle::skip(3)->first();
        $hatch = $vehicles->get('Hatchback') ?? Vehicle::skip(4)->first();

        $d1 = $drivers->get('Rameshwar Sahu') ?? Driver::first();
        $d2 = $drivers->get('Santosh Kumar Yadav') ?? Driver::skip(1)->first();
        $d3 = $drivers->get('Dilip Chandrakar') ?? Driver::skip(2)->first();
        $d4 = $drivers->get('Mahendra Bhagat') ?? Driver::skip(3)->first();

        $bookingsData = [
            // 1. VT-1001: Completed, Paid
            [
                'booking_id' => 'VT-1001',
                'customer_id' => $c1->id,
                'trip_type' => 'Airport Transfer',
                'pickup_location' => 'Mangal Chowk, Bilaspur',
                'destination' => 'Swami Vivekananda Airport, Raipur',
                'travel_date' => date('Y-m-d', strtotime('-3 days')),
                'travel_time' => '05:30:00',
                'return_date' => null,
                'vehicle_id' => $dzire?->id,
                'driver_id' => $d1?->id,
                'total_amount' => 2400.00,
                'paid_amount' => 2400.00,
                'balance_amount' => 0.00,
                'payment_status' => 'Paid',
                'booking_status' => 'Completed',
                'notes' => 'Early morning flight drop at Raipur airport. Punctual pickup requested.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Booking request placed online.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by dispatch desk.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Assigned Maruti Dzire (CG-10-AB-1204).'],
                    ['old' => 'Vehicle Assigned', 'new' => 'Driver Assigned', 'remarks' => 'Assigned Chauffeur Rameshwar Sahu.'],
                    ['old' => 'Driver Assigned', 'new' => 'Trip Started', 'remarks' => 'Vehicle reached doorstep at 05:25 AM.'],
                    ['old' => 'Trip Started', 'new' => 'On The Way', 'remarks' => 'Crossing Simga toll plaza.'],
                    ['old' => 'On The Way', 'new' => 'Completed', 'remarks' => 'Safely dropped at Raipur Airport Departure Gate.'],
                ],
                'trip' => [
                    'start_odometer' => 45200,
                    'end_odometer' => 45332,
                    'status' => 'Completed',
                    'route_notes' => 'On-time arrival at departure terminal.',
                ],
                'payment' => [
                    'amount' => 2400.00,
                    'method' => 'UPI / QR',
                    'txnid' => 'UPI20260918112049',
                ],
            ],

            // 2. VT-1002: Completed, Paid
            [
                'booking_id' => 'VT-1002',
                'customer_id' => $c2->id,
                'trip_type' => 'One-Way',
                'pickup_location' => 'Mangal Chowk, Bilaspur',
                'destination' => 'Balco Nagar, Korba',
                'travel_date' => date('Y-m-d', strtotime('-2 days')),
                'travel_time' => '08:00:00',
                'return_date' => null,
                'vehicle_id' => $ertiga?->id,
                'driver_id' => $d2?->id,
                'total_amount' => 2600.00,
                'paid_amount' => 2600.00,
                'balance_amount' => 0.00,
                'payment_status' => 'Paid',
                'booking_status' => 'Completed',
                'notes' => 'Corporate meeting travel to Balco plant.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Booking request submitted.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Dispatch desk confirmed trip.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Assigned Maruti Ertiga.'],
                    ['old' => 'Vehicle Assigned', 'new' => 'Driver Assigned', 'remarks' => 'Assigned Chauffeur Santosh Kumar Yadav.'],
                    ['old' => 'Driver Assigned', 'new' => 'Trip Started', 'remarks' => 'Trip started from Mangal Chowk.'],
                    ['old' => 'Trip Started', 'new' => 'On The Way', 'remarks' => 'Approaching Champa highway.'],
                    ['old' => 'On The Way', 'new' => 'Completed', 'remarks' => 'Reached Balco Nagar safely.'],
                ],
                'trip' => [
                    'start_odometer' => 32100,
                    'end_odometer' => 32210,
                    'status' => 'Completed',
                    'route_notes' => 'Corporate transit completed without delays.',
                ],
                'payment' => [
                    'amount' => 2600.00,
                    'method' => 'Cash',
                    'txnid' => 'CSH-2026091901',
                ],
            ],

            // 3. VT-1003: On The Way, Partial Payment
            [
                'booking_id' => 'VT-1003',
                'customer_id' => $c3->id,
                'trip_type' => 'One-Way',
                'pickup_location' => 'Vyas Nagar, Bilaspur',
                'destination' => 'Pandri, Raipur',
                'travel_date' => date('Y-m-d'),
                'travel_time' => '07:30:00',
                'return_date' => null,
                'vehicle_id' => $dzire?->id,
                'driver_id' => $d1?->id,
                'total_amount' => 2200.00,
                'paid_amount' => 1000.00,
                'balance_amount' => 1200.00,
                'payment_status' => 'Partial',
                'booking_status' => 'On The Way',
                'notes' => 'Need clean sanitized car with AC running.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Booking placed by customer.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by dispatch operator.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Assigned Maruti Dzire.'],
                    ['old' => 'Vehicle Assigned', 'new' => 'Driver Assigned', 'remarks' => 'Assigned Rameshwar Sahu.'],
                    ['old' => 'Driver Assigned', 'new' => 'Trip Started', 'remarks' => 'Passenger boarded at Vyas Nagar.'],
                    ['old' => 'Trip Started', 'new' => 'On The Way', 'remarks' => 'Cruising on NH-130 towards Raipur.'],
                ],
                'trip' => [
                    'start_odometer' => 45400,
                    'end_odometer' => null,
                    'status' => 'On The Way',
                    'route_notes' => 'Crossed Nandghat toll plaza.',
                ],
                'payment' => [
                    'amount' => 1000.00,
                    'method' => 'UPI / QR',
                    'txnid' => 'UPI20260922071501',
                ],
            ],

            // 4. VT-1004: Trip Started, Partial Payment
            [
                'booking_id' => 'VT-1004',
                'customer_id' => $c4->id,
                'trip_type' => 'Local Hourly',
                'pickup_location' => 'Bungalow 7, VIP Estate, Raipur',
                'destination' => 'Naya Raipur & Central City Local',
                'travel_date' => date('Y-m-d'),
                'travel_time' => '09:00:00',
                'return_date' => null,
                'vehicle_id' => $innova?->id,
                'driver_id' => $d3?->id,
                'total_amount' => 3200.00,
                'paid_amount' => 1500.00,
                'balance_amount' => 1700.00,
                'payment_status' => 'Partial',
                'booking_status' => 'Trip Started',
                'notes' => '8hr / 80km local package for delegates.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Hourly booking requested.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'VIP vehicle reserved.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Assigned Toyota Innova Crysta.'],
                    ['old' => 'Vehicle Assigned', 'new' => 'Driver Assigned', 'remarks' => 'Assigned Chauffeur Dilip Chandrakar.'],
                    ['old' => 'Driver Assigned', 'new' => 'Trip Started', 'remarks' => 'Chauffeur reported at VIP Estate.'],
                ],
                'trip' => [
                    'start_odometer' => 18400,
                    'end_odometer' => null,
                    'status' => 'Trip Started',
                    'route_notes' => 'VIP local rental duty commenced.',
                ],
                'payment' => [
                    'amount' => 1500.00,
                    'method' => 'Card',
                    'txnid' => 'CARD20260922090510',
                ],
            ],

            // 5. VT-1005: Driver Assigned, Trip Activated
            [
                'booking_id' => 'VT-1005',
                'customer_id' => $c5->id,
                'trip_type' => 'Airport Transfer',
                'pickup_location' => 'Sector 4, Balco Township, Korba',
                'destination' => 'Swami Vivekananda Airport, Raipur',
                'travel_date' => date('Y-m-d', strtotime('+1 day')),
                'travel_time' => '04:00:00',
                'return_date' => null,
                'vehicle_id' => $ertiga?->id,
                'driver_id' => $d2?->id,
                'total_amount' => 4500.00,
                'paid_amount' => 1000.00,
                'balance_amount' => 3500.00,
                'payment_status' => 'Partial',
                'booking_status' => 'Driver Assigned',
                'notes' => 'Long distance airport drop. Flight departure at 11:30 AM.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Booking received via public portal.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by dispatch.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Allocated Maruti Ertiga.'],
                    ['old' => 'Vehicle Assigned', 'new' => 'Driver Assigned', 'remarks' => 'Allocated Chauffeur Santosh Kumar Yadav. Trip activated.'],
                ],
                'trip' => [
                    'start_odometer' => null,
                    'end_odometer' => null,
                    'status' => 'Driver Assigned',
                    'route_notes' => 'Scheduled early morning departure from Korba.',
                ],
                'payment' => [
                    'amount' => 1000.00,
                    'method' => 'UPI / QR',
                    'txnid' => 'UPI20260921190001',
                ],
            ],

            // 6. VT-1006: Vehicle Assigned, Awaiting Chauffeur
            [
                'booking_id' => 'VT-1006',
                'customer_id' => $c1->id,
                'trip_type' => 'Round-Trip',
                'pickup_location' => 'Mangal Chowk, Bilaspur',
                'destination' => 'Maa Mahamaya Mandir, Ratanpur',
                'travel_date' => date('Y-m-d', strtotime('+2 days')),
                'travel_time' => '08:30:00',
                'return_date' => date('Y-m-d', strtotime('+2 days')),
                'vehicle_id' => $dzire?->id,
                'driver_id' => null,
                'total_amount' => 1800.00,
                'paid_amount' => 0.00,
                'balance_amount' => 1800.00,
                'payment_status' => 'Pending',
                'booking_status' => 'Vehicle Assigned',
                'notes' => 'Family temple visit. 2 hours darshan halt included.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Online reservation received.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by dispatch.'],
                    ['old' => 'Confirmed', 'new' => 'Vehicle Assigned', 'remarks' => 'Vehicle allocated: Maruti Dzire (CG-10-AB-1204).'],
                ],
                'trip' => null,
                'payment' => null,
            ],

            // 7. VT-1007: Confirmed, Pending Vehicle Allocation
            [
                'booking_id' => 'VT-1007',
                'customer_id' => $c2->id,
                'trip_type' => 'One-Way',
                'pickup_location' => 'Mangal Chowk, Bilaspur',
                'destination' => 'Railway Station, Durg',
                'travel_date' => date('Y-m-d', strtotime('+3 days')),
                'travel_time' => '11:00:00',
                'return_date' => null,
                'vehicle_id' => null,
                'driver_id' => null,
                'total_amount' => 2800.00,
                'paid_amount' => 0.00,
                'balance_amount' => 2800.00,
                'payment_status' => 'Pending',
                'booking_status' => 'Confirmed',
                'notes' => 'Train connectivity trip to Durg Junction.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Customer submitted booking.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by dispatch desk.'],
                ],
                'trip' => null,
                'payment' => null,
            ],

            // 8. VT-1008: Cancelled, Reason & Audit Recorded
            [
                'booking_id' => 'VT-1008',
                'customer_id' => $c3->id,
                'trip_type' => 'One-Way',
                'pickup_location' => 'Vyas Nagar, Bilaspur',
                'destination' => 'Sirpur Historical Site',
                'travel_date' => date('Y-m-d', strtotime('-1 day')),
                'travel_time' => '07:00:00',
                'return_date' => null,
                'vehicle_id' => $tempo?->id,
                'driver_id' => $d4?->id,
                'total_amount' => 4800.00,
                'paid_amount' => 0.00,
                'balance_amount' => 4800.00,
                'payment_status' => 'Pending',
                'booking_status' => 'Cancelled',
                'cancellation_status' => 'Approved',
                'cancellation_reason' => 'Family group tour postponed due to heavy rainfall.',
                'cancelled_at' => now()->subDay(),
                'cancelled_by' => $admin?->id,
                'notes' => '14 passenger sightseeing trip.',
                'timeline' => [
                    ['old' => null, 'new' => 'Pending', 'remarks' => 'Sightseeing tour request created.'],
                    ['old' => 'Pending', 'new' => 'Confirmed', 'remarks' => 'Confirmed by administrator.'],
                    ['old' => 'Confirmed', 'new' => 'Cancelled', 'remarks' => 'Customer requested cancellation due to heavy rainfall; approved by admin.'],
                ],
                'trip' => null,
                'payment' => null,
            ],
        ];

        foreach ($bookingsData as $bData) {
            $booking = Booking::updateOrCreate(
                ['booking_id' => $bData['booking_id']],
                [
                    'customer_id' => $bData['customer_id'],
                    'trip_type' => $bData['trip_type'],
                    'pickup_location' => $bData['pickup_location'],
                    'destination' => $bData['destination'],
                    'travel_date' => $bData['travel_date'],
                    'travel_time' => $bData['travel_time'],
                    'return_date' => $bData['return_date'],
                    'vehicle_id' => $bData['vehicle_id'],
                    'driver_id' => $bData['driver_id'],
                    'total_amount' => $bData['total_amount'],
                    'paid_amount' => $bData['paid_amount'],
                    'balance_amount' => $bData['balance_amount'],
                    'payment_status' => $bData['payment_status'],
                    'booking_status' => $bData['booking_status'],
                    'cancellation_status' => $bData['cancellation_status'] ?? null,
                    'cancellation_reason' => $bData['cancellation_reason'] ?? null,
                    'cancelled_at' => $bData['cancelled_at'] ?? null,
                    'cancelled_by' => $bData['cancelled_by'] ?? null,
                    'notes' => $bData['notes'],
                ]
            );

            // Audit history
            foreach ($bData['timeline'] as $hist) {
                BookingStatusHistory::firstOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'new_status' => $hist['new'],
                        'old_status' => $hist['old'],
                    ],
                    [
                        'changed_by' => $admin?->id,
                        'remarks' => $hist['remarks'],
                        'comment' => $hist['remarks'],
                        'created_at' => now(),
                    ]
                );
            }

            // Trip creation if applicable
            if (!empty($bData['trip'])) {
                Trip::updateOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'vehicle_id' => $booking->vehicle_id,
                        'driver_id' => $booking->driver_id,
                        'start_odometer' => $bData['trip']['start_odometer'],
                        'end_odometer' => $bData['trip']['end_odometer'],
                        'status' => $bData['trip']['status'],
                        'trip_status' => $bData['trip']['status'],
                        'route_notes' => $bData['trip']['route_notes'],
                        'notes' => $bData['trip']['route_notes'],
                    ]
                );
            }

            // Payment record if applicable
            if (!empty($bData['payment'])) {
                Payment::updateOrCreate(
                    ['booking_id' => $booking->id, 'transaction_id' => $bData['payment']['txnid']],
                    [
                        'customer_id' => $booking->customer_id,
                        'amount' => $bData['payment']['amount'],
                        'payment_method' => $bData['payment']['method'],
                        'status' => 'Success',
                        'paid_at' => now()->subDay(),
                    ]
                );
            }

            // Always create/sync Invoice
            $status = $booking->paid_amount >= $booking->total_amount && $booking->total_amount > 0 ? 'Paid' : ($booking->paid_amount > 0 ? 'Partially Paid' : 'Unpaid');
            Invoice::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'customer_id' => $booking->customer_id,
                    'total_amount' => $booking->total_amount,
                    'paid_amount' => $booking->paid_amount,
                    'balance_amount' => $booking->balance_amount,
                    'status' => $status,
                    'issued_at' => now(),
                ]
            );

            // Customer notification
            Notification::firstOrCreate(
                [
                    'user_id' => $booking->customer_id,
                    'title' => "Booking #{$booking->booking_id} {$booking->booking_status}",
                ],
                [
                    'message' => "Your booking {$booking->booking_id} status is currently '{$booking->booking_status}'.",
                    'type' => 'trip',
                    'action_url' => '/customer/bookings/' . $booking->id,
                ]
            );
        }

        // Broadcast admin notifications
        Notification::firstOrCreate(
            ['title' => 'New booking VT-1006 received.'],
            [
                'user_id' => null,
                'message' => 'New booking VT-1006 received from Rajesh Sharma for Bilaspur to Ratanpur.',
                'type' => 'booking',
                'action_url' => '/admin/bookings',
                'is_read' => false,
            ]
        );

        Notification::firstOrCreate(
            ['title' => 'Payment received for VT-1003.'],
            [
                'user_id' => null,
                'message' => 'Payment received for VT-1003. Amount: ₹1,000.00 via UPI.',
                'type' => 'payment',
                'action_url' => '/admin/bookings',
                'is_read' => false,
            ]
        );
    }
}
