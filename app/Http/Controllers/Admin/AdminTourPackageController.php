<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminTourPackageController extends Controller
{
    public function index()
    {
        $packages = TourPackage::latest()->paginate(15);

        return view('admin.tour-packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.tour-packages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'duration' => 'required|string|max:100',
            'duration_days' => 'required|integer|min:1',
            'destinations' => 'nullable|string',
            'applicable_vehicles' => 'nullable|string',
            'sedan_price' => 'nullable|numeric|min:0',
            'ertiga_price' => 'nullable|numeric|min:0',
            'innova_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['slug'] = Str::slug($data['name']).'-'.substr(uniqid(), -4);
        $data['is_active'] = $request->boolean('is_active', true);

        TourPackage::create($data);

        return redirect()->route('admin.tour-packages.index')->with('success', 'Tour Package created successfully.');
    }

    public function edit(TourPackage $tourPackage)
    {
        return view('admin.tour-packages.edit', compact('tourPackage'));
    }

    public function update(Request $request, TourPackage $tourPackage)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'route' => 'nullable|string|max:255',
            'duration' => 'required|string|max:100',
            'duration_days' => 'required|integer|min:1',
            'destinations' => 'nullable|string',
            'applicable_vehicles' => 'nullable|string',
            'sedan_price' => 'nullable|numeric|min:0',
            'ertiga_price' => 'nullable|numeric|min:0',
            'innova_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'terms' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $tourPackage->update($data);

        return redirect()->route('admin.tour-packages.index')->with('success', 'Tour Package updated successfully.');
    }

    public function destroy(TourPackage $tourPackage)
    {
        $tourPackage->delete();

        return redirect()->route('admin.tour-packages.index')->with('success', 'Tour Package deleted.');
    }
}
