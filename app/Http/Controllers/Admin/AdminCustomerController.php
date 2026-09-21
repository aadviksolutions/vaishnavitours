<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['customer', 'bookings'])
            ->where('role', 'customer');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $customers = $query->latest()->paginate(15);

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        if ($user->role !== 'customer') {
            abort(404);
        }

        $user->load(['customer', 'bookings.vehicle', 'bookings.driver', 'payments']);

        $totalSpent = $user->payments()->where('status', 'Success')->sum('amount');
        $totalBookings = $user->bookings()->count();
        $completedTrips = $user->bookings()->where('booking_status', 'Completed')->count();

        return view('admin.customers.show', compact('user', 'totalSpent', 'totalBookings', 'completedTrips'));
    }
}
