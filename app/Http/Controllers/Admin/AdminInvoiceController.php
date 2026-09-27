<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceDocumentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['booking.vehicle', 'booking.driver', 'customer']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate(15);

        return view('admin.invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice, InvoiceDocumentService $invoiceDocumentService): View
    {
        $invoice->loadMissing([
            'booking.customer.customer',
            'booking.vehicle',
            'booking.driver',
            'booking.payments',
            'booking.rateVehiclePrice',
        ]);

        $booking = $invoice->booking;
        $document = $invoiceDocumentService->forBooking($booking);
        $backUrl = route('admin.invoices.index');

        return view('invoices.printable', compact('invoice', 'booking', 'document', 'backUrl'));
    }
}
