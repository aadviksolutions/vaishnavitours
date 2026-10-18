<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use Illuminate\Http\Request;

class AdminBookingCategoryController extends Controller
{
    public function index()
    {
        $categories = BookingCategory::withCount('bookings')->latest()->paginate(15);

        return view('admin.booking-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.booking-categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20|uppercase|unique:booking_categories,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_system'] = false;

        BookingCategory::create($data);

        return redirect()->route('admin.booking-categories.index')->with('success', 'Booking Category created successfully.');
    }

    public function edit(BookingCategory $bookingCategory)
    {
        return view('admin.booking-categories.edit', compact('bookingCategory'));
    }

    public function update(Request $request, BookingCategory $bookingCategory)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $bookingCategory->update($data);

        return redirect()->route('admin.booking-categories.index')->with('success', 'Booking Category updated successfully.');
    }

    public function destroy(BookingCategory $bookingCategory)
    {
        if ($bookingCategory->is_system) {
            return back()->with('error', 'System default categories (CORP, WEB, MAN, TP) cannot be deleted.');
        }

        $bookingCategory->delete();

        return redirect()->route('admin.booking-categories.index')->with('success', 'Booking Category deleted.');
    }
}
