<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['booking', 'customer']);

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(15);
        $totalCollected = (float) Payment::where('status', 'Success')->sum('amount');
        $successfulCount = Payment::where('status', 'Success')->count();
        $totalPendingBalance = (float) Booking::where('balance_amount', '>', 0)->sum('balance_amount');
        $pendingBookingsCount = Booking::where('balance_amount', '>', 0)->count();

        return view('admin.payments.index', compact('payments', 'totalCollected', 'successfulCount', 'totalPendingBalance', 'pendingBookingsCount'));
    }

    public function create(Request $request)
    {
        $bookings = Booking::where('balance_amount', '>', 0)->with('customer')->get();
        $selectedBookingId = $request->query('booking_id');

        return view('admin.payments.create', compact('bookings', 'selectedBookingId'));
    }

    public function store(Request $request, PaymentService $paymentService)
    {
        $data = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $booking = Booking::findOrFail($data['booking_id']);
        $payment = $paymentService->recordPayment($booking, $data, Auth::user());

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Payment of ₹'.number_format($payment->amount, 2).' recorded successfully!');
    }

    public function refund(Request $request, Booking $booking, PaymentService $paymentService)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:'.$booking->paid_amount,
            'reason' => 'required|string|max:255',
        ]);

        $paymentService->recordRefund($booking, (float) $data['amount'], $data['reason'], Auth::user());

        return back()->with('success', 'Refund of ₹'.number_format($data['amount'], 2).' processed successfully.');
    }
}
