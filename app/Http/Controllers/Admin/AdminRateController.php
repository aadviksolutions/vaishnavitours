<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RateRoute;
use App\Models\RateVehiclePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminRateController extends Controller
{
    private const CATEGORIES = [
        'airport' => 'Quick Airport Booking',
        'outstation' => 'Outstation Route',
        'round_trip' => 'Round Trip Per KM',
        'local_8h_80km' => 'Local Rental 8 Hours / 80 KM',
        'local_4h_40km' => 'Local Rental 4 Hours / 40 KM',
    ];

    public function index(): View
    {
        $routes = RateRoute::query()
            ->with('vehiclePrices')
            ->orderBy('category')
            ->orderBy('origin')
            ->paginate(25);

        return view('admin.rates.index', ['routes' => $routes, 'categories' => self::CATEGORIES]);
    }

    public function create(): View
    {
        return view('admin.rates.form', [
            'rateRoute' => new RateRoute(['category' => 'outstation', 'return_same_rate' => true, 'active' => true]),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRate($request);

        DB::transaction(function () use ($data): void {
            $route = RateRoute::create($this->routeAttributes($data));
            $this->syncPrices($route, $data['prices']);
        });

        return redirect()->route('admin.rates.index')->with('success', 'Rate route created.');
    }

    public function edit(RateRoute $rate): View
    {
        $rate->load('vehiclePrices');

        return view('admin.rates.form', ['rateRoute' => $rate, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, RateRoute $rate): RedirectResponse
    {
        $data = $this->validateRate($request);

        DB::transaction(function () use ($rate, $data): void {
            $rate->update($this->routeAttributes($data));
            $this->syncPrices($rate, $data['prices']);
        });

        return redirect()->route('admin.rates.index')->with('success', 'Rate route updated.');
    }

    public function destroy(RateRoute $rate): RedirectResponse
    {
        $rate->delete();

        return redirect()->route('admin.rates.index')->with('success', 'Rate route deleted.');
    }

    private function validateRate(Request $request): array
    {
        $category = $request->input('category');
        $rateId = $request->route('rate')?->id;

        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'origin' => [Rule::requiredIf(in_array($category, ['airport', 'outstation'], true)), 'nullable', 'string', 'max:255'],
            'destination' => [Rule::requiredIf(in_array($category, ['airport', 'outstation'], true)), 'nullable', 'string', 'max:255'],
            'km_limit' => ['nullable', 'integer', 'min:0'],
            'included_hours' => ['nullable', 'numeric', 'min:0'],
            'return_same_rate' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.vehicle_category' => ['required', 'string', 'max:80', 'distinct:ignore_case'],
            'prices.*.base_fare' => ['nullable', 'numeric', 'min:0'],
            'prices.*.gst_applicable' => ['sometimes', 'boolean'],
            'prices.*.extra_km_rate' => ['nullable', 'numeric', 'min:0'],
            'prices.*.extra_hour_rate' => ['nullable', 'numeric', 'min:0'],
            'prices.*.vehicle_rent' => ['nullable', 'numeric', 'min:0'],
            'prices.*.per_km_rate' => ['nullable', 'numeric', 'min:0'],
            'prices.*.night_charge' => ['nullable', 'numeric', 'min:0'],
            'prices.*.toll_type' => ['nullable', Rule::in(['included', 'extra'])],
            'prices.*.parking_type' => ['nullable', Rule::in(['included', 'extra'])],
            'prices.*.border_tax_type' => ['nullable', Rule::in(['included', 'extra'])],
            'prices.*.driver_food_type' => ['nullable', Rule::in(['included', 'extra'])],
        ]);

        foreach ($data['prices'] as $index => $price) {
            $hasBaseFare = isset($price['base_fare']) && $price['base_fare'] !== '';
            $hasPerKmTariff = isset($price['vehicle_rent'], $price['per_km_rate'])
                && $price['vehicle_rent'] !== ''
                && $price['per_km_rate'] !== '';

            if (! $hasBaseFare && ! $hasPerKmTariff) {
                throw ValidationException::withMessages([
                    "prices.{$index}.base_fare" => ['Enter a base fare or both vehicle rent and per-kilometer rate.'],
                ]);
            }
        }

        return $data;
    }

    private function routeAttributes(array $data): array
    {
        return [
            'category' => $data['category'],
            'origin' => $data['origin'] ?? null,
            'destination' => $data['destination'] ?? null,
            'km_limit' => $data['km_limit'] ?? null,
            'included_hours' => $data['included_hours'] ?? null,
            'return_same_rate' => (bool) ($data['return_same_rate'] ?? false),
            'active' => (bool) ($data['active'] ?? false),
        ];
    }

    private function syncPrices(RateRoute $route, array $prices): void
    {
        $categories = [];
        foreach ($prices as $price) {
            $vehicleCategory = trim($price['vehicle_category']);
            $categories[] = $vehicleCategory;
            RateVehiclePrice::updateOrCreate(
                ['rate_route_id' => $route->id, 'vehicle_category' => $vehicleCategory],
                [
                    'base_fare' => $price['base_fare'] ?? null,
                    'gst_applicable' => (bool) ($price['gst_applicable'] ?? false),
                    'extra_km_rate' => $price['extra_km_rate'] ?? null,
                    'extra_hour_rate' => $price['extra_hour_rate'] ?? null,
                    'vehicle_rent' => $price['vehicle_rent'] ?? null,
                    'per_km_rate' => $price['per_km_rate'] ?? null,
                    'night_charge' => $price['night_charge'] ?? null,
                    'toll_type' => $price['toll_type'] ?? null,
                    'parking_type' => $price['parking_type'] ?? null,
                    'border_tax_type' => $price['border_tax_type'] ?? null,
                    'driver_food_type' => $price['driver_food_type'] ?? null,
                ]
            );
        }

        $route->vehiclePrices()->whereNotIn('vehicle_category', $categories)->delete();
    }
}
