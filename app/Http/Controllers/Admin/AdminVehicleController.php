<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AdminVehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['assignedDriver', 'trips']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('vehicle_type', $request->type);
        }

        $vehicles = $query->latest()->paginate(10);

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50|unique:vehicles',
            'vehicle_type' => 'required|string',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'ac_non_ac' => 'required|string',
            'per_km_rate' => 'required|numeric|min:1',
            'per_hour_rate' => 'required|numeric|min:1',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle registered successfully!');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['assignedDriver', 'bookings.customer', 'trips.driver']);
        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        return view('admin.vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,' . $vehicle->id,
            'vehicle_type' => 'required|string',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'ac_non_ac' => 'required|string',
            'per_km_rate' => 'required|numeric|min:1',
            'per_hour_rate' => 'required|numeric|min:1',
            'status' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $vehicle->update($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle details updated successfully!');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle removed from fleet.');
    }
}
