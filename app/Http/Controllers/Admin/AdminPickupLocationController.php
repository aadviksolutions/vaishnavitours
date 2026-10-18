<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPickupLocationController extends Controller
{
    public function index()
    {
        $locations = PickupLocation::with('creator')->latest()->paginate(20);

        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('admin.locations.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:pickup_locations,name',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['created_by'] = Auth::id();

        PickupLocation::create($data);

        return redirect()->route('admin.locations.index')->with('success', 'Pickup Location added successfully.');
    }

    public function edit(PickupLocation $location)
    {
        return view('admin.locations.edit', compact('location'));
    }

    public function update(Request $request, PickupLocation $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:pickup_locations,name,'.$location->id,
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $location->update($data);

        return redirect()->route('admin.locations.index')->with('success', 'Pickup Location updated successfully.');
    }

    public function destroy(PickupLocation $location)
    {
        $location->delete();

        return redirect()->route('admin.locations.index')->with('success', 'Pickup Location deleted.');
    }
}
