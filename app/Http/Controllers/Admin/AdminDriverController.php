<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AdminDriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with(['assignedVehicle', 'trips.booking', 'complaints']);

        // Assigned vs Unassigned filtering
        if ($request->query('filter') === 'unassigned') {
            $query->unassigned();
        } elseif ($request->query('filter') === 'assigned') {
            $query->assigned();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sorting for unassigned or general drivers
        $sortBy = $request->query('sort', 'latest');
        match ($sortBy) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'rating_desc' => $query->orderBy('rating', 'desc'),
            'experience' => $query->orderBy('experience_years', 'desc'),
            default => $query->latest(),
        };

        $drivers = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', '!=', 'Inactive')->count(),
            'available_unassigned' => Driver::where('status', 'Available')
                ->whereDoesntHave('trips', fn ($t) => $t->whereIn('status', ['Trip Started', 'Ongoing', 'Assigned']))
                ->count(),
            'assigned' => Driver::whereHas('trips', fn ($t) => $t->whereIn('status', ['Trip Started', 'Ongoing', 'Assigned']))
                ->orWhereNotNull('assigned_vehicle_id')
                ->count(),
        ];

        return view('admin.drivers.index', compact('drivers', 'stats'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();

        return view('admin.drivers.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:drivers',
            'alternate_mobile' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50|unique:drivers',
            'license_expiry' => 'nullable|date',
            'address' => 'nullable|string',
            'status' => 'required|string',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
            'rating' => 'nullable|numeric|min:1|max:5',
            'experience_years' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        Driver::create($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver onboarded successfully!');
    }

    public function show(Driver $driver)
    {
        $driver->load(['assignedVehicle', 'bookings.customer', 'trips.booking', 'complaints.booking']);

        return view('admin.drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        $vehicles = Vehicle::where('status', '!=', 'Inactive')->get();

        return view('admin.drivers.edit', compact('driver', 'vehicles'));
    }

    public function update(Request $request, Driver $driver)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20|unique:drivers,mobile,'.$driver->id,
            'alternate_mobile' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,'.$driver->id,
            'license_expiry' => 'nullable|date',
            'address' => 'nullable|string',
            'status' => 'required|string',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
            'rating' => 'nullable|numeric|min:1|max:5',
            'experience_years' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $driver->update($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver details updated successfully!');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver record deleted.');
    }
}
