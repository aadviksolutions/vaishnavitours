@extends('layouts.admin')

@section('title', 'Vehicle: ' . $vehicle->name)
@section('page_title', 'Vehicle Details')

@section('content')
<div class="d-flex justify-between align-center flex-wrap gap-2 mb-3">
    <div>
        <a href="{{ route('admin.vehicles.index') }}" style="font-size: 13px; color: var(--slate-600); font-weight: 600; text-decoration: none;">
            ← Back to Fleet Management
        </a>
        <div class="d-flex align-center gap-2 mt-1">
            <h1 style="font-size: 18px; font-weight: 700; margin: 0;">{{ $vehicle->name }}</h1>
            <span class="badge {{ $vehicle->status === 'Available' ? 'badge-success' : ($vehicle->status === 'On Trip' ? 'badge-warning' : 'badge-danger') }}">
                {{ $vehicle->status }}
            </span>
            <span class="badge {{ $vehicle->ownership_type === 'third_party' ? 'badge-warning' : 'badge-primary' }}">
                {{ $vehicle->ownership_type === 'third_party' ? 'Third-Party Attached' : 'Fleet Owned' }}
            </span>
        </div>
    </div>

    <div class="d-flex align-center gap-2">
        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-primary btn-sm">
            ✏️ Edit Vehicle
        </a>
        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" onsubmit="return confirm('Remove this vehicle from the fleet?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">🗑️ Remove</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-3" style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 0.75rem 1rem; border-radius: 6px;">
        ✓ {{ session('success') }}
    </div>
@endif

<div class="grid grid-3 gap-3">
    <!-- Left 2 Cols: Specs, Vendor Tracking & Trips -->
    <div style="grid-column: span 2;">
        <!-- Specs -->
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark-900);">Fleet Specifications</h3>

            <div class="grid grid-3 gap-2 mb-3">
                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px;">
                    <div style="font-size: 11px; color: var(--slate-500); text-transform: uppercase;">Registration Number</div>
                    <div style="font-weight: 700; font-family: monospace; font-size: 14px; color: #2563EB; margin-top: 2px;">{{ $vehicle->registration_number }}</div>
                </div>

                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px;">
                    <div style="font-size: 11px; color: var(--slate-500); text-transform: uppercase;">Category & Model</div>
                    <div style="font-weight: 700; font-size: 13.5px; margin-top: 2px;">
                        {{ $vehicle->category?->name ?? $vehicle->vehicle_type }}
                        @if($vehicle->vehicleModel)
                            <span style="font-size: 12px; font-weight: 600; color: #4F46E5;"> • {{ $vehicle->vehicleModel->name }}</span>
                        @endif
                    </div>
                </div>

                <div style="background: var(--slate-50); padding: 0.75rem; border-radius: 6px;">
                    <div style="font-size: 11px; color: var(--slate-500); text-transform: uppercase;">Seating & AC</div>
                    <div style="font-weight: 700; font-size: 13.5px; margin-top: 2px;">{{ $vehicle->seating_capacity }} Seater • {{ $vehicle->ac_non_ac }}</div>
                </div>
            </div>

            @if($vehicle->notes)
                <div style="background: #fffbeb; padding: 0.75rem; border-radius: 6px; border: 1px solid #fef3c7; font-size: 12.5px;">
                    <strong>Fleet Notes:</strong> {{ $vehicle->notes }}
                </div>
            @endif
        </div>

        <!-- Third-Party Vendor Tracking & Payment Records (Section 3 & 9 requirement) -->
        @if($vehicle->ownership_type === 'third_party')
            <div class="card mb-3" style="background: #fff; border: 1px solid #FDE68A; border-radius: 8px; padding: 1.25rem;">
                <div class="d-flex justify-between align-center mb-2">
                    <h3 style="font-size: 14px; font-weight: 700; margin: 0; color: #92400E;">
                        🤝 Third-Party Vendor / Owner Accounting
                    </h3>
                    <span class="badge {{ $vehicle->vendor_payment_status === 'Paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ $vehicle->vendor_payment_status ?: 'Pending' }}
                    </span>
                </div>

                <div class="grid grid-4 gap-2 mb-3" style="background: #FFFBEB; padding: 0.75rem; border-radius: 6px;">
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #92400E; font-weight: 600;">Vendor / Owner</span>
                        <div style="font-weight: 700; font-size: 13px;">{{ $vehicle->vendor_name ?: 'Vendor' }}</div>
                        <div style="font-size: 11px; color: var(--slate-600);">📞 {{ $vehicle->vendor_phone ?: 'N/A' }}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #92400E; font-weight: 600;">Agreed Cost</span>
                        <div style="font-weight: 700; font-size: 15px; color: var(--dark-900);">₹{{ number_format($vehicle->agreed_vendor_amount, 2) }}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #92400E; font-weight: 600;">Paid to Vendor</span>
                        <div style="font-weight: 700; font-size: 15px; color: #059669;">₹{{ number_format($vehicle->vendor_paid_amount, 2) }}</div>
                    </div>
                    <div>
                        <span style="font-size: 11px; text-transform: uppercase; color: #92400E; font-weight: 600;">Balance Payable</span>
                        <div style="font-weight: 700; font-size: 15px; color: #DC2626;">₹{{ number_format($vehicle->vendor_balance_amount, 2) }}</div>
                    </div>
                </div>

                <!-- Record Vendor Payment Form -->
                <form action="{{ route('admin.vehicles.payments.store', $vehicle->id) }}" method="POST" class="mb-3" style="background: var(--slate-50); padding: 0.85rem; border-radius: 6px;">
                    @csrf
                    <div style="font-weight: 600; font-size: 12px; margin-bottom: 0.5rem; color: var(--dark-900);">
                        + Record Vendor Payment / Settlement
                    </div>
                    <div class="grid grid-4 gap-2 mb-2">
                        <div>
                            <input type="number" step="0.01" name="amount" class="form-control form-control-sm" placeholder="Amount (₹)" required>
                        </div>
                        <div>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="Bank Transfer">Bank Transfer (NEFT/RTGS)</option>
                                <option value="UPI">UPI / GPay</option>
                                <option value="Cash">Cash Voucher</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div>
                            <input type="text" name="transaction_reference" class="form-control form-control-sm" placeholder="Txn Ref / UTR #">
                        </div>
                    </div>
                    <div class="d-flex justify-between align-center">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Payment remarks..." style="max-width: 400px;">
                        <button type="submit" class="btn btn-primary btn-sm">Record Payment</button>
                    </div>
                </form>

                <!-- Vendor Payment History Table -->
                <div style="font-weight: 600; font-size: 12px; margin-bottom: 0.35rem;">Vendor Payment History</div>
                <div class="table-responsive">
                    <table class="data-table" style="width: 100%; font-size: 12px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Txn Ref</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicle->thirdPartyPayments as $tpp)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($tpp->payment_date)->format('d M Y') }}</td>
                                    <td style="font-weight: 700; color: #059669;">₹{{ number_format($tpp->amount, 2) }}</td>
                                    <td>{{ $tpp->payment_method }}</td>
                                    <td>{{ $tpp->transaction_reference ?: '-' }}</td>
                                    <td>{{ $tpp->creator?->name ?? 'Admin' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--slate-400); padding: 0.75rem;">No payment transactions recorded for this vendor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Vehicle Quality Complaints & Reviews (Section 3 requirement) -->
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <div class="d-flex justify-between align-center mb-2">
                <h3 style="font-size: 14px; font-weight: 700; margin: 0; color: var(--dark-900);">
                    🛠️ Vehicle Quality & Cleanliness Audit Reviews
                </h3>
                <a href="{{ route('admin.complaints.create') }}" class="btn btn-outline btn-sm" style="font-size: 11px;">
                    + Log Vehicle Issue
                </a>
            </div>
            <div class="table-responsive">
                <table class="data-table" style="width: 100%; font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Rating</th>
                            <th>Issue / Review</th>
                            <th>Customer</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->complaints as $vc)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($vc->complaint_date)->format('d M Y') }}</td>
                                <td style="font-weight: 700;">⭐ {{ $vc->rating }}/5</td>
                                <td>
                                    <strong>{{ $vc->title }}</strong>
                                    <div style="font-size: 11px; color: var(--slate-500);">{{ Str::limit($vc->description, 50) }}</div>
                                </td>
                                <td>{{ $vc->customer_name }}</td>
                                <td><span class="badge badge-secondary">{{ $vc->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--slate-400); padding: 0.75rem;">No vehicle complaints or cleanliness issues logged.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Trip History -->
        <div class="card" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem;">📋 Booking & Trip History</h3>
            <div class="table-responsive">
                <table class="data-table" style="width: 100%; font-size: 12px;">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Route</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Fare</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicle->bookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $b->id) }}" style="font-weight: 700; color: #2563EB;">
                                        {{ $b->booking_id }}
                                    </a>
                                </td>
                                <td>{{ $b->customer->name ?? 'Customer' }}</td>
                                <td>{{ $b->pickup_location }} ➔ {{ $b->destination }}</td>
                                <td>{{ $b->travel_date ? $b->travel_date->format('d M Y') : '' }}</td>
                                <td><span class="badge badge-secondary">{{ $b->booking_status }}</span></td>
                                <td style="font-weight: 700;">₹{{ number_format($b->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 1.5rem; color: var(--slate-500);">No trips assigned to this vehicle yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right 1 Col: Compliance Expiry & Assigned Driver -->
    <div>
        <!-- Compliance & Mandatory Expiry Dates (Section 3 requirement) -->
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark-900);">
                📑 Mandatory Document Expiry Dates
            </h3>
            @php
                $docs = [
                    'Insurance' => ['date' => $vehicle->insurance_expiry, 'status' => $vehicle->getInsuranceStatusAttribute()],
                    'PUC (Pollution)' => ['date' => $vehicle->puc_expiry, 'status' => $vehicle->getPucStatusAttribute()],
                    'Fitness Certificate' => ['date' => $vehicle->fitness_expiry, 'status' => $vehicle->fitness_expiry ? ($vehicle->fitness_expiry->isPast() ? 'Expired' : 'Valid') : 'Missing'],
                    'Commercial Permit' => ['date' => $vehicle->permit_expiry, 'status' => $vehicle->permit_expiry ? ($vehicle->permit_expiry->isPast() ? 'Expired' : 'Valid') : 'Missing'],
                ];
            @endphp
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach($docs as $label => $d)
                    <div style="background: var(--slate-50); padding: 0.65rem; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-weight: 600; font-size: 12.5px;">{{ $label }}</div>
                            <div style="font-size: 11px; color: var(--slate-500);">
                                {{ $d['date'] ? \Carbon\Carbon::parse($d['date'])->format('d M Y') : 'Not Set' }}
                            </div>
                        </div>
                        <div>
                            @php
                                $badgeClass = match($d['status']) {
                                    'Valid' => 'bg-emerald-100 text-emerald-800',
                                    'Expiring Soon' => 'bg-amber-100 text-amber-800',
                                    'Expired' => 'bg-rose-100 text-rose-800 font-bold',
                                    default => 'bg-slate-100 text-slate-800',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $badgeClass }}">
                                {{ $d['status'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Assigned Driver -->
        <div class="card mb-3" style="background: #fff; border: 1px solid var(--slate-200); border-radius: 8px; padding: 1.25rem;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark-900);">👨‍✈️ Default Assigned Chauffeur</h3>

            @if($vehicle->assignedDriver)
                <div style="font-size: 14px; font-weight: 700; color: var(--dark-900);">{{ $vehicle->assignedDriver->name }}</div>
                <div style="color: var(--slate-600); font-size: 12px; margin-top: 2px;">
                    📞 <a href="tel:{{ $vehicle->assignedDriver->mobile }}">{{ $vehicle->assignedDriver->mobile }}</a>
                </div>
                <div style="color: var(--slate-500); font-size: 11px; margin-top: 2px;">
                    License: {{ $vehicle->assignedDriver->license_number }}
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.drivers.show', $vehicle->assignedDriver->id) }}" class="btn btn-outline btn-sm w-100" style="text-align: center; font-size: 11px;">
                        View Chauffeur Profile →
                    </a>
                </div>
            @else
                <div style="color: var(--slate-500); font-size: 12px;">
                    No primary driver attached. Drivers can be allocated dynamically during booking dispatch.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
