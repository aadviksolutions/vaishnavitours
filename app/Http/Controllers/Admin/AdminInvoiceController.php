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

        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('booking_category', $cat)
                    ->orWhereHas('booking', fn ($b) => $b->where('booking_category', $cat));
            });
        }

        if ($request->filled('booking_type')) {
            $type = $request->booking_type;
            $query->whereHas('booking', fn ($b) => $b->where('trip_type', $type));
        }

        if ($request->filled('payment_status')) {
            $ps = $request->payment_status;
            $query->whereHas('booking', fn ($b) => $b->where('payment_status', $ps));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('booking', function ($bq) use ($s) {
                        $bq->where('booking_id', 'like', "%{$s}%")
                            ->orWhere('company_name', 'like', "%{$s}%");
                    })
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%");
                    });
            });
        }

        $invoices = $query->latest('invoice_date')->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_invoices' => Invoice::count(),
            'total_taxable' => (float) Invoice::sum('taxable_amount'),
            'total_gst' => (float) Invoice::sum('gst_amount'),
            'total_billed' => (float) Invoice::sum('total_amount'),
        ];

        return view('admin.invoices.index', compact('invoices', 'stats'));
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
