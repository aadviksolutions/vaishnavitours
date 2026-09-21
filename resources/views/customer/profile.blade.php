@extends('layouts.customer')

@section('title', 'Profile Settings')

@section('content')
<div class="mb-4">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin: 0;">Profile & Account Settings</h1>
    <p style="color: var(--slate-500); margin: 4px 0 0 0;">Manage your contact details, Bilaspur travel address, and security preferences.</p>
</div>

<div class="grid grid-3 gap-4">
    <!-- Left Column: User Summary Card -->
    <div>
        <div class="card text-center mb-4">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--dark-900); color: var(--primary); font-size: 1.75rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <h3 style="font-size: 1.2rem; font-weight: 800; margin: 0;">{{ $user->name }}</h3>
            <div style="color: var(--slate-500); font-size: 0.85rem; margin-top: 2px;">{{ $user->email }}</div>
            <div style="color: var(--slate-500); font-size: 0.85rem;">📞 {{ $user->phone }}</div>
            
            <div style="border-top: 1px solid var(--slate-200); margin-top: 1.25rem; padding-top: 1.25rem; text-align: left;">
                <div class="d-flex justify-between mb-2" style="font-size: 0.85rem;">
                    <span style="color: var(--slate-500);">Account Type:</span>
                    <span class="badge badge-primary">Verified Passenger</span>
                </div>
                <div class="d-flex justify-between mb-2" style="font-size: 0.85rem;">
                    <span style="color: var(--slate-500);">Registered On:</span>
                    <strong>{{ $user->created_at->format('d M Y') }}</strong>
                </div>
                <div class="d-flex justify-between" style="font-size: 0.85rem;">
                    <span style="color: var(--slate-500);">Total Bookings:</span>
                    <strong>{{ $user->bookings ? $user->bookings->count() : 0 }}</strong>
                </div>
            </div>
        </div>

        <!-- Quick Help Card -->
        <div class="card" style="background: var(--slate-50);">
            <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 0.5rem;">Need Account Support?</h4>
            <p style="font-size: 0.825rem; color: var(--slate-600); margin-bottom: 0.75rem;">
                If you need to change your registered email address or update business GST details for corporate billing, please contact our Bilaspur office.
            </p>
            <a href="{{ route('contact') }}" class="btn btn-outline btn-sm btn-block" style="text-align: center;">Contact Vaishnavi Support</a>
        </div>
    </div>

    <!-- Right 2 Columns: Edit Forms -->
    <div style="grid-column: span 2;">
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Personal Contact Details -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">👤 Personal Contact Details</h3>
                
                <div class="grid grid-2 gap-3 mb-3">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address (Registered)</label>
                        <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled style="background: var(--slate-100); color: var(--slate-500); cursor: not-allowed;">
                        <small style="color: var(--slate-400); font-size: 0.75rem;">Email cannot be modified directly.</small>
                    </div>
                </div>

                <div class="grid grid-2 gap-3">
                    <div class="form-group">
                        <label for="phone" class="form-label">Primary Mobile Number *</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required placeholder="e.g. 9826100000">
                    </div>

                    <div class="form-group">
                        <label for="alternate_phone" class="form-label">Alternate / Emergency Contact</label>
                        <input type="text" name="alternate_phone" id="alternate_phone" class="form-control" value="{{ old('alternate_phone', $user->customer->alternate_phone ?? '') }}" placeholder="e.g. 9425200000">
                    </div>
                </div>
            </div>

            <!-- Billing & Frequent Pickup Address -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 1.25rem;">📍 Default Pickup / Billing Address</h3>

                <div class="form-group mb-3">
                    <label for="address" class="form-label">Street Address / House No. / Landmark</label>
                    <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $user->customer->address ?? '') }}" placeholder="e.g. Flat 302, B.N City Colony, Mangla">
                </div>

                <div class="grid grid-3 gap-3">
                    <div class="form-group">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $user->customer->city ?? 'Bilaspur') }}">
                    </div>

                    <div class="form-group">
                        <label for="state" class="form-label">State</label>
                        <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $user->customer->state ?? 'Chhattisgarh') }}">
                    </div>

                    <div class="form-group">
                        <label for="pincode" class="form-label">PIN Code</label>
                        <input type="text" name="pincode" id="pincode" class="form-control" value="{{ old('pincode', $user->customer->pincode ?? '495001') }}">
                    </div>
                </div>
            </div>

            <!-- Security & Password Update -->
            <div class="card mb-4">
                <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem;">🔒 Change Password</h3>
                <p style="color: var(--slate-500); font-size: 0.85rem; margin-bottom: 1.25rem;">Leave blank if you do not wish to change your password.</p>

                <div class="form-group mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password to verify">
                </div>

                <div class="grid grid-2 gap-3">
                    <div class="form-group">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min. 6 characters">
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Re-type new password">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-end gap-2">
                <a href="{{ route('customer.dashboard') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary" style="min-width: 160px;">💾 Save All Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
