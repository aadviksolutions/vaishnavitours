<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerInvoiceController extends Controller
{
    public function show(Invoice $invoice)
    {
        if ($invoice->customer_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->load(['booking.vehicle', 'booking.driver', 'customer.customer']);

        return view('customer.invoices.show', compact('invoice'));
    }

    /**
     * Section 19: Dedicated /invoice/{booking} route
     * Strictly authorized for booking customer or admin.
     */
    public function bookingInvoice(Booking $booking, PaymentService $paymentService)
    {
        Gate::authorize('viewInvoice', $booking);

        if (!$booking->invoice) {
            $paymentService->syncInvoice($booking);
            $booking->refresh();
        }

        $booking->load(['customer.customer', 'vehicle', 'driver', 'payments', 'invoice']);

        return view('invoices.booking-invoice', compact('booking'));
    }
}
