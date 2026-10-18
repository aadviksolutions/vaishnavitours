<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThirdPartyPayment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminVehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with(['assignedDriver', 'trips', 'thirdPartyPayments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('vehicle_type', $request->type);
        }

        if ($request->filled('ownership_type')) {
            $query->where('ownership_type', $request->ownership_type);
        }

        if ($request->filled('doc_status')) {
            $today = now()->toDateString();
            $soon = now()->addDays(30)->toDateString();
            if ($request->doc_status === 'expired') {
                $query->where(function ($q) use ($today) {
                    $q->where('insurance_expiry', '<', $today)
                        ->orWhere('puc_expiry', '<', $today);
                });
            } elseif ($request->doc_status === 'expiring_soon') {
                $query->where(function ($q) use ($today, $soon) {
                    $q->whereBetween('insurance_expiry', [$today, $soon])
                        ->orWhereBetween('puc_expiry', [$today, $soon]);
                });
            } elseif ($request->doc_status === 'valid') {
                $query->where('insurance_expiry', '>', $soon)
                    ->where('puc_expiry', '>', $soon);
            }
        }

        $vehicles = $query->latest()->paginate(10)->withQueryString();

        $stats = [
            'total' => Vehicle::count(),
            'owned' => Vehicle::where('ownership_type', 'owned')->count(),
            'third_party' => Vehicle::where('ownership_type', 'third_party')->count(),
            'available' => Vehicle::where('status', 'Available')->count(),
            'expired_docs' => Vehicle::where('insurance_expiry', '<', now()->toDateString())
                ->orWhere('puc_expiry', '<', now()->toDateString())
                ->count(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'stats'));
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
            'insurance_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'required|date',
            'fitness_expiry' => 'nullable|date',
            'permit_expiry' => 'nullable|date',
            'puc_expiry' => 'required|date',
            'per_km_rate' => 'nullable|numeric|min:0',
            'per_hour_rate' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'ownership_type' => 'required|in:owned,third_party',
            'vendor_name' => 'nullable|required_if:ownership_type,third_party|string|max:255',
            'vendor_phone' => 'nullable|string|max:20',
            'vendor_email' => 'nullable|email|max:255',
            'agreed_vendor_amount' => 'nullable|numeric|min:0',
        ]);

        if (($data['ownership_type'] ?? 'owned') === 'third_party') {
            $agreed = (float) ($data['agreed_vendor_amount'] ?? 0);
            $data['vendor_balance_amount'] = $agreed;
            $data['vendor_paid_amount'] = 0;
            $data['vendor_payment_status'] = $agreed > 0 ? 'Pending' : 'Settled';
        }

        Vehicle::create($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle registered successfully with verified mandatory document records!');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'assignedDriver',
            'bookings.customer',
            'trips.driver',
            'thirdPartyPayments.creator',
            'complaints.booking',
        ]);

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
            'registration_number' => 'required|string|max:50|unique:vehicles,registration_number,'.$vehicle->id,
            'vehicle_type' => 'required|string',
            'seating_capacity' => 'required|integer|min:1|max:50',
            'ac_non_ac' => 'required|string',
            'insurance_number' => 'nullable|string|max:100',
            'insurance_expiry' => 'required|date',
            'fitness_expiry' => 'nullable|date',
            'permit_expiry' => 'nullable|date',
            'puc_expiry' => 'required|date',
            'per_km_rate' => 'nullable|numeric|min:0',
            'per_hour_rate' => 'nullable|numeric|min:0',
            'status' => 'required|string',
            'notes' => 'nullable|string',
            'ownership_type' => 'required|in:owned,third_party',
            'vendor_name' => 'nullable|required_if:ownership_type,third_party|string|max:255',
            'vendor_phone' => 'nullable|string|max:20',
            'vendor_email' => 'nullable|email|max:255',
            'agreed_vendor_amount' => 'nullable|numeric|min:0',
        ]);

        if (($data['ownership_type'] ?? 'owned') === 'third_party') {
            $agreed = (float) ($data['agreed_vendor_amount'] ?? 0);
            $paid = (float) $vehicle->vendor_paid_amount;
            $data['vendor_balance_amount'] = max(0, $agreed - $paid);
            $data['vendor_payment_status'] = $paid >= $agreed && $agreed > 0 ? 'Paid' : ($paid > 0 ? 'Partially Paid' : 'Pending');
        }

        $vehicle->update($data);

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle details updated successfully!');
    }

    public function storeThirdPartyPayment(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $payment = ThirdPartyPayment::create([
            'vehicle_id' => $vehicle->id,
            'booking_id' => $data['booking_id'] ?? null,
            'vendor_name' => $vehicle->vendor_name ?: 'Vendor',
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'transaction_reference' => $data['transaction_reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $vehicle->recalculateVendorPayments();

        return back()->with('success', 'Payment of ₹'.number_format($data['amount'], 2)." recorded for vendor {$vehicle->vendor_name}!");
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')
            ->with('success', 'Vehicle removed from fleet.');
    }
}
