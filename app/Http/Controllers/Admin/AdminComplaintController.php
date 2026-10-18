<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['booking', 'driver', 'vehicle', 'resolver']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $driverComplaintsCount = Complaint::where('type', 'Driver')->count();
        $vehicleComplaintsCount = Complaint::where('type', 'Vehicle')->count();
        $openCount = Complaint::where('status', 'Open')->count();

        $complaints = $query->latest('complaint_date')->paginate(15);

        return view('admin.complaints.index', compact('complaints', 'driverComplaintsCount', 'vehicleComplaintsCount', 'openCount'));
    }

    public function create()
    {
        $bookings = Booking::latest()->take(50)->get();
        $drivers = Driver::where('status', '!=', 'Inactive')->get();
        $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();

        return view('admin.complaints.create', compact('bookings', 'drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:Driver,Vehicle',
            'booking_id' => 'nullable|exists:bookings,id',
            'driver_id' => 'nullable|required_if:type,Driver|exists:drivers,id',
            'vehicle_id' => 'nullable|required_if:type,Vehicle|exists:vehicles,id',
            'customer_name' => 'required|string|max:255',
            'customer_mobile' => 'nullable|string|max:30',
            'customer_email' => 'nullable|email|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'complaint_date' => 'required|date',
            'description' => 'required|string|max:1500',
        ]);

        $data['status'] = 'Open';

        Complaint::create($data);

        return redirect()->route('admin.complaints.index')->with('success', 'Complaint / Review recorded successfully.');
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['booking', 'driver', 'vehicle', 'resolver']);

        return view('admin.complaints.show', compact('complaint'));
    }

    public function updateStatus(Request $request, Complaint $complaint)
    {
        $data = $request->validate([
            'status' => 'required|in:Open,In Progress,Resolved,Dismissed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'status' => $data['status'],
            'resolution_notes' => $data['resolution_notes'],
        ];

        if ($data['status'] === 'Resolved' && ! $complaint->resolved_at) {
            $updateData['resolved_at'] = now();
            $updateData['resolved_by'] = Auth::id();
        }

        $complaint->update($updateData);

        return redirect()->route('admin.complaints.show', $complaint)->with('success', 'Complaint status updated.');
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('admin.complaints.index')->with('success', 'Complaint deleted.');
    }
}
