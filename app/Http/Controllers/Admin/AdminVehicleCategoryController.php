<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use App\Models\VehicleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminVehicleCategoryController extends Controller
{
    public function index()
    {
        $categories = VehicleCategory::with(['models', 'vehicles'])->orderBy('sort_order')->get();

        $stats = [
            'total_categories' => $categories->count(),
            'active_categories' => $categories->where('is_active', true)->count(),
            'total_models' => VehicleModel::count(),
            'active_models' => VehicleModel::where('is_active', true)->count(),
        ];

        return view('admin.vehicle-categories.index', compact('categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50|unique:vehicle_categories,code',
            'display_name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'default_seating_capacity' => 'required|integer|min:1|max:70',
            'min_seating_capacity' => 'nullable|integer|min:1|max:70',
            'max_seating_capacity' => 'nullable|integer|min:1|max:70',
            'is_capacity_configurable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($data['code'])) {
            $data['code'] = Str::slug($data['name'], '_');
        }

        $data['is_capacity_configurable'] = $request->boolean('is_capacity_configurable');
        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        VehicleCategory::create($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$data['name']}' created successfully!");
    }

    public function storeQuick(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
            'is_capacity_configurable' => 'nullable|boolean',
            'min_seating_capacity' => 'nullable|integer|min:1|max:70',
            'max_seating_capacity' => 'nullable|integer|min:1|max:70',
        ]);

        $name = trim($data['name']);
        $existing = VehicleCategory::where('name', $name)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => "Vehicle category '{$existing->name}' already exists.",
                'category' => $existing,
            ]);
        }

        $code = Str::slug($name, '_');
        $defaultCap = (int) ($data['default_seating_capacity'] ?? 4);

        $category = VehicleCategory::create([
            'name' => $name,
            'code' => $code,
            'display_name' => $name,
            'default_seating_capacity' => $defaultCap,
            'min_seating_capacity' => $data['min_seating_capacity'] ?? $defaultCap,
            'max_seating_capacity' => $data['max_seating_capacity'] ?? $defaultCap,
            'is_capacity_configurable' => $request->boolean('is_capacity_configurable'),
            'is_active' => true,
            'sort_order' => (VehicleCategory::max('sort_order') ?? 0) + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vehicle category '{$category->name}' added successfully!",
            'category' => $category,
        ]);
    }

    public function storeModelQuick(Request $request)
    {
        $data = $request->validate([
            'vehicle_category_id' => 'required|exists:vehicle_categories,id',
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:60',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
        ]);

        $name = trim($data['name']);
        $model = VehicleModel::create([
            'vehicle_category_id' => $data['vehicle_category_id'],
            'name' => $name,
            'brand' => $data['brand'] ?? null,
            'default_seating_capacity' => $data['default_seating_capacity'] ?? null,
            'is_active' => true,
            'sort_order' => (VehicleModel::where('vehicle_category_id', $data['vehicle_category_id'])->max('sort_order') ?? 0) + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Vehicle model '{$model->name}' added successfully!",
            'model' => $model,
        ]);
    }

    public function update(Request $request, VehicleCategory $vehicle_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:vehicle_categories,code,'.$vehicle_category->id,
            'display_name' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'default_seating_capacity' => 'required|integer|min:1|max:70',
            'min_seating_capacity' => 'nullable|integer|min:1|max:70',
            'max_seating_capacity' => 'nullable|integer|min:1|max:70',
            'is_capacity_configurable' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $data['is_capacity_configurable'] = $request->boolean('is_capacity_configurable');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        $vehicle_category->update($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' updated successfully!");
    }

    public function toggleStatus(VehicleCategory $vehicle_category)
    {
        $vehicle_category->is_active = ! $vehicle_category->is_active;
        $vehicle_category->save();

        $statusStr = $vehicle_category->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' {$statusStr} successfully!");
    }

    public function destroy(VehicleCategory $vehicle_category)
    {
        if ($vehicle_category->vehicles()->exists()) {
            return back()->with('error', "Cannot delete category '{$vehicle_category->name}' because {$vehicle_category->vehicles()->count()} vehicles are currently assigned to it. Deactivate it instead.");
        }

        $vehicle_category->models()->delete();
        $vehicle_category->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle category '{$vehicle_category->name}' deleted successfully.");
    }

    public function storeModel(Request $request, VehicleCategory $vehicle_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:60',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
            'sort_order' => 'nullable|integer',
        ]);

        $data['vehicle_category_id'] = $vehicle_category->id;
        $data['is_active'] = true;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        VehicleModel::create($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$data['name']}' added to {$vehicle_category->name} successfully!");
    }

    public function updateModel(Request $request, VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'brand' => 'nullable|string|max:60',
            'default_seating_capacity' => 'nullable|integer|min:1|max:70',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $vehicle_model->is_active;
        $data['sort_order'] = (int) ($data['sort_order'] ?? $vehicle_model->sort_order);

        $vehicle_model->update($data);

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$vehicle_model->name}' updated successfully!");
    }

    public function toggleModelStatus(VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        $vehicle_model->is_active = ! $vehicle_model->is_active;
        $vehicle_model->save();

        $statusStr = $vehicle_model->is_active ? 'activated' : 'deactivated';

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Model '{$vehicle_model->name}' {$statusStr} successfully!");
    }

    public function destroyModel(VehicleCategory $vehicle_category, VehicleModel $vehicle_model)
    {
        if ($vehicle_model->vehicles()->exists()) {
            return back()->with('error', "Cannot delete model '{$vehicle_model->name}' because vehicles are currently using it. Deactivate it instead.");
        }

        $name = $vehicle_model->name;
        $vehicle_model->delete();

        return redirect()->route('admin.vehicle-categories.index')
            ->with('success', "Vehicle model '{$name}' deleted successfully.");
    }
}
