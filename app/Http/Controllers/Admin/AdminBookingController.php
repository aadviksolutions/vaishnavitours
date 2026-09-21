<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['customer.customer', 'vehicle', 'driver', 'payments', 'trip']);

        if ($request->filled('status')) {
            $query->where('booking_status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('trip_type')) {
            $query->where('trip_type', $request->trip_type);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('booking_id', 'like', "%{$s}%")
                  ->orWhere('pickup_location', 'like', "%{$s}%")
                  ->orWhere('destination', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        $bookings = $query->latest()->paginate(15);
        
        // Pass only Available vehicles and drivers for assignments
        $availableVehicles = Vehicle::where('status', 'Available')->get();
        $availableDrivers = Driver::where('status', 'Available')->get();
        $allVehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        $allDrivers = Driver::where('status', '!=', 'Inactive')->get();

        return view('admin.bookings.index', compact('bookings', 'availableVehicles', 'availableDrivers', 'allVehicles', 'allDrivers'));
    }

    public function create()
    {
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Available')->get();
        $drivers = Driver::where('status', 'Available')->get();

        return view('admin.bookings.create', compact('customers', 'vehicles', 'drivers'));
    }

    public function store(Request $request, BookingService $bookingService)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'required|date',
            'travel_time' => 'required',
            'return_date' => 'nullable|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'booking_status' => 'required|string',
            'payment_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        if (isset($data['paid_amount']) && (float)$data['paid_amount'] > (float)$data['total_amount']) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Paid amount cannot exceed total fare amount.'],
            ]);
        }

        $user = User::findOrFail($data['customer_id']);
        $booking = $bookingService->createBooking($data, $user);

        // If specific admin overrides were provided:
        $total = (float)$data['total_amount'];
        $paid = (float)($data['paid_amount'] ?? 0);
        $balance = max(0, $total - $paid);

        $booking->update([
            'booking_status' => $data['booking_status'],
            'payment_status' => $data['payment_status'],
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'driver_id' => $data['driver_id'] ?? null,
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Booking #{$booking->booking_id} created successfully!");
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'customer.customer',
            'vehicle',
            'driver',
            'trip',
            'payments',
            'invoice',
            'statusHistories.changer',
            'cancelledBy',
        ]);

        // Filter: Show only vehicles that are Available, plus current assigned vehicle
        $availableVehicles = Vehicle::where('status', 'Available')
            ->orWhere('id', $booking->vehicle_id)
            ->get();

        // Filter: Show only drivers that are Available, plus current assigned driver
        $availableDrivers = Driver::where('status', 'Available')
            ->orWhere('id', $booking->driver_id)
            ->get();

        return view('admin.bookings.show', compact('booking', 'availableVehicles', 'availableDrivers'));
    }

    public function edit(Booking $booking)
    {
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();
        $drivers = Driver::where('status', '!=', 'Inactive')->get();

        return view('admin.bookings.edit', compact('booking', 'customers', 'vehicles', 'drivers'));
    }

    public function update(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'trip_type' => 'required|string',
            'pickup_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'required|date',
            'travel_time' => 'required',
            'return_date' => 'nullable|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'payment_status' => 'required|string',
            'booking_status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $totalAmount = (float)$data['total_amount'];
        $paidAmount = (float)$data['paid_amount'];

        if ($paidAmount > $totalAmount) {
            throw ValidationException::withMessages([
                'paid_amount' => ['Paid amount cannot exceed total fare amount.'],
            ]);
        }

        $balance = max(0, $totalAmount - $paidAmount);
        $oldStatus = $booking->booking_status;
        $newStatus = $data['booking_status'];

        $booking->update([
            'customer_id' => $data['customer_id'],
            'trip_type' => $data['trip_type'],
            'pickup_location' => $data['pickup_location'],
            'destination' => $data['destination'],
            'travel_date' => $data['travel_date'],
            'travel_time' => $data['travel_time'],
            'return_date' => $data['return_date'] ?? null,
            'vehicle_id' => $data['vehicle_id'],
            'driver_id' => $data['driver_id'] ?? null,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $balance,
            'payment_status' => $data['payment_status'],
            'booking_status' => $newStatus,
            'notes' => $data['notes'] ?? null,
        ]);

        // Sync Trip
        if ($booking->trip) {
            $booking->trip->update([
                'vehicle_id' => $booking->vehicle_id,
                'driver_id' => $booking->driver_id,
                'status' => $newStatus,
                'trip_status' => $newStatus,
            ]);
        }

        // Record history if status changed
        if ($oldStatus !== $newStatus) {
            BookingStatusHistory::create([
                'booking_id' => $booking->id,
                'changed_by' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'remarks' => "Details updated by administrator; status set to {$newStatus}.",
                'comment' => "Details updated by administrator; status set to {$newStatus}.",
                'created_at' => now(),
            ]);
        }

        $paymentService->syncInvoice($booking);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Booking #{$booking->booking_id} updated successfully!");
    }

    /**
     * Action: Confirm Booking (Pending -> Confirmed)
     */
    public function confirm(Booking $booking, BookingService $bookingService)
    {
        if ($booking->booking_status !== 'Pending') {
            return back()->with('error', "Only Pending bookings can be confirmed. Current status: {$booking->booking_status}");
        }

        $bookingService->updateStatus($booking, 'Confirmed', 'Booking confirmed by central dispatch administrator.', Auth::user());

        return back()->with('success', "Booking #{$booking->booking_id} has been confirmed!");
    }

    /**
     * Action: Assign Vehicle (Available only, overlap checked)
     */
    public function assignVehicle(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
        ]);

        try {
            $bookingService->assignVehicle($booking, (int)$request->vehicle_id, Auth::user());
            return back()->with('success', "Vehicle successfully allocated to Booking #{$booking->booking_id}!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Assign Driver (Available only, overlap checked, creates/activates Trip)
     */
    public function assignDriver(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
        ]);

        try {
            $bookingService->assignDriver($booking, (int)$request->driver_id, Auth::user());
            return back()->with('success', "Chauffeur successfully assigned and Trip activated for Booking #{$booking->booking_id}!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Update Amount (Total Amount, Paid Amount, Balance calculation, intelligent Payment Status)
     */
    public function updateAmount(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        $total = (float)$request->total_amount;
        $paid = (float)$request->paid_amount;

        if ($paid > $total) {
            return back()->with('error', 'Validation Error: Paid Amount cannot be greater than Total Fare Amount.');
        }

        $balance = max(0, $total - $paid);

        $paymentStatus = 'Pending';
        if ($total > 0 && $paid >= $total) {
            $paymentStatus = 'Paid';
        } elseif ($paid > 0) {
            $paymentStatus = 'Partial';
        }

        $booking->update([
            'total_amount' => $total,
            'paid_amount' => $paid,
            'balance_amount' => $balance,
            'payment_status' => $paymentStatus,
        ]);

        $paymentService->syncInvoice($booking);

        return back()->with('success', "Billing amounts updated for Booking #{$booking->booking_id}! Total: ₹{$total}, Paid: ₹{$paid}, Balance: ₹{$balance}.");
    }

    /**
     * Action: Update Payment (Add direct payment record)
     */
    public function updatePayment(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $amount = (float)$request->amount;
        $maxPayable = (float)$booking->total_amount - (float)$booking->paid_amount;

        if ($amount > $maxPayable) {
            return back()->with('error', "Cannot accept payment of ₹{$amount}. Remaining balance is ₹{$maxPayable}.");
        }

        $paymentService->recordPayment($booking, [
            'amount' => $amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id ?: ('TXN-' . strtoupper(uniqid())),
            'status' => 'Success',
            'notes' => $request->notes,
        ], Auth::user());

        return back()->with('success', "Payment of ₹" . number_format($amount, 2) . " recorded successfully for Booking #{$booking->booking_id}!");
    }

    /**
     * Action: Controlled Status Transition
     */
    public function updateStatus(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'booking_status' => 'required|string',
            'remarks' => 'nullable|string|max:500',
        ]);

        $newStatus = $request->booking_status;
        $remarks = $request->remarks ?: $request->comment;

        try {
            $bookingService->updateStatus($booking, $newStatus, $remarks, Auth::user());
            return back()->with('success', "Booking #{$booking->booking_id} status transitioned to '{$newStatus}'!");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Action: Cancel Booking (Admin direct cancel or customer cancellation approval)
     */
    public function cancel(Request $request, Booking $booking, BookingService $bookingService)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $bookingService->cancelBooking($booking, $request->cancellation_reason, Auth::user());

        return back()->with('success', "Booking #{$booking->booking_id} has been cancelled.");
    }

    public function destroy(Booking $booking)
    {
        $id = $booking->booking_id;
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking #{$id} has been deleted.");
    }
}
