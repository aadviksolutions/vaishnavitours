<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminCustomerController extends Controller
{
    public function index(Request $request)
    {
        $currentType = $request->query('type', 'all');
        $searchQuery = $request->query('search', '');

        $query = User::with(['customer', 'bookings'])
            ->where('role', 'customer');

        if ($currentType === 'individual') {
            $query->where(function ($q) {
                $q->whereHas('customer', function ($cq) {
                    $cq->where('customer_type', 'individual')->orWhereNull('customer_type');
                })->orWhereDoesntHave('customer');
            });
        } elseif ($currentType === 'corporate') {
            $query->whereHas('customer', function ($cq) {
                $cq->where('customer_type', 'corporate');
            });
        } elseif ($currentType === 'travel_partner') {
            $query->whereHas('customer', function ($cq) {
                $cq->where('customer_type', 'travel_partner');
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhereHas('customer', function ($cq) use ($s) {
                        $cq->where('company_name', 'like', "%{$s}%")
                            ->orWhere('gstin', 'like', "%{$s}%")
                            ->orWhere('partner_name', 'like', "%{$s}%")
                            ->orWhere('city', 'like', "%{$s}%");
                    });
            });
        }

        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'individual' => User::where('role', 'customer')->where(function ($q) {
                $q->whereHas('customer', fn ($cq) => $cq->where('customer_type', 'individual')->orWhereNull('customer_type'))
                    ->orWhereDoesntHave('customer');
            })->count(),
            'corporate' => User::where('role', 'customer')->whereHas('customer', fn ($cq) => $cq->where('customer_type', 'corporate'))->count(),
            'travel_partner' => User::where('role', 'customer')->whereHas('customer', fn ($cq) => $cq->where('customer_type', 'travel_partner'))->count(),
        ];

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers', 'stats', 'currentType', 'searchQuery'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        $customerType = $request->input('customer_type', 'individual');

        $finalName = '';
        $finalPhone = '';
        $finalEmail = '';

        if ($customerType === 'corporate') {
            $finalName = trim($request->input('company_name') ?: $request->input('corporate_contact_person') ?: 'Corporate Client');
            $finalPhone = substr(preg_replace('/\D/', '', (string) ($request->input('corporate_phone') ?: $request->input('phone'))), -10);
            $finalEmail = trim((string) ($request->input('corporate_email') ?: $request->input('email')));
        } elseif ($customerType === 'travel_partner') {
            $finalName = trim($request->input('partner_name') ?: $request->input('partner_contact_person') ?: 'Travel Partner');
            $finalPhone = substr(preg_replace('/\D/', '', (string) ($request->input('partner_phone') ?: $request->input('phone'))), -10);
            $finalEmail = trim((string) ($request->input('partner_email') ?: $request->input('email')));
        } else {
            $finalName = trim((string) $request->input('name'));
            $finalPhone = substr(preg_replace('/\D/', '', (string) $request->input('phone')), -10);
            $finalEmail = trim((string) $request->input('email'));
        }

        if (empty($finalName)) {
            $msg = $customerType === 'corporate' ? 'Company name is required.' : ($customerType === 'travel_partner' ? 'Agency name is required.' : 'Customer name is required.');

            return back()->withInput()->with('error', $msg);
        }

        if (empty($finalPhone) || strlen($finalPhone) < 10) {
            return back()->withInput()->with('error', 'A valid 10-digit mobile number is required.');
        }

        if (empty($finalEmail)) {
            $finalEmail = "{$finalPhone}@vaishnavitours.in";
        }

        $effectiveGstin = $customerType === 'corporate' ? trim((string) $request->input('corporate_gstin')) : trim((string) $request->input('partner_gstin'));
        $effectiveAddress = $customerType === 'corporate' ? trim((string) ($request->input('corporate_billing_address') ?: $request->input('address'))) : ($customerType === 'travel_partner' ? trim((string) ($request->input('partner_address') ?: $request->input('address'))) : trim((string) $request->input('address')));
        $effectiveContactPerson = $customerType === 'corporate' ? trim((string) ($request->input('corporate_contact_person') ?: $request->input('name'))) : ($customerType === 'travel_partner' ? trim((string) ($request->input('partner_contact_person') ?: $request->input('name'))) : trim((string) $request->input('name')));

        DB::beginTransaction();
        try {
            $user = User::where('phone', $finalPhone)->orWhere('email', $finalEmail)->first();

            if (! $user) {
                $user = User::create([
                    'name' => $finalName,
                    'email' => $finalEmail,
                    'phone' => $finalPhone,
                    'role' => 'customer',
                    'is_active' => true,
                    'password' => Hash::make('Password@123'),
                ]);
            } else {
                $user->update([
                    'name' => $finalName,
                ]);
            }

            Customer::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'customer_type' => $customerType,
                    'company_name' => $customerType === 'corporate' ? ($request->input('company_name') ?: $finalName) : null,
                    'contact_person' => $effectiveContactPerson,
                    'gstin' => $effectiveGstin ?: null,
                    'billing_address' => $effectiveAddress ?: null,
                    'partner_name' => $customerType === 'travel_partner' ? ($request->input('partner_name') ?: $finalName) : null,
                    'partner_commission' => $customerType === 'travel_partner' ? $request->input('partner_commission') : null,
                    'alternate_phone' => $request->input('alternate_phone'),
                    'address' => $effectiveAddress ?: null,
                    'city' => $request->input('city', 'Bilaspur') ?: 'Bilaspur',
                    'state' => $request->input('state', 'Chhattisgarh') ?: 'Chhattisgarh',
                    'pincode' => $request->input('pincode', '495001') ?: '495001',
                    'notes' => $request->input('notes'),
                ]
            );

            DB::commit();

            return redirect()->route('admin.customers.show', $user->id)->with('success', 'Customer account created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Error saving customer: '.$e->getMessage());
        }
    }

    public function sampleCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="sample_customers_vaishnavi_tours.csv"',
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Mobile', 'Email', 'Customer_Type', 'Company_Name', 'Contact_Person', 'GSTIN', 'City', 'Address', 'State', 'Pincode', 'Partner_Name', 'Partner_Commission', 'Notes']);
            fputcsv($out, ['Rajesh Sharma', '9876543210', 'rajesh.sharma@example.com', 'individual', '', '', '', 'Bilaspur', 'Civil Lines, Bilaspur', 'Chhattisgarh', '495001', '', '', 'Regular individual passenger']);
            fputcsv($out, ['Tata Projects Ltd', '9823456789', 'transport@tataprojects.com', 'corporate', 'Tata Projects Ltd', 'Mr. Anil Verma', '22AAAAA0000A1Z5', 'Raipur', 'Plot 45, Industrial Area', 'Chhattisgarh', '492001', '', '', 'Monthly corporate billing client']);
            fputcsv($out, ['Sharma Travel Agency', '9898765432', 'sharmatours@gmail.com', 'travel_partner', '', '', '', 'Bilaspur', 'Station Road', 'Chhattisgarh', '495001', 'Sharma Travel Agency', '10', '10% commission travel partner']);
            fclose($out);
        }, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file',
        ]);

        $file = $request->file('csv_file');
        $duplicateAction = $request->input('duplicate_action', 'update');
        $defaultType = $request->input('default_type', 'auto');

        $handle = fopen($file->getRealPath(), 'r');
        if (! $handle) {
            return back()->with('error', 'Unable to open uploaded CSV file.');
        }

        $headerRow = fgetcsv($handle);
        if (! $headerRow) {
            fclose($handle);

            return back()->with('error', 'The uploaded CSV file is empty.');
        }

        $headers = array_map(fn ($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '', str_replace([' ', '-'], '_', $h)))), $headerRow);

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            $data = [];
            foreach ($headers as $idx => $header) {
                $data[$header] = isset($row[$idx]) ? trim($row[$idx]) : null;
            }

            $name = $data['name'] ?? $data['customer_name'] ?? $data['passenger_name'] ?? null;
            $phone = preg_replace('/\D/', '', (string) ($data['mobile'] ?? $data['phone'] ?? $data['contact'] ?? ''));
            $phone = substr($phone, -10);

            if (empty($name) || strlen($phone) < 10) {
                $skipped++;

                continue;
            }

            $email = $data['email'] ?? "{$phone}@vaishnavitours.in";
            $custType = $data['customer_type'] ?? $data['type'] ?? ($defaultType !== 'auto' ? $defaultType : 'individual');
            if (! in_array($custType, ['individual', 'corporate', 'travel_partner'])) {
                $custType = 'individual';
            }

            $companyName = $data['company_name'] ?? ($custType === 'corporate' ? $name : null);
            $contactPerson = $data['contact_person'] ?? $name;
            $gstin = $data['gstin'] ?? $data['gst_number'] ?? null;
            $partnerName = $data['partner_name'] ?? ($custType === 'travel_partner' ? $name : null);
            $partnerCommission = $data['partner_commission'] ?? null;
            $city = $data['city'] ?? 'Bilaspur';
            $address = $data['address'] ?? null;
            $state = $data['state'] ?? 'Chhattisgarh';
            $pincode = $data['pincode'] ?? '495001';
            $notes = $data['notes'] ?? null;

            $existingUser = User::where('phone', $phone)->orWhere('email', $email)->first();

            if ($existingUser) {
                if ($duplicateAction === 'skip') {
                    $skipped++;

                    continue;
                }

                $existingUser->update(['name' => $name]);

                Customer::updateOrCreate(
                    ['user_id' => $existingUser->id],
                    [
                        'customer_type' => $custType,
                        'company_name' => $companyName,
                        'contact_person' => $contactPerson,
                        'gstin' => $gstin,
                        'billing_address' => $address,
                        'partner_name' => $partnerName,
                        'partner_commission' => $partnerCommission,
                        'address' => $address,
                        'city' => $city,
                        'state' => $state,
                        'pincode' => $pincode,
                        'notes' => $notes,
                    ]
                );
                $updated++;
            } else {
                $newUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'role' => 'customer',
                    'is_active' => true,
                    'password' => Hash::make('Password@123'),
                ]);

                Customer::create([
                    'user_id' => $newUser->id,
                    'customer_type' => $custType,
                    'company_name' => $companyName,
                    'contact_person' => $contactPerson,
                    'gstin' => $gstin,
                    'billing_address' => $address,
                    'partner_name' => $partnerName,
                    'partner_commission' => $partnerCommission,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'notes' => $notes,
                ]);
                $inserted++;
            }
        }

        fclose($handle);

        return redirect()->route('admin.customers.index')->with('success', "Bulk CSV Import complete: {$inserted} new registered, {$updated} updated, {$skipped} skipped.");
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
