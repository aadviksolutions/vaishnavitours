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
        $query = Driver::with(['assignedVehicle', 'trips']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->latest()->paginate(10);

        return view('admin.drivers.index', compact('drivers'));
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
            'notes' => 'nullable|string',
        ]);

        Driver::create($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Driver onboarded successfully!');
    }

    public function show(Driver $driver)
    {
        $driver->load(['assignedVehicle', 'bookings.customer', 'trips.booking']);
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
            'mobile' => 'required|string|max:20|unique:drivers,mobile,' . $driver->id,
            'alternate_mobile' => 'nullable|string|max:20',
            'license_number' => 'required|string|max:50|unique:drivers,license_number,' . $driver->id,
            'license_expiry' => 'nullable|date',
            'address' => 'nullable|string',
            'status' => 'required|string',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
            'rating' => 'nullable|numeric|min:1|max:5',
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
