<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['booking.vehicle', 'trip', 'enteredBy']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('expense_date', '<=', $request->date_to);
        }

        $totalFuel = (clone $query)->where('category', 'Fuel')->sum('amount');
        $totalToll = (clone $query)->where('category', 'Toll')->sum('amount');
        $totalOther = (clone $query)->where('category', 'Other')->sum('amount');
        $grandTotal = (clone $query)->sum('amount');

        $totals = [
            'total' => (float) $grandTotal,
            'fuel' => (float) $totalFuel,
            'toll' => (float) $totalToll,
            'other' => (float) $totalOther,
        ];

        $expenses = $query->latest('expense_date')->paginate(15);

        return view('admin.expenses.index', compact('expenses', 'totals', 'totalFuel', 'totalToll', 'totalOther', 'grandTotal'));
    }

    public function create(Request $request)
    {
        $bookingId = $request->query('booking_id');
        $selectedBooking = $bookingId ? Booking::find($bookingId) : null;
        $bookings = Booking::latest()->take(50)->get();
        $vehicles = Vehicle::orderBy('name')->get();

        return view('admin.expenses.create', compact('selectedBooking', 'bookings', 'vehicles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',
            'trip_id' => 'nullable|exists:trips,id',
            'category' => 'required|in:Fuel,Toll,Other',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'payment_method' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'receipt_number' => 'nullable|string|max:100',
        ]);

        if (empty($data['trip_id']) && ! empty($data['booking_id'])) {
            $trip = Trip::where('booking_id', $data['booking_id'])->first();
            $data['trip_id'] = $trip?->id;
        }

        $data['entered_by'] = Auth::id();

        Expense::create($data);

        return redirect()->route('admin.expenses.index')->with('success', 'Expense recorded successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted.');
    }
}
