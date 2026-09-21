<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user()->load('customer');
        return view('customer.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'],
        ]);

        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'The current password provided is incorrect.']);
            }
            $user->update(['password' => Hash::make($data['new_password'])]);
        }

        Customer::updateOrCreate(
            ['user_id' => $user->id],
            [
                'alternate_phone' => $data['alternate_phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? 'Bilaspur',
                'state' => $data['state'] ?? 'Chhattisgarh',
                'pincode' => $data['pincode'] ?? '495001',
            ]
        );

        return back()->with('success', 'Your profile details have been updated successfully.');
    }
}
