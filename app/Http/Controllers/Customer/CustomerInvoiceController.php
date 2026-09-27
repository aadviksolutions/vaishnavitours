<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Services\InvoiceDocumentService;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CustomerInvoiceController extends Controller
{
    public function show(Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): View
    {
        if ($invoice->customer_id !== Auth::id() && ! Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $invoice->loadMissing([
            'booking.customer.customer',
            'booking.vehicle',
            'booking.driver',
            'booking.payments',
            'booking.rateVehiclePrice',
        ]);

        $booking = $invoice->booking;
        $document = $invoiceDocumentService->forBooking($booking);
        $backUrl = route('customer.bookings.show', $invoice->booking_id);

        return view('invoices.printable', compact('invoice', 'booking', 'document', 'backUrl'));
    }

    /**
     * Section 19: Dedicated /invoice/{booking} route
     * Strictly authorized for booking customer or admin.
     */
    public function bookingInvoice(Booking $booking, PaymentService $paymentService, InvoiceDocumentService $invoiceDocumentService): View
    {
        Gate::authorize('viewInvoice', $booking);

        if (! $booking->invoice) {
            $paymentService->syncInvoice($booking);
            $booking->refresh();
        }

        $booking->load(['customer.customer', 'vehicle', 'driver', 'payments', 'invoice', 'rateVehiclePrice']);
        $invoice = $booking->invoice;
        $document = $invoiceDocumentService->forBooking($booking);
        $backUrl = 'javascript:history.back()';

        return view('invoices.printable', compact('invoice', 'booking', 'document', 'backUrl'));
    }
}
