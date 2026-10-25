<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminEnquiryController extends Controller
{
    public function __construct(private readonly WhatsappService $whatsapp) {}

    public function index(Request $request)
    {
        $currentSource = $request->query('source', 'website');
        $query = Enquiry::with(['assignedUser', 'convertedBooking'])->latest();

        if ($currentSource !== 'all') {
            $query->where('source', $currentSource);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('enquiry_id', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('pickup_location', 'like', "%{$s}%")
                    ->orWhere('destination', 'like', "%{$s}%");
            });
        }

        $enquiries = $query->paginate(15)->withQueryString();

        $sourceCounts = [
            'website' => Enquiry::where('source', 'website')->count(),
            'manual' => Enquiry::where('source', 'manual')->count(),
            'corporate' => Enquiry::where('source', 'corporate')->count(),
            'third_party' => Enquiry::where('source', 'third_party')->count(),
            'all' => Enquiry::count(),
        ];

        $staffUsers = User::whereIn('role', ['admin', 'manager', 'dispatcher', 'staff'])->get();

        return view('admin.enquiries.index', compact('enquiries', 'sourceCounts', 'currentSource', 'staffUsers'));
    }

    public function storeManual(Request $request)
    {
        if ($request->filled('phone')) {
            $raw = (string) $request->input('phone');
            $clean = preg_replace('/[^0-9]/', '', $raw);
            if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
                $clean = substr($clean, 2);
            } elseif (strlen($clean) === 11 && str_starts_with($clean, '0')) {
                $clean = substr($clean, 1);
            }
            $request->merge(['phone' => $clean]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'email' => 'nullable|email|max:255',
            'source' => 'required|in:manual,corporate,third_party',
            'booking_type' => 'required|string|max:50',
            'pickup_location' => 'required|string|max:255',
            'via_location' => 'nullable|string|max:255',
            'destination' => 'required|string|max:255',
            'travel_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'duration_days' => 'nullable|integer|min:1',
            'vehicle_preference' => 'nullable|string|max:80',
            'message' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data['enquiry_id'] = Enquiry::generateEnquiryId($data['source']);
        $data['status'] = 'New';
        $data['assigned_to'] = $data['assigned_to'] ?? Auth::id();

        $enquiry = Enquiry::create($data);

        // WhatsApp notification for new manual enquiry
        if ($enquiry->phone) {
            $this->whatsapp->sendEvent(
                'new_enquiry',
                $this->whatsapp->enquiryVariables($enquiry),
                $enquiry->phone,
                $enquiry
            );
        }

        return redirect()->route('admin.enquiries.index', ['source' => $enquiry->source])
            ->with('success', "Manual enquiry {$enquiry->enquiry_id} recorded successfully.");
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['assignedUser', 'convertedBooking']);
        $staffUsers = User::whereIn('role', ['admin', 'manager', 'dispatcher', 'staff'])->get();

        return view('admin.enquiries.show', compact('enquiry', 'staffUsers'));
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate([
            'status' => 'required|string|in:New,Contacted,Quotation Sent,Converted,Lost,In Progress,Resolved,Closed',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $oldNotes = $enquiry->notes ? $enquiry->notes."\n" : '';
        if ($request->filled('new_followup_note')) {
            $user = Auth::user()?->name ?? 'Staff';
            $data['notes'] = $oldNotes.'['.now()->format('d M Y, h:i A')." by {$user}]: ".$request->input('new_followup_note');
        }

        $enquiry->update($data);

        return back()->with('success', "Enquiry {$enquiry->enquiry_id} updated successfully.");
    }

    public function convert(Enquiry $enquiry)
    {
        // Redirect to booking creation prefilled with enquiry data
        return redirect()->route('admin.bookings.create', [
            'from_enquiry' => $enquiry->id,
        ])->with('info', "Converting enquiry {$enquiry->enquiry_id} to a confirmed booking.");
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted successfully.');
    }
}
